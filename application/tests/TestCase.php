<?php
class TestCase extends PHPUnit_Framework_TestCase {
    
    protected $CI;
    
    public function setUp() {
        // Carrega instância do CodeIgniter
        $this->CI =& get_instance();
        
        // Carrega configurações
        $this->CI->load->config('mobile');
        
        // Carrega bibliotecas necessárias
        $this->CI->load->library('Mobile_auth_lib');
        
        // Limpa cache de banco
        $this->CI->db->cache_delete_all();
        
        // Inicia transação
        $this->CI->db->trans_start();
    }
    
    public function tearDown() {
        // Reverte transação
        $this->CI->db->trans_rollback();
    }
    
    /**
     * Cria token de teste
     */
    protected function createTestToken($user_data = []) {
        $default_data = [
            'id' => 1,
            'username' => 'test_user',
            'role' => 'admin'
        ];
        
        $data = array_merge($default_data, $user_data);
        return $this->CI->mobile_auth_lib->generate_token((object)$data);
    }
    
    /**
     * Cria usuário de teste
     */
    protected function createTestUser($data = []) {
        $default_data = [
            'usuarioNome' => 'Test User',
            'usuarioEmail' => 'test@example.com',
            'usuarioSenha' => password_hash('test123', PASSWORD_DEFAULT),
            'usuarioStatus' => 'active',
            'usuarioRole' => 'admin'
        ];
        
        $data = array_merge($default_data, $data);
        
        $this->CI->db->insert('Usuarios', $data);
        return $this->CI->db->insert_id();
    }
    
    /**
     * Cria cliente de teste
     */
    protected function createTestCustomer($data = []) {
        $default_data = [
            'clienteNome' => 'Test Customer',
            'clienteEmail' => 'customer@example.com',
            'clienteDocumento' => '12345678901',
            'clienteStatus' => 'active',
            'clienteTipo' => 'pf'
        ];
        
        $data = array_merge($default_data, $data);
        
        $this->CI->db->insert('Clientes', $data);
        return $this->CI->db->insert_id();
    }
    
    /**
     * Cria produto de teste
     */
    protected function createTestProduct($data = []) {
        $default_data = [
            'produtoModelo' => 'TEST001',
            'produtoNome' => 'Test Product',
            'produtoDescricao' => 'Test product description',
            'produtoPreco' => 100.00,
            'produtoEstoque' => 50
        ];
        
        $data = array_merge($default_data, $data);
        
        $this->CI->db->insert('Produtos', $data);
        return $this->CI->db->insert_id();
    }
    
    /**
     * Cria lead de teste
     */
    protected function createTestLead($data = []) {
        $default_data = [
            'status' => 'pending',
            'dataEmissao' => date('Y-m-d H:i:s')
        ];
        
        $data = array_merge($default_data, $data);
        
        $this->CI->db->insert('Leads', $data);
        return $this->CI->db->insert_id();
    }
    
    /**
     * Simula requisição
     */
    protected function makeRequest($method, $uri, $data = null, $headers = []) {
        // Configura método
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        
        // Configura URI
        $_SERVER['REQUEST_URI'] = $uri;
        
        // Configura headers
        foreach ($headers as $key => $value) {
            $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }
        
        // Configura input
        if ($data !== null) {
            $input = json_encode($data);
            $this->CI->input->_clean_input_data($input);
        }
        
        // Executa controller
        $this->CI->router->_set_routing();
        $class = $this->CI->router->fetch_class();
        $method = $this->CI->router->fetch_method();
        
        $controller = new $class();
        return $controller->$method();
    }
    
    /**
     * Assertions personalizados
     */
    protected function assertJsonResponse($response) {
        $this->assertJson($response);
        $data = json_decode($response, true);
        $this->assertArrayHasKey('error', $data);
        return $data;
    }
    
    protected function assertSuccessResponse($response) {
        $data = $this->assertJsonResponse($response);
        $this->assertFalse($data['error']);
        $this->assertArrayHasKey('data', $data);
        return $data['data'];
    }
    
    protected function assertErrorResponse($response, $code = null) {
        $data = $this->assertJsonResponse($response);
        $this->assertTrue($data['error']);
        $this->assertArrayHasKey('message', $data);
        
        if ($code !== null) {
            $this->assertEquals($code, http_response_code());
        }
        
        return $data['message'];
    }
}




