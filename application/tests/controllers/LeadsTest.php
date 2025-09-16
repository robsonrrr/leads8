<?php
require_once(APPPATH . 'tests/TestCase.php');

class LeadsTest extends TestCase {
    
    public function setUp() {
        parent::setUp();
        
        // Cria usuário de teste
        $this->user_id = $this->createTestUser();
        
        // Cria token de teste
        $this->token = $this->createTestToken([
            'id' => $this->user_id
        ]);
        
        // Cria cliente de teste
        $this->customer_id = $this->createTestCustomer();
        
        // Cria lead de teste
        $this->lead_id = $this->createTestLead([
            'usuarioPOID' => $this->user_id,
            'clientePOID' => $this->customer_id
        ]);
    }
    
    public function testListLeadsSuccess() {
        $response = $this->makeRequest('GET', '/api/v1/leads', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('limit', $data);
        $this->assertArrayHasKey('total_pages', $data);
        $this->assertArrayHasKey('leads', $data);
        
        $this->assertGreaterThan(0, count($data['leads']));
        
        $lead = $data['leads'][0];
        $this->assertEquals($this->lead_id, $lead->id);
        $this->assertEquals($this->customer_id, $lead->clientePOID);
        $this->assertEquals('pending', $lead->status);
    }
    
    public function testListLeadsWithFilters() {
        // Cria mais alguns leads para testar filtros
        $this->createTestLead([
            'usuarioPOID' => $this->user_id,
            'clientePOID' => $this->customer_id,
            'status' => 'completed'
        ]);
        
        $this->createTestLead([
            'usuarioPOID' => $this->user_id,
            'clientePOID' => $this->customer_id,
            'status' => 'cancelled'
        ]);
        
        // Testa filtro por status
        $response = $this->makeRequest('GET', '/api/v1/leads?status=completed', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(1, count($data['leads']));
        $this->assertEquals('completed', $data['leads'][0]->status);
        
        // Testa filtro por data
        $response = $this->makeRequest('GET', '/api/v1/leads?date_start=' . date('Y-m-d'), null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(3, count($data['leads']));
        
        // Testa paginação
        $response = $this->makeRequest('GET', '/api/v1/leads?limit=1', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(1, count($data['leads']));
        $this->assertEquals(3, $data['total']);
        $this->assertEquals(3, $data['total_pages']);
    }
    
    public function testGetLeadSuccess() {
        $response = $this->makeRequest('GET', '/api/v1/leads/' . $this->lead_id, null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals($this->lead_id, $data->id);
        $this->assertEquals($this->customer_id, $data->clientePOID);
        $this->assertEquals('pending', $data->status);
        
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('customer', $data);
        $this->assertArrayHasKey('history', $data);
    }
    
    public function testGetLeadNotFound() {
        $response = $this->makeRequest('GET', '/api/v1/leads/999999', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 404);
    }
    
    public function testCreateLeadSuccess() {
        $product_id = $this->createTestProduct();
        
        $response = $this->makeRequest('POST', '/api/v1/leads', [
            'customer_id' => $this->customer_id,
            'items' => [
                [
                    'product_id' => $product_id,
                    'quantity' => 2,
                    'unit_price' => 100.00
                ]
            ],
            'notes' => 'Test lead'
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($this->customer_id, $data->clientePOID);
        $this->assertEquals('pending', $data->status);
        $this->assertEquals('Test lead', $data->observacoes);
        
        $this->assertEquals(1, count($data->items));
        $this->assertEquals($product_id, $data->items[0]->produtoPOID);
        $this->assertEquals(2, $data->items[0]->quantidade);
        $this->assertEquals(100.00, $data->items[0]->valorUnitario);
        $this->assertEquals(200.00, $data->items[0]->valorTotal);
    }
    
    public function testCreateLeadValidation() {
        // Testa sem cliente
        $response = $this->makeRequest('POST', '/api/v1/leads', [
            'items' => []
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 400);
        
        // Testa com produto inválido
        $response = $this->makeRequest('POST', '/api/v1/leads', [
            'customer_id' => $this->customer_id,
            'items' => [
                [
                    'product_id' => 999999,
                    'quantity' => 1,
                    'unit_price' => 100.00
                ]
            ]
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 400);
        
        // Testa com quantidade inválida
        $product_id = $this->createTestProduct();
        
        $response = $this->makeRequest('POST', '/api/v1/leads', [
            'customer_id' => $this->customer_id,
            'items' => [
                [
                    'product_id' => $product_id,
                    'quantity' => 0,
                    'unit_price' => 100.00
                ]
            ]
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 400);
    }
    
    public function testUpdateLeadSuccess() {
        $response = $this->makeRequest('PUT', '/api/v1/leads/' . $this->lead_id, [
            'status' => 'completed',
            'notes' => 'Updated lead'
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals($this->lead_id, $data->id);
        $this->assertEquals('completed', $data->status);
        $this->assertEquals('Updated lead', $data->observacoes);
    }
    
    public function testUpdateLeadItems() {
        $product_id = $this->createTestProduct();
        
        $response = $this->makeRequest('PUT', '/api/v1/leads/' . $this->lead_id, [
            'items' => [
                [
                    'product_id' => $product_id,
                    'quantity' => 2,
                    'unit_price' => 100.00
                ]
            ]
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals(1, count($data->items));
        $this->assertEquals($product_id, $data->items[0]->produtoPOID);
        $this->assertEquals(2, $data->items[0]->quantidade);
        $this->assertEquals(100.00, $data->items[0]->valorUnitario);
        $this->assertEquals(200.00, $data->items[0]->valorTotal);
    }
    
    public function testUpdateLeadNotFound() {
        $response = $this->makeRequest('PUT', '/api/v1/leads/999999', [
            'status' => 'completed'
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 404);
    }
    
    public function testDeleteLeadSuccess() {
        $response = $this->makeRequest('DELETE', '/api/v1/leads/' . $this->lead_id, null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertSuccessResponse($response);
        
        // Verifica se lead foi removido
        $this->CI->db->where('id', $this->lead_id);
        $this->assertEquals(0, $this->CI->db->count_all_results('Leads'));
    }
    
    public function testDeleteLeadNotFound() {
        $response = $this->makeRequest('DELETE', '/api/v1/leads/999999', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 404);
    }
}


