<?php
require_once(APPPATH . 'tests/TestCase.php');

class ProductsTest extends TestCase {
    
    public function setUp() {
        parent::setUp();
        
        // Cria usuário de teste
        $this->user_id = $this->createTestUser();
        
        // Cria token de teste
        $this->token = $this->createTestToken([
            'id' => $this->user_id
        ]);
        
        // Cria produtos de teste
        $this->products = [];
        
        $this->products[] = $this->createTestProduct([
            'produtoModelo' => 'TEST001',
            'produtoNome' => 'Test Product 1',
            'produtoDescricao' => 'Description 1',
            'produtoPreco' => 100.00,
            'produtoEstoque' => 50,
            'categoriaPOID' => 1,
            'marcaPOID' => 1
        ]);
        
        $this->products[] = $this->createTestProduct([
            'produtoModelo' => 'TEST002',
            'produtoNome' => 'Test Product 2',
            'produtoDescricao' => 'Description 2',
            'produtoPreco' => 200.00,
            'produtoEstoque' => 0,
            'categoriaPOID' => 1,
            'marcaPOID' => 2
        ]);
        
        $this->products[] = $this->createTestProduct([
            'produtoModelo' => 'TEST003',
            'produtoNome' => 'Test Product 3',
            'produtoDescricao' => 'Description 3',
            'produtoPreco' => 300.00,
            'produtoEstoque' => 100,
            'categoriaPOID' => 2,
            'marcaPOID' => 1
        ]);
    }
    
    public function testListProductsSuccess() {
        $response = $this->makeRequest('GET', '/api/v1/products', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('limit', $data);
        $this->assertArrayHasKey('total_pages', $data);
        $this->assertArrayHasKey('products', $data);
        $this->assertArrayHasKey('filters', $data);
        
        $this->assertEquals(3, count($data['products']));
        
        $product = $data['products'][0];
        $this->assertEquals('TEST001', $product->produtoModelo);
        $this->assertEquals('Test Product 1', $product->produtoNome);
        $this->assertEquals(100.00, $product->produtoPreco);
        $this->assertEquals(50, $product->produtoEstoque);
        
        $this->assertArrayHasKey('images', $product);
        $this->assertArrayHasKey('attributes', $product);
        $this->assertArrayHasKey('stock', $product);
        $this->assertArrayHasKey('prices', $product);
    }
    
    public function testListProductsWithFilters() {
        // Testa busca por texto
        $response = $this->makeRequest('GET', '/api/v1/products?search=Product 2', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(1, count($data['products']));
        $this->assertEquals('TEST002', $data['products'][0]->produtoModelo);
        
        // Testa filtro por categoria
        $response = $this->makeRequest('GET', '/api/v1/products?category=1', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(2, count($data['products']));
        
        // Testa filtro por marca
        $response = $this->makeRequest('GET', '/api/v1/products?brand=1', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(2, count($data['products']));
        
        // Testa filtro por estoque
        $response = $this->makeRequest('GET', '/api/v1/products?stock=available', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(2, count($data['products']));
        
        // Testa filtro por preço
        $response = $this->makeRequest('GET', '/api/v1/products?min_price=200&max_price=300', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(2, count($data['products']));
    }
    
    public function testListProductsSorting() {
        // Testa ordenação por código
        $response = $this->makeRequest('GET', '/api/v1/products?sort_by=code&sort_order=DESC', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals('TEST003', $data['products'][0]->produtoModelo);
        
        // Testa ordenação por preço
        $response = $this->makeRequest('GET', '/api/v1/products?sort_by=price&sort_order=ASC', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(100.00, $data['products'][0]->produtoPreco);
        
        // Testa ordenação por estoque
        $response = $this->makeRequest('GET', '/api/v1/products?sort_by=stock&sort_order=DESC', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        $this->assertEquals(100, $data['products'][0]->produtoEstoque);
    }
    
    public function testGetProductSuccess() {
        $response = $this->makeRequest('GET', '/api/v1/products/' . $this->products[0], null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals('TEST001', $data->produtoModelo);
        $this->assertEquals('Test Product 1', $data->produtoNome);
        $this->assertEquals(100.00, $data->produtoPreco);
        $this->assertEquals(50, $data->produtoEstoque);
        
        $this->assertArrayHasKey('images', $data);
        $this->assertArrayHasKey('attributes', $data);
        $this->assertArrayHasKey('stock', $data);
        $this->assertArrayHasKey('prices', $data);
        $this->assertArrayHasKey('related', $data);
    }
    
    public function testGetProductNotFound() {
        $response = $this->makeRequest('GET', '/api/v1/products/999999', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 404);
    }
    
    public function testProductBarcodeSuccess() {
        // Adiciona código de barras ao produto
        $this->CI->db->where('produtoPOID', $this->products[0]);
        $this->CI->db->update('Produtos', [
            'produtoCodigoBarras' => '7891234567890'
        ]);
        
        $response = $this->makeRequest('GET', '/api/v1/product/barcode?code=7891234567890', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals('TEST001', $data->produtoModelo);
        $this->assertEquals('Test Product 1', $data->produtoNome);
    }
    
    public function testProductBarcodeNotFound() {
        $response = $this->makeRequest('GET', '/api/v1/product/barcode?code=7891234567891', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 404);
    }
    
    public function testProductCodeSuccess() {
        $response = $this->makeRequest('GET', '/api/v1/product/code?code=TEST001', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertEquals('TEST001', $data->produtoModelo);
        $this->assertEquals('Test Product 1', $data->produtoNome);
    }
    
    public function testProductCodeNotFound() {
        $response = $this->makeRequest('GET', '/api/v1/product/code?code=TEST999', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $this->assertErrorResponse($response, 404);
    }
    
    public function testProductCategoriesSuccess() {
        $response = $this->makeRequest('GET', '/api/v1/product/categories', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertArrayHasKey('categories', $data);
        $this->assertGreaterThan(0, count($data['categories']));
        
        $category = $data['categories'][0];
        $this->assertArrayHasKey('id', $category);
        $this->assertArrayHasKey('name', $category);
    }
    
    public function testProductBrandsSuccess() {
        $response = $this->makeRequest('GET', '/api/v1/product/brands', null, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        
        $data = $this->assertSuccessResponse($response);
        
        $this->assertArrayHasKey('brands', $data);
        $this->assertGreaterThan(0, count($data['brands']));
        
        $brand = $data['brands'][0];
        $this->assertArrayHasKey('id', $brand);
        $this->assertArrayHasKey('name', $brand);
        $this->assertArrayHasKey('formatted', $brand);
    }
}


