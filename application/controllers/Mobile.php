<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mobile extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Carregar modelos necessários
        $this->load->model('Lead_model');
        $this->load->model('Product_model');
        $this->load->model('Cart_model');
        $this->load->model('Customer_model');
        $this->load->model('Mobile_model');
        
        // Carregar bibliotecas
        $this->load->library('Mobile_auth_lib');
        
        // Carregar helpers
        $this->load->helper('url');
        $this->load->helper('form');
        
        // Carregar configurações
        $this->load->config('mobile');
        
        // Verificar autenticação via API token
        $this->_check_api_auth();
    }
    
    /**
     * Verifica autenticação via token API
     */
    private function _check_api_auth() {
        // Verifica se é uma rota pública
        $uri = $this->uri->uri_string();
        $public_routes = $this->config->item('public_endpoints', 'mobile');
        
        foreach ($public_routes as $route) {
            if (strpos($uri, $route) !== false) {
                return;
            }
        }
        
        // Obtém token
        $token = $this->input->get_request_header('Authorization');
        if (!$token) {
            $this->_send_error('Unauthorized - No token provided', 401);
            return;
        }
        
        // Remove 'Bearer ' se presente
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        // Valida token
        $user_data = $this->mobile_auth_lib->validate_token($token);
        if (!$user_data) {
            $this->_send_error('Unauthorized - Invalid token', 401);
            return;
        }
        
        // Armazena dados do usuário para uso posterior
        $this->user_data = $user_data;
    }
    
    /**
     * Envia resposta de erro
     */
    private function _send_error($message, $code = 400) {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output(json_encode([
                'error' => true,
                'message' => $message
            ]));
    }
    
    /**
     * Envia resposta de sucesso
     */
    private function _send_success($data, $code = 200) {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output(json_encode([
                'error' => false,
                'data' => $data
            ]));
    }
    
    /**
     * Endpoint inicial que retorna configurações e dados básicos
     */
    public function index() {
        $data = [
            'version' => '1.0.0',
            'api_version' => '1',
            'min_version' => '1.0.0',
            'force_update' => false,
            'maintenance' => false,
            'features' => [
                'offline_mode' => true,
                'biometric_auth' => true,
                'push_notifications' => true
            ]
        ];
        
        $this->_send_success($data);
    }
    
    /**
     * Autenticação do usuário
     */
    public function auth() {
        // Valida método
        if ($this->input->method() !== 'post') {
            $this->_send_error('Method not allowed', 405);
            return;
        }
        
        // Obtém credenciais
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $device_id = $this->input->post('device_id');
        
        // Valida campos obrigatórios
        if (!$username || !$password) {
            $this->_send_error('Username and password are required');
            return;
        }
        
        // Tenta autenticar
        $auth_result = $this->mobile_auth_lib->authenticate($username, $password);
        
        if (!$auth_result) {
            $this->_send_error('Invalid credentials', 401);
            return;
        }
        
        // Registra dispositivo se fornecido
        if ($device_id) {
            $this->Mobile_model->register_device(
                $auth_result['user']->id,
                $device_id,
                $this->input->post('device_name'),
                $this->input->post('device_platform'),
                $this->input->post('device_version')
            );
        }
        
        // Formata resposta
        $response = [
            'token' => $auth_result['token'],
            'user' => $auth_result['user'],
            'app' => [
                'version' => $this->config->item('api_version', 'mobile'),
                'min_version' => $this->config->item('min_app_version', 'mobile'),
                'force_update' => false
            ],
            'features' => $this->config->item('features', 'mobile')
        ];
        
        // Adiciona informações de sessão anterior se existir
        $last_session = $this->Mobile_model->get_last_session($auth_result['user']->id);
        if ($last_session) {
            $response['last_session'] = $last_session;
        }
        
        $this->_send_success($response);
    }
    
    /**
     * Refresh do token
     */
    public function refresh_token() {
        if (!$this->user_data) {
            $this->_send_error('Unauthorized', 401);
            return;
        }
        
        $new_token = $this->mobile_auth_lib->generate_token((object)$this->user_data);
        
        $this->_send_success([
            'token' => $new_token
        ]);
    }
    
    /**
     * Logout
     */
    public function logout() {
        if (!$this->user_data) {
            $this->_send_error('Unauthorized', 401);
            return;
        }
        
        // Registra logout
        $this->Mobile_model->log_logout($this->user_data->id);
        
        // Remove token do dispositivo se fornecido
        $device_id = $this->input->post('device_id');
        if ($device_id) {
            $this->Mobile_model->unregister_device(
                $this->user_data->id,
                $device_id
            );
        }
        
        $this->_send_success([
            'message' => 'Logged out successfully'
        ]);
    }
    
    /**
     * Lista de leads
     */
    public function leads() {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            // Obtém parâmetros
            $filters = [
                'search' => $this->input->get('search'),
                'status' => $this->input->get('status'),
                'date_start' => $this->input->get('date_start'),
                'date_end' => $this->input->get('date_end'),
                'min_value' => $this->input->get('min_value'),
                'max_value' => $this->input->get('max_value'),
                'sort_by' => $this->input->get('sort_by'),
                'sort_order' => $this->input->get('sort_order')
            ];
            
            $page = (int)($this->input->get('page') ?? 1);
            $limit = (int)($this->input->get('limit') ?? $this->config->item('pagination')['default_limit']);
            
            // Valida limite
            $max_limit = $this->config->item('pagination')['max_limit'];
            if ($limit > $max_limit) {
                $limit = $max_limit;
            }
            
            // Busca leads
            $result = $this->Mobile_model->get_leads(
                $this->user_data->id,
                $filters,
                $page,
                $limit
            );
            
            $this->_send_success($result);
            
        } catch (Exception $e) {
            log_message('error', 'Error in leads: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Detalhes de um lead
     */
    public function lead($id = null) {
        try {
            // Valida método
            if ($this->input->method() === 'get') {
                $this->_get_lead($id);
            } else if ($this->input->method() === 'post') {
                $this->_create_lead();
            } else if ($this->input->method() === 'put') {
                $this->_update_lead($id);
            } else if ($this->input->method() === 'delete') {
                $this->_delete_lead($id);
            } else {
                $this->_send_error('Method not allowed', 405);
            }
        } catch (Exception $e) {
            log_message('error', 'Error in lead: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Obtém detalhes de um lead
     */
    private function _get_lead($id) {
        if (!$id) {
            $this->_send_error('Lead ID is required');
            return;
        }
        
        $lead = $this->Mobile_model->get_lead($id);
        
        if (!$lead) {
            $this->_send_error('Lead not found', 404);
            return;
        }
        
        $this->_send_success($lead);
    }
    
    /**
     * Cria novo lead
     */
    private function _create_lead() {
        // Valida dados
        $data = json_decode($this->input->raw_input_stream, true);
        
        if (!$data) {
            $this->_send_error('Invalid request data');
            return;
        }
        
        if (empty($data['customer_id'])) {
            $this->_send_error('Customer ID is required');
            return;
        }
        
        // Cria lead
        $lead_id = $this->Mobile_model->create_lead(
            $this->user_data->id,
            $data
        );
        
        if (!$lead_id) {
            $this->_send_error('Error creating lead');
            return;
        }
        
        // Retorna lead criado
        $lead = $this->Mobile_model->get_lead($lead_id);
        $this->_send_success($lead, 201);
    }
    
    /**
     * Atualiza lead
     */
    private function _update_lead($id) {
        if (!$id) {
            $this->_send_error('Lead ID is required');
            return;
        }
        
        // Valida dados
        $data = json_decode($this->input->raw_input_stream, true);
        
        if (!$data) {
            $this->_send_error('Invalid request data');
            return;
        }
        
        // Atualiza lead
        $success = $this->Mobile_model->update_lead(
            $id,
            $this->user_data->id,
            $data
        );
        
        if (!$success) {
            $this->_send_error('Error updating lead');
            return;
        }
        
        // Retorna lead atualizado
        $lead = $this->Mobile_model->get_lead($id);
        $this->_send_success($lead);
    }
    
    /**
     * Remove lead
     */
    private function _delete_lead($id) {
        if (!$id) {
            $this->_send_error('Lead ID is required');
            return;
        }
        
        // Verifica permissão
        if (!$this->Mobile_model->can_edit_lead($id, $this->user_data->id)) {
            $this->_send_error('Permission denied', 403);
            return;
        }
        
        // Remove lead
        $success = $this->Mobile_model->delete_lead($id);
        
        if (!$success) {
            $this->_send_error('Error deleting lead');
            return;
        }
        
        $this->_send_success(['message' => 'Lead deleted successfully']);
    }
    
    /**
     * Detalhes de um lead específico
     */
    public function lead($id = null) {
        if (!$id) {
            $this->_send_error('Lead ID is required');
            return;
        }
        
        // TODO: Implementar busca real do lead
        // Por enquanto retorna dados fake
        $data = [
            'id' => $id,
            'customer' => [
                'id' => 1,
                'name' => 'Cliente Teste',
                'document' => '123.456.789-00',
                'email' => 'cliente@teste.com'
            ],
            'items' => [
                [
                    'id' => 1,
                    'product_id' => 100,
                    'name' => 'Produto Teste',
                    'quantity' => 2,
                    'price' => 150.00,
                    'total' => 300.00
                ]
                // Mais itens aqui...
            ],
            'totals' => [
                'items' => 5,
                'quantity' => 10,
                'value' => 1500.00
            ],
            'created_at' => '2025-09-10 10:00:00',
            'updated_at' => '2025-09-10 10:30:00',
            'status' => 'pending'
        ];
        
        $this->_send_success($data);
    }
    
    /**
     * Lista de produtos
     */
    public function products() {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            // Obtém parâmetros
            $filters = [
                'search' => $this->input->get('search'),
                'category' => $this->input->get('category'),
                'brand' => $this->input->get('brand'),
                'stock' => $this->input->get('stock'),
                'min_price' => $this->input->get('min_price'),
                'max_price' => $this->input->get('max_price'),
                'sort_by' => $this->input->get('sort_by'),
                'sort_order' => $this->input->get('sort_order')
            ];
            
            $page = (int)($this->input->get('page') ?? 1);
            $limit = (int)($this->input->get('limit') ?? $this->config->item('pagination')['default_limit']);
            
            // Valida limite
            $max_limit = $this->config->item('pagination')['max_limit'];
            if ($limit > $max_limit) {
                $limit = $max_limit;
            }
            
            // Busca produtos
            $result = $this->Mobile_model->get_products(
                $filters,
                $page,
                $limit
            );
            
            // Adiciona informações extras
            $result['filters'] = $this->_get_product_filters();
            
            $this->_send_success($result);
            
        } catch (Exception $e) {
            log_message('error', 'Error in products: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Detalhes de um produto
     */
    public function product($id = null) {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            if (!$id) {
                $this->_send_error('Product ID is required');
                return;
            }
            
            // Busca produto
            $product = $this->Mobile_model->get_product($id);
            
            if (!$product) {
                $this->_send_error('Product not found', 404);
                return;
            }
            
            $this->_send_success($product);
            
        } catch (Exception $e) {
            log_message('error', 'Error in product: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Busca produtos por código de barras
     */
    public function product_barcode() {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            $barcode = $this->input->get('code');
            
            if (!$barcode) {
                $this->_send_error('Barcode is required');
                return;
            }
            
            // Busca produto
            $product = $this->Mobile_model->get_product_by_barcode($barcode);
            
            if (!$product) {
                $this->_send_error('Product not found', 404);
                return;
            }
            
            $this->_send_success($product);
            
        } catch (Exception $e) {
            log_message('error', 'Error in product_barcode: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Busca produtos por código
     */
    public function product_code() {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            $code = $this->input->get('code');
            
            if (!$code) {
                $this->_send_error('Product code is required');
                return;
            }
            
            // Busca produto
            $product = $this->Mobile_model->get_product_by_code($code);
            
            if (!$product) {
                $this->_send_error('Product not found', 404);
                return;
            }
            
            $this->_send_success($product);
            
        } catch (Exception $e) {
            log_message('error', 'Error in product_code: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Lista categorias de produtos
     */
    public function product_categories() {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            // Busca categorias
            $categories = $this->Mobile_model->get_product_categories();
            
            $this->_send_success([
                'categories' => $categories
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error in product_categories: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Lista marcas de produtos
     */
    public function product_brands() {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            // Busca marcas
            $brands = $this->Mobile_model->get_product_brands();
            
            $this->_send_success([
                'brands' => $brands
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error in product_brands: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Obtém filtros disponíveis para produtos
     */
    private function _get_product_filters() {
        return [
            'categories' => $this->Mobile_model->get_product_categories(),
            'brands' => $this->Mobile_model->get_product_brands(),
            'stock_locations' => [
                [
                    'id' => 1,
                    'name' => 'SP-Matriz'
                ],
                [
                    'id' => 6,
                    'name' => 'SC'
                ],
                [
                    'id' => 8,
                    'name' => 'SP-BF'
                ]
            ],
            'sort_options' => [
                [
                    'id' => 'code',
                    'name' => 'Código'
                ],
                [
                    'id' => 'name',
                    'name' => 'Nome'
                ],
                [
                    'id' => 'price',
                    'name' => 'Preço'
                ],
                [
                    'id' => 'stock',
                    'name' => 'Estoque'
                ],
                [
                    'id' => 'category',
                    'name' => 'Categoria'
                ],
                [
                    'id' => 'brand',
                    'name' => 'Marca'
                ]
            ]
        ];
    }
    
    /**
     * Gerenciamento do carrinho
     */
    public function cart() {
        try {
            $method = $this->input->method();
            
            if ($method === 'get') {
                $this->_get_cart();
            } else if ($method === 'post') {
                $this->_add_to_cart();
            } else if ($method === 'put') {
                $this->_update_cart();
            } else if ($method === 'delete') {
                $this->_clear_cart();
            } else {
                $this->_send_error('Method not allowed', 405);
            }
        } catch (Exception $e) {
            log_message('error', 'Error in cart: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Obtém carrinho atual
     */
    private function _get_cart() {
        $cart = $this->Mobile_model->get_cart($this->user_data->id);
        
        if (!$cart) {
            $this->_send_success([
                'items' => [],
                'totals' => [
                    'total_items' => 0,
                    'total_quantity' => 0,
                    'total_value' => 0,
                    'total_discount' => 0
                ]
            ]);
            return;
        }
        
        $this->_send_success($cart);
    }
    
    /**
     * Adiciona item ao carrinho
     */
    private function _add_to_cart() {
        $data = json_decode($this->input->raw_input_stream, true);
        
        if (!$data) {
            $this->_send_error('Invalid request data');
            return;
        }
        
        if (empty($data['product_id'])) {
            $this->_send_error('Product ID is required');
            return;
        }
        
        if (empty($data['quantity']) || $data['quantity'] < 1) {
            $this->_send_error('Invalid quantity');
            return;
        }
        
        // Verifica estoque
        $product = $this->Mobile_model->get_product($data['product_id']);
        if (!$product || $product->produtoEstoque < $data['quantity']) {
            $this->_send_error('Product out of stock');
            return;
        }
        
        // Adiciona ao carrinho
        $item_id = $this->Mobile_model->add_to_cart(
            $this->user_data->id,
            $data
        );
        
        if (!$item_id) {
            $this->_send_error('Error adding item to cart');
            return;
        }
        
        // Retorna carrinho atualizado
        $cart = $this->Mobile_model->get_cart($this->user_data->id);
        $this->_send_success($cart);
    }
    
    /**
     * Atualiza item do carrinho
     */
    private function _update_cart() {
        $data = json_decode($this->input->raw_input_stream, true);
        
        if (!$data) {
            $this->_send_error('Invalid request data');
            return;
        }
        
        if (empty($data['item_id'])) {
            $this->_send_error('Item ID is required');
            return;
        }
        
        // Valida quantidade se fornecida
        if (isset($data['quantity'])) {
            if ($data['quantity'] < 1) {
                $this->_send_error('Invalid quantity');
                return;
            }
            
            // Verifica estoque
            $item = $this->Mobile_model->get_cart_item($data['item_id']);
            if (!$item) {
                $this->_send_error('Item not found');
                return;
            }
            
            $product = $this->Mobile_model->get_product($item->produtoPOID);
            if (!$product || $product->produtoEstoque < $data['quantity']) {
                $this->_send_error('Product out of stock');
                return;
            }
        }
        
        // Atualiza item
        $success = $this->Mobile_model->update_cart_item(
            $data['item_id'],
            $data
        );
        
        if (!$success) {
            $this->_send_error('Error updating cart item');
            return;
        }
        
        // Retorna carrinho atualizado
        $cart = $this->Mobile_model->get_cart($this->user_data->id);
        $this->_send_success($cart);
    }
    
    /**
     * Remove item do carrinho
     */
    public function remove_from_cart($item_id = null) {
        try {
            // Valida método
            if ($this->input->method() !== 'delete') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            if (!$item_id) {
                $this->_send_error('Item ID is required');
                return;
            }
            
            // Remove item
            $success = $this->Mobile_model->remove_from_cart($item_id);
            
            if (!$success) {
                $this->_send_error('Error removing item from cart');
                return;
            }
            
            // Retorna carrinho atualizado
            $cart = $this->Mobile_model->get_cart($this->user_data->id);
            $this->_send_success($cart);
            
        } catch (Exception $e) {
            log_message('error', 'Error in remove_from_cart: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Limpa carrinho
     */
    private function _clear_cart() {
        $success = $this->Mobile_model->clear_cart($this->user_data->id);
        
        if (!$success) {
            $this->_send_error('Error clearing cart');
            return;
        }
        
        $this->_send_success([
            'message' => 'Cart cleared successfully',
            'items' => [],
            'totals' => [
                'total_items' => 0,
                'total_quantity' => 0,
                'total_value' => 0,
                'total_discount' => 0
            ]
        ]);
    }
    
    /**
     * Lista de clientes
     */
    public function customers() {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            // Obtém parâmetros
            $filters = [
                'search' => $this->input->get('search'),
                'status' => $this->input->get('status'),
                'type' => $this->input->get('type'),
                'state' => $this->input->get('state'),
                'city' => $this->input->get('city'),
                'sort_by' => $this->input->get('sort_by'),
                'sort_order' => $this->input->get('sort_order')
            ];
            
            $page = (int)($this->input->get('page') ?? 1);
            $limit = (int)($this->input->get('limit') ?? $this->config->item('pagination')['default_limit']);
            
            // Valida limite
            $max_limit = $this->config->item('pagination')['max_limit'];
            if ($limit > $max_limit) {
                $limit = $max_limit;
            }
            
            // Busca clientes
            $result = $this->Mobile_model->get_customers(
                $this->user_data->id,
                $filters,
                $page,
                $limit
            );
            
            // Adiciona informações extras
            $result['filters'] = $this->_get_customer_filters();
            
            $this->_send_success($result);
            
        } catch (Exception $e) {
            log_message('error', 'Error in customers: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Detalhes de um cliente
     */
    public function customer($id = null) {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            if (!$id) {
                $this->_send_error('Customer ID is required');
                return;
            }
            
            // Busca cliente
            $customer = $this->Mobile_model->get_customer($id);
            
            if (!$customer) {
                $this->_send_error('Customer not found', 404);
                return;
            }
            
            $this->_send_success($customer);
            
        } catch (Exception $e) {
            log_message('error', 'Error in customer: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Busca clientes por documento
     */
    public function customer_document() {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            $document = $this->input->get('document');
            
            if (!$document) {
                $this->_send_error('Document is required');
                return;
            }
            
            // Remove formatação
            $document = preg_replace('/[^0-9]/', '', $document);
            
            // Busca cliente
            $customer = $this->Mobile_model->get_customer_by_document($document);
            
            if (!$customer) {
                $this->_send_error('Customer not found', 404);
                return;
            }
            
            $this->_send_success($customer);
            
        } catch (Exception $e) {
            log_message('error', 'Error in customer_document: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Busca clientes por email
     */
    public function customer_email() {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            $email = $this->input->get('email');
            
            if (!$email) {
                $this->_send_error('Email is required');
                return;
            }
            
            // Busca cliente
            $customer = $this->Mobile_model->get_customer_by_email($email);
            
            if (!$customer) {
                $this->_send_error('Customer not found', 404);
                return;
            }
            
            $this->_send_success($customer);
            
        } catch (Exception $e) {
            log_message('error', 'Error in customer_email: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Lista endereços de um cliente
     */
    public function customer_addresses($customer_id = null) {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            if (!$customer_id) {
                $this->_send_error('Customer ID is required');
                return;
            }
            
            // Busca endereços
            $addresses = $this->Mobile_model->get_customer_addresses($customer_id);
            
            $this->_send_success([
                'addresses' => $addresses
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error in customer_addresses: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Lista contatos de um cliente
     */
    public function customer_contacts($customer_id = null) {
        try {
            // Valida método
            if ($this->input->method() !== 'get') {
                $this->_send_error('Method not allowed', 405);
                return;
            }
            
            if (!$customer_id) {
                $this->_send_error('Customer ID is required');
                return;
            }
            
            // Busca contatos
            $contacts = $this->Mobile_model->get_customer_contacts($customer_id);
            
            $this->_send_success([
                'contacts' => $contacts
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error in customer_contacts: ' . $e->getMessage());
            $this->_send_error('Internal server error', 500);
        }
    }
    
    /**
     * Obtém filtros disponíveis para clientes
     */
    private function _get_customer_filters() {
        return [
            'status' => [
                [
                    'id' => 'active',
                    'name' => 'Ativo'
                ],
                [
                    'id' => 'inactive',
                    'name' => 'Inativo'
                ],
                [
                    'id' => 'blocked',
                    'name' => 'Bloqueado'
                ]
            ],
            'type' => [
                [
                    'id' => 'pf',
                    'name' => 'Pessoa Física'
                ],
                [
                    'id' => 'pj',
                    'name' => 'Pessoa Jurídica'
                ]
            ],
            'sort_options' => [
                [
                    'id' => 'name',
                    'name' => 'Nome'
                ],
                [
                    'id' => 'email',
                    'name' => 'Email'
                ],
                [
                    'id' => 'document',
                    'name' => 'Documento'
                ],
                [
                    'id' => 'state',
                    'name' => 'Estado'
                ],
                [
                    'id' => 'city',
                    'name' => 'Cidade'
                ],
                [
                    'id' => 'leads',
                    'name' => 'Total de Leads'
                ],
                [
                    'id' => 'orders',
                    'name' => 'Total de Pedidos'
                ]
            ]
        ];
    }
    
    /**
     * Detalhes de um produto específico
     */
    public function product($id = null) {
        if (!$id) {
            $this->_send_error('Product ID is required');
            return;
        }
        
        // TODO: Implementar busca real do produto
        // Por enquanto retorna dados fake
        $data = [
            'id' => $id,
            'code' => 'PROD001',
            'name' => 'Produto Teste',
            'description' => 'Descrição completa do produto teste',
            'technical_details' => 'Detalhes técnicos do produto',
            'price' => 150.00,
            'stock' => [
                'total' => 50,
                'locations' => [
                    [
                        'id' => 1,
                        'name' => 'Depósito 1',
                        'quantity' => 30
                    ],
                    [
                        'id' => 2,
                        'name' => 'Depósito 2',
                        'quantity' => 20
                    ]
                ]
            ],
            'category' => [
                'id' => 1,
                'name' => 'Category 1'
            ],
            'images' => [
                [
                    'id' => 1,
                    'type' => 'main',
                    'thumb' => 'path/to/thumb.jpg',
                    'full' => 'path/to/full.jpg'
                ]
                // Mais imagens aqui...
            ],
            'attributes' => [
                [
                    'name' => 'Cor',
                    'value' => 'Azul'
                ],
                [
                    'name' => 'Tamanho',
                    'value' => 'Grande'
                ]
                // Mais atributos aqui...
            ]
        ];
        
        $this->_send_success($data);
    }
}
