<?php
require_once(APPPATH . 'tests/TestCase.php');

class CartTest extends TestCase {
    
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
        
        // Cria produtos de teste
        $this->products = [];
        
        $this->products[] = $this->createTestProduct([
            'produtoModelo' => 'TEST001',
            'produtoNome' => 'Test Product 1',
            'produtoPreco' => 100.00,
            'produtoEstoque' => 50
        ]);
        
        $this->products[] = $this->createTestProduct([
            'produtoModelo' => 'TEST002',
            'produtoNome' => 'Test Product 2',
            'produtoPreco' => 200.00,
            'produtoEstoque' => 0
        ]);
        
        // Cria lead/carrinho de teste
        $this->lead_id = $this->createTestLead([
            'usuarioPOID' => $this->user_id,
            'clientePOID' => $this->customer_id,
            'status' => 'pending'
        ]);
    }
    
    public function testGetCartEmpty() {
        $response = $this->makeRequest('GET', '/api/v1/cart', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('totals', $data);
        $this->assertEquals(0, count($data['items']));
        $this->assertEquals(0, $data['totals']['total_items']);
        $this->assertEquals(0, $data['totals']['total_quantity']);
        $this->assertEquals(0, $data['totals']['total_value']);
    }
    
    public function testAddToCartSuccess() {
        $response = $this->makeRequest('POST', '/api/v1/cart', [
            'product_id' => $this->products[0],
            'quantity' => 2
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertArrayHasKey('items', $data);
        $this->assertEquals(1, count($data['items']));
        
        $item = $data['items'][0];
        $this->assertEquals($this->products[0], $item->produtoPOID);
        $this->assertEquals(2, $item->quantidade);
        $this->assertEquals(100.00, $item->valorUnitario);
        $this->assertEquals(200.00, $item->valorTotal);
    }
    
    public function testAddToCartOutOfStock() {
        $response = $this->makeRequest('POST', '/api/v1/cart', [
            'product_id' => $this->products[1], // Produto sem estoque
            'quantity' => 1
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 400);
    }
    
    public function testAddToCartInvalidQuantity() {
        $response = $this->makeRequest('POST', '/api/v1/cart', [
            'product_id' => $this->products[0],
            'quantity' => 0
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 400);
        
        $response = $this->makeRequest('POST', '/api/v1/cart', [
            'product_id' => $this->products[0],
            'quantity' => 51 // Maior que o estoque
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 400);
    }
    
    public function testAddToCartDuplicate() {
        // Adiciona produto pela primeira vez
        $response = $this->makeRequest('POST', '/api/v1/cart', [
            'product_id' => $this->products[0],
            'quantity' => 2
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertSuccessResponse($response);
        
        // Adiciona o mesmo produto novamente
        $response = $this->makeRequest('POST', '/api/v1/cart', [
            'product_id' => $this->products[0],
            'quantity' => 3
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals(1, count($data['items']));
        $this->assertEquals(5, $data['items'][0]->quantidade);
    }
    
    public function testUpdateCartItemSuccess() {
        // Adiciona item ao carrinho
        $response = $this->makeRequest('POST', '/api/v1/cart', [
            'product_id' => $this->products[0],
            'quantity' => 2
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $item_id = $data['items'][0]->id;
        
        // Atualiza quantidade
        $response = $this->makeRequest('PUT', '/api/v1/cart', [
            'item_id' => $item_id,
            'quantity' => 3
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals(1, count($data['items']));
        $this->assertEquals(3, $data['items'][0]->quantidade);
        $this->assertEquals(300.00, $data['items'][0]->valorTotal);
    }
    
    public function testUpdateCartItemPrice() {
        // Adiciona item ao carrinho
        $response = $this->makeRequest('POST', '/api/v1/cart', [
            'product_id' => $this->products[0],
            'quantity' => 2
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $item_id = $data['items'][0]->id;
        
        // Atualiza preço
        $response = $this->makeRequest('PUT', '/api/v1/cart', [
            'item_id' => $item_id,
            'price' => 90.00
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals(90.00, $data['items'][0]->valorUnitario);
        $this->assertEquals(180.00, $data['items'][0]->valorTotal);
    }
    
    public function testUpdateCartItemDiscount() {
        // Adiciona item ao carrinho
        $response = $this->makeRequest('POST', '/api/v1/cart', [
            'product_id' => $this->products[0],
            'quantity' => 2
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $item_id = $data['items'][0]->id;
        
        // Aplica desconto
        $response = $this->makeRequest('PUT', '/api/v1/cart', [
            'item_id' => $item_id,
            'discount' => 20.00
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals(20.00, $data['items'][0]->valorDesconto);
        $this->assertEquals(180.00, $data['items'][0]->valorTotal);
    }
    
    public function testRemoveFromCartSuccess() {
        // Adiciona item ao carrinho
        $response = $this->makeRequest('POST', '/api/v1/cart', [
            'product_id' => $this->products[0],
            'quantity' => 2
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $item_id = $data['items'][0]->id;
        
        // Remove item
        $response = $this->makeRequest('DELETE', '/api/v1/cart/' . $item_id, null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals(0, count($data['items']));
    }
    
    public function testRemoveFromCartNotFound() {
        $response = $this->makeRequest('DELETE', '/api/v1/cart/999999', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 404);
    }
    
    public function testClearCartSuccess() {
        // Adiciona alguns itens ao carrinho
        $this->makeRequest('POST', '/api/v1/cart', [
            'product_id' => $this->products[0],
            'quantity' => 2
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        // Limpa carrinho
        $response = $this->makeRequest('DELETE', '/api/v1/cart', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals(0, count($data['items']));
        $this->assertEquals(0, $data['totals']['total_items']);
        $this->assertEquals(0, $data['totals']['total_quantity']);
        $this->assertEquals(0, $data['totals']['total_value']);
    }
}


