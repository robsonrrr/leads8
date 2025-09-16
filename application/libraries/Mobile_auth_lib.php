<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mobile_auth_lib {
    
    protected $CI;
    protected $config;
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('mobile');
        $this->config = $this->CI->config->item('mobile');
        
        // Carrega dependências
        $this->CI->load->database();
        $this->CI->load->library('encryption');
        $this->CI->load->helper('string');
    }
    
    /**
     * Autentica usuário
     */
    public function authenticate($username, $password) {
        // Busca usuário
        $this->CI->db->where('username', $username);
        $this->CI->db->where('status', 'active');
        $user = $this->CI->db->get('users')->row();
        
        if (!$user) {
            return false;
        }
        
        // Verifica senha
        if (!password_verify($password, $user->password)) {
            $this->log_failed_attempt($username);
            return false;
        }
        
        // Gera token
        $token = $this->generate_token($user);
        
        // Registra login
        $this->log_login($user->id);
        
        return [
            'token' => $token,
            'user' => $this->format_user_data($user)
        ];
    }
    
    /**
     * Gera token JWT
     */
    private function generate_token($user) {
        $time = time();
        
        $payload = [
            'iss' => base_url(),
            'aud' => base_url(),
            'iat' => $time,
            'nbf' => $time,
            'exp' => $time + $this->config['token_expiration'],
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role
            ]
        ];
        
        // Header
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        
        // Codifica header e payload
        $header_encoded = $this->base64url_encode(json_encode($header));
        $payload_encoded = $this->base64url_encode(json_encode($payload));
        
        // Gera assinatura
        $signature = hash_hmac('sha256', 
            "$header_encoded.$payload_encoded", 
            $this->CI->config->item('encryption_key'),
            true
        );
        $signature_encoded = $this->base64url_encode($signature);
        
        // Retorna token completo
        return "$header_encoded.$payload_encoded.$signature_encoded";
    }
    
    /**
     * Valida token JWT
     */
    public function validate_token($token) {
        $parts = explode('.', $token);
        
        if (count($parts) != 3) {
            return false;
        }
        
        list($header_encoded, $payload_encoded, $signature_encoded) = $parts;
        
        // Recria assinatura
        $signature = hash_hmac('sha256', 
            "$header_encoded.$payload_encoded", 
            $this->CI->config->item('encryption_key'),
            true
        );
        $signature_check = $this->base64url_encode($signature);
        
        if ($signature_encoded !== $signature_check) {
            return false;
        }
        
        // Decodifica payload
        $payload = json_decode($this->base64url_decode($payload_encoded));
        
        // Verifica expiração
        if ($payload->exp < time()) {
            return false;
        }
        
        return $payload->data;
    }
    
    /**
     * Registra tentativa falha de login
     */
    private function log_failed_attempt($username) {
        $data = [
            'username' => $username,
            'ip_address' => $this->CI->input->ip_address(),
            'user_agent' => $this->CI->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->CI->db->insert('failed_logins', $data);
        
        // Verifica bloqueio
        $this->check_brute_force($username);
    }
    
    /**
     * Verifica tentativas de força bruta
     */
    private function check_brute_force($username) {
        $window = date('Y-m-d H:i:s', strtotime('-15 minutes'));
        
        $this->CI->db->where('username', $username);
        $this->CI->db->where('created_at >=', $window);
        $attempts = $this->CI->db->count_all_results('failed_logins');
        
        if ($attempts >= 5) {
            // Bloqueia usuário
            $this->CI->db->where('username', $username);
            $this->CI->db->update('users', ['status' => 'blocked']);
            
            // Registra bloqueio
            $this->log_security_event('user_blocked', [
                'username' => $username,
                'reason' => 'brute_force',
                'attempts' => $attempts
            ]);
        }
    }
    
    /**
     * Registra login bem-sucedido
     */
    private function log_login($user_id) {
        $data = [
            'user_id' => $user_id,
            'ip_address' => $this->CI->input->ip_address(),
            'user_agent' => $this->CI->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->CI->db->insert('login_history', $data);
    }
    
    /**
     * Registra evento de segurança
     */
    private function log_security_event($type, $data) {
        $event = [
            'type' => $type,
            'data' => json_encode($data),
            'ip_address' => $this->CI->input->ip_address(),
            'user_agent' => $this->CI->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->CI->db->insert('security_events', $event);
    }
    
    /**
     * Formata dados do usuário
     */
    private function format_user_data($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'permissions' => $this->get_user_permissions($user->id),
            'settings' => $this->get_user_settings($user->id),
            'last_login' => $this->get_last_login($user->id)
        ];
    }
    
    /**
     * Busca permissões do usuário
     */
    private function get_user_permissions($user_id) {
        $this->CI->db->select('permission');
        $this->CI->db->where('user_id', $user_id);
        $permissions = $this->CI->db->get('user_permissions')->result();
        
        return array_map(function($p) {
            return $p->permission;
        }, $permissions);
    }
    
    /**
     * Busca configurações do usuário
     */
    private function get_user_settings($user_id) {
        $this->CI->db->where('user_id', $user_id);
        return $this->CI->db->get('user_settings')->row();
    }
    
    /**
     * Busca último login
     */
    private function get_last_login($user_id) {
        $this->CI->db->where('user_id', $user_id);
        $this->CI->db->order_by('created_at', 'DESC');
        $this->CI->db->limit(1);
        $login = $this->CI->db->get('login_history')->row();
        
        return $login ? $login->created_at : null;
    }
    
    /**
     * Codifica string em base64url
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Decodifica string em base64url
     */
    private function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
