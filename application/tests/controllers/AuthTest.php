<?php
require_once(APPPATH . 'tests/TestCase.php');

class AuthTest extends TestCase {
    
    public function setUp() {
        parent::setUp();
        
        // Cria usuário de teste
        $this->user_id = $this->createTestUser();
    }
    
    public function testLoginSuccess() {
        $response = $this->makeRequest('POST', '/api/v1/auth', [
            'username' => 'test@example.com',
            'password' => 'test123'
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('app', $data);
        $this->assertArrayHasKey('features', $data);
        
        $this->assertEquals('Test User', $data['user']['name']);
        $this->assertEquals('test@example.com', $data['user']['email']);
        $this->assertEquals('admin', $data['user']['role']);
    }
    
    public function testLoginInvalidCredentials() {
        $response = $this->makeRequest('POST', '/api/v1/auth', [
            'username' => 'test@example.com',
            'password' => 'wrong_password'
        ]);
        
        $this->assertErrorResponse($response, 401);
    }
    
    public function testLoginMissingFields() {
        $response = $this->makeRequest('POST', '/api/v1/auth', [
            'username' => 'test@example.com'
        ]);
        
        $this->assertErrorResponse($response, 400);
        
        $response = $this->makeRequest('POST', '/api/v1/auth', [
            'password' => 'test123'
        ]);
        
        $this->assertErrorResponse($response, 400);
    }
    
    public function testLoginInactiveUser() {
        // Atualiza usuário para inativo
        $this->CI->db->where('usuarioPOID', $this->user_id);
        $this->CI->db->update('Usuarios', ['usuarioStatus' => 'inactive']);
        
        $response = $this->makeRequest('POST', '/api/v1/auth', [
            'username' => 'test@example.com',
            'password' => 'test123'
        ]);
        
        $this->assertErrorResponse($response, 401);
    }
    
    public function testLoginBlockedUser() {
        // Atualiza usuário para bloqueado
        $this->CI->db->where('usuarioPOID', $this->user_id);
        $this->CI->db->update('Usuarios', ['usuarioStatus' => 'blocked']);
        
        $response = $this->makeRequest('POST', '/api/v1/auth', [
            'username' => 'test@example.com',
            'password' => 'test123'
        ]);
        
        $this->assertErrorResponse($response, 401);
    }
    
    public function testRefreshTokenSuccess() {
        $token = $this->createTestToken([
            'id' => $this->user_id
        ]);
        
        $response = $this->makeRequest('POST', '/api/v1/refresh-token', null, [
            'Authorization' => 'Bearer ' . $token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEquals($token, $data['token']);
    }
    
    public function testRefreshTokenInvalid() {
        $response = $this->makeRequest('POST', '/api/v1/refresh-token', null, [
            'Authorization' => 'Bearer invalid_token'
        ]);
        
        $this->assertErrorResponse($response, 401);
    }
    
    public function testRefreshTokenMissing() {
        $response = $this->makeRequest('POST', '/api/v1/refresh-token');
        
        $this->assertErrorResponse($response, 401);
    }
    
    public function testLogoutSuccess() {
        $token = $this->createTestToken([
            'id' => $this->user_id
        ]);
        
        $response = $this->makeRequest('POST', '/api/v1/logout', null, [
            'Authorization' => 'Bearer ' . $token
        ]);
        
        $this->assertSuccessResponse($response);
        
        // Verifica se token foi invalidado
        $response = $this->makeRequest('GET', '/api/v1/profile', null, [
            'Authorization' => 'Bearer ' . $token
        ]);
        
        $this->assertErrorResponse($response, 401);
    }
    
    public function testLogoutWithDevice() {
        $token = $this->createTestToken([
            'id' => $this->user_id
        ]);
        
        $response = $this->makeRequest('POST', '/api/v1/logout', [
            'device_id' => 'test_device_123'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        
        $this->assertSuccessResponse($response);
        
        // Verifica se dispositivo foi removido
        $this->CI->db->where('usuarioPOID', $this->user_id);
        $this->CI->db->where('deviceId', 'test_device_123');
        $this->assertEquals(0, $this->CI->db->count_all_results('UsuarioDispositivos'));
    }
    
    public function testBruteForceProtection() {
        // Tenta login 5 vezes com senha errada
        for ($i = 0; $i < 5; $i++) {
            $response = $this->makeRequest('POST', '/api/v1/auth', [
                'username' => 'test@example.com',
                'password' => 'wrong_password'
            ]);
            
            $this->assertErrorResponse($response, 401);
        }
        
        // Verifica se usuário foi bloqueado
        $this->CI->db->where('usuarioPOID', $this->user_id);
        $user = $this->CI->db->get('Usuarios')->row();
        
        $this->assertEquals('blocked', $user->usuarioStatus);
        
        // Tenta login com senha correta
        $response = $this->makeRequest('POST', '/api/v1/auth', [
            'username' => 'test@example.com',
            'password' => 'test123'
        ]);
        
        $this->assertErrorResponse($response, 401);
    }
}



