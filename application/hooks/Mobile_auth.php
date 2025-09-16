<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mobile_auth {
    
    private $CI;
    private $config;
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('mobile');
        $this->config = $this->CI->config->item('mobile');
    }
    
    /**
     * Verifica autenticação para rotas da API mobile
     */
    public function check_auth() {
        // Obtém a rota atual
        $uri = $this->CI->uri->uri_string();
        
        // Verifica se é uma rota da API mobile
        if (strpos($uri, 'api/v1') === 0) {
            // Verifica se é uma rota pública
            if ($this->is_public_route($uri)) {
                return;
            }
            
            // Verifica token de autenticação
            $token = $this->get_token();
            if (!$token) {
                $this->send_error('Unauthorized - No token provided', 401);
                return;
            }
            
            // Verifica validade do token
            if (!$this->validate_token($token)) {
                $this->send_error('Unauthorized - Invalid token', 401);
                return;
            }
            
            // Verifica rate limiting
            if ($this->config['security']['enable_rate_limiting']) {
                if (!$this->check_rate_limit($token)) {
                    $this->send_error('Too many requests', 429);
                    return;
                }
            }
            
            // Verifica IP whitelist
            if ($this->config['security']['enable_ip_whitelist']) {
                if (!$this->check_ip_whitelist()) {
                    $this->send_error('Forbidden - IP not allowed', 403);
                    return;
                }
            }
        }
    }
    
    /**
     * Verifica se é uma rota pública
     */
    private function is_public_route($uri) {
        foreach ($this->config['public_endpoints'] as $endpoint) {
            if (strpos($uri, $endpoint) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Obtém token do header
     */
    private function get_token() {
        $header = $this->CI->input->get_request_header('Authorization');
        if (!$header) {
            return null;
        }
        
        // Remove 'Bearer ' do início
        if (strpos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }
        
        return $header;
    }
    
    /**
     * Valida token
     */
    private function validate_token($token) {
        $this->CI->load->model('Mobile_model');
        return $this->CI->Mobile_model->check_api_token($token);
    }
    
    /**
     * Verifica rate limiting
     */
    private function check_rate_limit($token) {
        $key = 'rate_limit_' . md5($token);
        $this->CI->load->driver('cache');
        
        // Obtém contagem atual
        $count = (int)$this->CI->cache->get($key);
        
        if ($count >= $this->config['security']['rate_limit_requests']) {
            return false;
        }
        
        // Incrementa contagem
        if (!$count) {
            $this->CI->cache->save($key, 1, $this->config['security']['rate_limit_window']);
        } else {
            $this->CI->cache->save($key, $count + 1, $this->CI->cache->get_metadata($key)['expire']);
        }
        
        return true;
    }
    
    /**
     * Verifica IP whitelist
     */
    private function check_ip_whitelist() {
        $ip = $this->CI->input->ip_address();
        return in_array($ip, $this->config['security']['ip_whitelist']);
    }
    
    /**
     * Envia resposta de erro
     */
    private function send_error($message, $code) {
        $this->CI->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output(json_encode([
                'error' => true,
                'message' => $message
            ]))
            ->_display();
        exit;
    }
}
