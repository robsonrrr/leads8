<?php
require_once(APPPATH . 'tests/TestCase.php');

class CustomersTest extends TestCase {
    
    public function setUp() {
        parent::setUp();
        
        // Cria usuário de teste
        $this->user_id = $this->createTestUser();
        
        // Cria token de teste
        $this->token = $this->createTestToken([
            'id' => $this->user_id
        ]);
        
        // Cria clientes de teste
        $this->customers = [];
        
        $this->customers[] = $this->createTestCustomer([
            'clienteNome' => 'Test Customer 1',
            'clienteEmail' => 'customer1@example.com',
            'clienteDocumento' => '12345678901',
            'clienteStatus' => 'active',
            'clienteTipo' => 'pf',
            'clienteEstado' => 'SP',
            'clienteCidade' => 'São Paulo',
            'gerentePOID' => $this->user_id
        ]);
        
        $this->customers[] = $this->createTestCustomer([
            'clienteNome' => 'Test Customer 2',
            'clienteEmail' => 'customer2@example.com',
            'clienteDocumento' => '98765432101',
            'clienteStatus' => 'inactive',
            'clienteTipo' => 'pj',
            'clienteEstado' => 'RJ',
            'clienteCidade' => 'Rio de Janeiro',
            'gerentePOID' => $this->user_id
        ]);
        
        $this->customers[] = $this->createTestCustomer([
            'clienteNome' => 'Test Customer 3',
            'clienteEmail' => 'customer3@example.com',
            'clienteDocumento' => '45678912301',
            'clienteStatus' => 'blocked',
            'clienteTipo' => 'pf',
            'clienteEstado' => 'SP',
            'clienteCidade' => 'Campinas',
            'gerentePOID' => 999 // Outro gerente
        ]);
    }
    
    public function testListCustomersSuccess() {
        $response = $this->makeRequest('GET', '/api/v1/customers', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('limit', $data);
        $this->assertArrayHasKey('total_pages', $data);
        $this->assertArrayHasKey('customers', $data);
        $this->assertArrayHasKey('filters', $data);
        
        // Usuário normal só vê seus clientes
        $this->assertEquals(2, count($data['customers']));
        
        $customer = $data['customers'][0];
        $this->assertEquals('Test Customer 1', $customer->clienteNome);
        $this->assertEquals('customer1@example.com', $customer->clienteEmail);
        
        $this->assertArrayHasKey('addresses', $customer);
        $this->assertArrayHasKey('contacts', $customer);
        $this->assertArrayHasKey('stats', $customer);
    }
    
    public function testListCustomersAdmin() {
        // Atualiza usuário para admin
        $this->CI->db->where('usuarioPOID', $this->user_id);
        $this->CI->db->update('Usuarios', ['usuarioRole' => 'admin']);
        
        $response = $this->makeRequest('GET', '/api/v1/customers', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        // Admin vê todos os clientes
        $this->assertEquals(3, count($data['customers']));
    }
    
    public function testListCustomersWithFilters() {
        // Testa busca por texto
        $response = $this->makeRequest('GET', '/api/v1/customers?search=Customer 2', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(1, count($data['customers']));
        $this->assertEquals('Test Customer 2', $data['customers'][0]->clienteNome);
        
        // Testa filtro por status
        $response = $this->makeRequest('GET', '/api/v1/customers?status=active', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(1, count($data['customers']));
        
        // Testa filtro por tipo
        $response = $this->makeRequest('GET', '/api/v1/customers?type=pj', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(1, count($data['customers']));
        
        // Testa filtro por estado
        $response = $this->makeRequest('GET', '/api/v1/customers?state=SP', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(1, count($data['customers']));
    }
    
    public function testListCustomersSorting() {
        // Testa ordenação por nome
        $response = $this->makeRequest('GET', '/api/v1/customers?sort_by=name&sort_order=DESC', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals('Test Customer 2', $data['customers'][0]->clienteNome);
        
        // Testa ordenação por email
        $response = $this->makeRequest('GET', '/api/v1/customers?sort_by=email&sort_order=ASC', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals('customer1@example.com', $data['customers'][0]->clienteEmail);
    }
    
    public function testGetCustomerSuccess() {
        $response = $this->makeRequest('GET', '/api/v1/customers/' . $this->customers[0], null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals('Test Customer 1', $data->clienteNome);
        $this->assertEquals('customer1@example.com', $data->clienteEmail);
        $this->assertEquals('12345678901', $data->clienteDocumento);
        
        $this->assertArrayHasKey('addresses', $data);
        $this->assertArrayHasKey('contacts', $data);
        $this->assertArrayHasKey('stats', $data);
        $this->assertArrayHasKey('recent_leads', $data);
        $this->assertArrayHasKey('recent_orders', $data);
        $this->assertArrayHasKey('payment_info', $data);
    }
    
    public function testGetCustomerNotFound() {
        $response = $this->makeRequest('GET', '/api/v1/customers/999999', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 404);
    }
    
    public function testGetCustomerUnauthorized() {
        $response = $this->makeRequest('GET', '/api/v1/customers/' . $this->customers[2], null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 403);
    }
    
    public function testCustomerDocumentSuccess() {
        $response = $this->makeRequest('GET', '/api/v1/customer/document?document=123.456.789-01', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals('Test Customer 1', $data->clienteNome);
        $this->assertEquals('12345678901', $data->clienteDocumento);
    }
    
    public function testCustomerDocumentNotFound() {
        $response = $this->makeRequest('GET', '/api/v1/customer/document?document=999.999.999-99', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 404);
    }
    
    public function testCustomerEmailSuccess() {
        $response = $this->makeRequest('GET', '/api/v1/customer/email?email=customer1@example.com', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals('Test Customer 1', $data->clienteNome);
        $this->assertEquals('customer1@example.com', $data->clienteEmail);
    }
    
    public function testCustomerEmailNotFound() {
        $response = $this->makeRequest('GET', '/api/v1/customer/email?email=notfound@example.com', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 404);
    }
    
    public function testCustomerAddressesSuccess() {
        // Cria endereço de teste
        $this->CI->db->insert('ClienteEnderecos', [
            'clientePOID' => $this->customers[0],
            'enderecoTipo' => 'faturamento',
            'enderecoLogradouro' => 'Rua Teste',
            'enderecoNumero' => '123',
            'enderecoBairro' => 'Centro',
            'enderecoCidade' => 'São Paulo',
            'enderecoEstado' => 'SP',
            'enderecoCEP' => '01234567',
            'enderecoPrincipal' => 1
        ]);
        
        $response = $this->makeRequest('GET', '/api/v1/customer/addresses/' . $this->customers[0], null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertArrayHasKey('addresses', $data);
        $this->assertEquals(1, count($data['addresses']));
        
        $address = $data['addresses'][0];
        $this->assertEquals('faturamento', $address->type);
        $this->assertEquals('Rua Teste', $address->street);
        $this->assertEquals('123', $address->number);
    }
    
    public function testCustomerContactsSuccess() {
        // Cria contato de teste
        $this->CI->db->insert('ClienteContatos', [
            'clientePOID' => $this->customers[0],
            'contatoNome' => 'Test Contact',
            'contatoCargo' => 'Manager',
            'contatoEmail' => 'contact@example.com',
            'contatoTelefone' => '1199999999',
            'contatoPrincipal' => 1
        ]);
        
        $response = $this->makeRequest('GET', '/api/v1/customer/contacts/' . $this->customers[0], null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertArrayHasKey('contacts', $data);
        $this->assertEquals(1, count($data['contacts']));
        
        $contact = $data['contacts'][0];
        $this->assertEquals('Test Contact', $contact->name);
        $this->assertEquals('Manager', $contact->role);
        $this->assertEquals('contact@example.com', $contact->email);
    }
}


