<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mobile_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Verifica token de API
     */
    public function check_api_token($token) {
        // TODO: Implementar verificação real do token
        return true;
    }
    
    /**
     * Autenticação do usuário
     */
    public function authenticate($username, $password) {
        $this->db->where('username', $username);
        $user = $this->db->get('users')->row();
        
        if (!$user) {
            return false;
        }
        
        // TODO: Implementar verificação real de senha
        // Por enquanto apenas verifica se existe
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ];
    }
    
    /**
     * Gera token de API
     */
    public function generate_api_token($user_id) {
        $token = bin2hex(random_bytes(32));
        
        $data = [
            'user_id' => $user_id,
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days'))
        ];
        
        $this->db->insert('api_tokens', $data);
        
        return $token;
    }
    
    /**
     * Busca leads com filtros
     */
    public function get_leads($user_id, $filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        // Query base
        $this->db->select('l.*, c.clienteNome as customer_name, c.clienteEmail as customer_email');
        $this->db->from('Leads l');
        $this->db->join('Clientes c', 'l.clientePOID = c.clientePOID');
        
        // Filtros de usuário
        $user = $this->get_user($user_id);
        if ($user->role !== 'admin') {
            $this->db->where('l.usuarioPOID', $user_id);
        }
        
        // Filtros de busca
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('c.clienteNome', $filters['search']);
            $this->db->or_like('c.clienteEmail', $filters['search']);
            $this->db->or_like('l.id', $filters['search']);
            $this->db->group_end();
        }
        
        // Filtros de status
        if (!empty($filters['status'])) {
            $this->db->where('l.status', $filters['status']);
        }
        
        // Filtros de data
        if (!empty($filters['date_start'])) {
            $this->db->where('l.dataEmissao >=', $filters['date_start']);
        }
        if (!empty($filters['date_end'])) {
            $this->db->where('l.dataEmissao <=', $filters['date_end']);
        }
        
        // Filtros de valor
        if (!empty($filters['min_value'])) {
            $this->db->where('l.valorTotal >=', $filters['min_value']);
        }
        if (!empty($filters['max_value'])) {
            $this->db->where('l.valorTotal <=', $filters['max_value']);
        }
        
        // Total de registros
        $total = $this->db->count_all_results('', false);
        
        // Paginação e ordenação
        $this->db->limit($limit, $offset);
        $this->db->order_by($filters['sort_by'] ?? 'l.dataEmissao', $filters['sort_order'] ?? 'DESC');
        
        // Executa query
        $leads = $this->db->get()->result();
        
        // Processa leads
        foreach ($leads as &$lead) {
            $lead->items = $this->get_lead_items($lead->id);
            $lead->totals = $this->get_lead_totals($lead->id);
            $lead->customer = $this->get_lead_customer($lead->clientePOID);
            $lead->history = $this->get_lead_history($lead->id);
            $lead->attachments = $this->get_lead_attachments($lead->id);
        }
        
        return [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
            'leads' => $leads
        ];
    }
    
    /**
     * Busca itens de um lead
     */
    private function get_lead_items($lead_id) {
        $this->db->select('li.*, p.produtoNome as product_name, p.produtoModelo as product_model');
        $this->db->from('LeadItems li');
        $this->db->join('Produtos p', 'li.produtoPOID = p.produtoPOID');
        $this->db->where('li.leadPOID', $lead_id);
        
        return $this->db->get()->result();
    }
    
    /**
     * Busca totais de um lead
     */
    private function get_lead_totals($lead_id) {
        $this->db->select('
            COUNT(*) as total_items,
            SUM(quantidade) as total_quantity,
            SUM(valorTotal) as total_value,
            SUM(valorDesconto) as total_discount
        ');
        $this->db->from('LeadItems');
        $this->db->where('leadPOID', $lead_id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Busca cliente de um lead
     */
    private function get_lead_customer($customer_id) {
        $this->db->select('
            clientePOID as id,
            clienteNome as name,
            clienteEmail as email,
            clienteTelefone as phone,
            clienteDocumento as document,
            clienteEndereco as address,
            clienteCidade as city,
            clienteEstado as state,
            clientePais as country
        ');
        $this->db->from('Clientes');
        $this->db->where('clientePOID', $customer_id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Busca histórico de um lead
     */
    private function get_lead_history($lead_id) {
        $this->db->select('
            h.*,
            u.usuarioNome as user_name
        ');
        $this->db->from('LeadHistory h');
        $this->db->join('Usuarios u', 'h.usuarioPOID = u.usuarioPOID');
        $this->db->where('h.leadPOID', $lead_id);
        $this->db->order_by('h.dataHora', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Busca anexos de um lead
     */
    private function get_lead_attachments($lead_id) {
        $this->db->select('
            attachmentPOID as id,
            tipo as type,
            nome as name,
            tamanho as size,
            dataUpload as upload_date,
            url
        ');
        $this->db->from('LeadAttachments');
        $this->db->where('leadPOID', $lead_id);
        
        return $this->db->get()->result();
    }
    
    /**
     * Cria novo lead
     */
    public function create_lead($user_id, $data) {
        // Dados básicos do lead
        $lead_data = [
            'usuarioPOID' => $user_id,
            'clientePOID' => $data['customer_id'],
            'status' => 'pending',
            'dataEmissao' => date('Y-m-d H:i:s'),
            'observacoes' => $data['notes'] ?? null
        ];
        
        // Inicia transação
        $this->db->trans_start();
        
        // Insere lead
        $this->db->insert('Leads', $lead_data);
        $lead_id = $this->db->insert_id();
        
        // Insere itens
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $item_data = [
                    'leadPOID' => $lead_id,
                    'produtoPOID' => $item['product_id'],
                    'quantidade' => $item['quantity'],
                    'valorUnitario' => $item['unit_price'],
                    'valorDesconto' => $item['discount'] ?? 0,
                    'valorTotal' => ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0)
                ];
                $this->db->insert('LeadItems', $item_data);
            }
        }
        
        // Registra histórico
        $this->add_lead_history($lead_id, $user_id, 'create', 'Lead criado');
        
        // Finaliza transação
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        
        return $lead_id;
    }
    
    /**
     * Atualiza lead
     */
    public function update_lead($lead_id, $user_id, $data) {
        // Verifica permissão
        if (!$this->can_edit_lead($lead_id, $user_id)) {
            return false;
        }
        
        // Dados para atualização
        $lead_data = array_intersect_key($data, array_flip([
            'status',
            'observacoes',
            'valorTotal',
            'valorDesconto'
        ]));
        
        // Inicia transação
        $this->db->trans_start();
        
        // Atualiza lead
        $this->db->where('id', $lead_id);
        $this->db->update('Leads', $lead_data);
        
        // Atualiza itens se fornecidos
        if (isset($data['items'])) {
            // Remove itens existentes
            $this->db->where('leadPOID', $lead_id);
            $this->db->delete('LeadItems');
            
            // Insere novos itens
            foreach ($data['items'] as $item) {
                $item_data = [
                    'leadPOID' => $lead_id,
                    'produtoPOID' => $item['product_id'],
                    'quantidade' => $item['quantity'],
                    'valorUnitario' => $item['unit_price'],
                    'valorDesconto' => $item['discount'] ?? 0,
                    'valorTotal' => ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0)
                ];
                $this->db->insert('LeadItems', $item_data);
            }
        }
        
        // Registra histórico
        $this->add_lead_history($lead_id, $user_id, 'update', 'Lead atualizado');
        
        // Finaliza transação
        $this->db->trans_complete();
        
        return $this->db->trans_status() !== FALSE;
    }
    
    /**
     * Verifica se usuário pode editar lead
     */
    private function can_edit_lead($lead_id, $user_id) {
        $user = $this->get_user($user_id);
        
        // Admins podem editar qualquer lead
        if ($user->role === 'admin') {
            return true;
        }
        
        // Outros usuários só podem editar seus próprios leads
        $this->db->where('id', $lead_id);
        $this->db->where('usuarioPOID', $user_id);
        return $this->db->count_all_results('Leads') > 0;
    }
    
    /**
     * Adiciona entrada no histórico do lead
     */
    private function add_lead_history($lead_id, $user_id, $action, $description) {
        $data = [
            'leadPOID' => $lead_id,
            'usuarioPOID' => $user_id,
            'acao' => $action,
            'descricao' => $description,
            'dataHora' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('LeadHistory', $data);
    }
    
    /**
     * Busca detalhes de um lead
     */
    public function get_lead($id) {
        $this->db->where('id', $id);
        $lead = $this->db->get('leads')->row();
        
        if (!$lead) {
            return false;
        }
        
        // Busca itens do lead
        $this->db->where('lead_id', $id);
        $items = $this->db->get('lead_items')->result();
        
        $lead->items = $items;
        
        return $lead;
    }
    
    /**
     * Busca produtos com filtros
     */
    public function get_products($filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        // Query base
        $this->db->select('
            p.*,
            c.categoriaNome as category_name,
            m.marcaNome as brand_name,
            m.marcaFormatado as brand_formatted
        ');
        $this->db->from('Produtos p');
        $this->db->join('Categorias c', 'p.categoriaPOID = c.categoriaPOID', 'left');
        $this->db->join('Marcas m', 'p.marcaPOID = m.marcaPOID', 'left');
        
        // Filtros de busca
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('p.produtoModelo', $filters['search']);
            $this->db->or_like('p.produtoNome', $filters['search']);
            $this->db->or_like('p.produtoDescricao', $filters['search']);
            $this->db->group_end();
        }
        
        // Filtros de categoria
        if (!empty($filters['category'])) {
            $this->db->where('p.categoriaPOID', $filters['category']);
        }
        
        // Filtros de marca
        if (!empty($filters['brand'])) {
            $this->db->where('p.marcaPOID', $filters['brand']);
        }
        
        // Filtros de estoque
        if (isset($filters['stock'])) {
            if ($filters['stock'] === 'available') {
                $this->db->where('p.produtoEstoque >', 0);
            } else if ($filters['stock'] === 'unavailable') {
                $this->db->where('p.produtoEstoque', 0);
            } else if (is_numeric($filters['stock'])) {
                // Estoque específico (1 = SP, 6 = SC, etc)
                $this->db->where('p.produtoEstoqueLocal_' . $filters['stock'] . ' >', 0);
            }
        }
        
        // Filtros de preço
        if (!empty($filters['min_price'])) {
            $this->db->where('p.produtoPreco >=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $this->db->where('p.produtoPreco <=', $filters['max_price']);
        }
        
        // Total de registros
        $total = $this->db->count_all_results('', false);
        
        // Paginação e ordenação
        $this->db->limit($limit, $offset);
        
        // Ordenação
        $sort_field = $filters['sort_by'] ?? 'p.produtoModelo';
        $sort_order = $filters['sort_order'] ?? 'ASC';
        
        // Mapeamento de campos de ordenação
        $sort_fields = [
            'code' => 'p.produtoModelo',
            'name' => 'p.produtoNome',
            'price' => 'p.produtoPreco',
            'stock' => 'p.produtoEstoque',
            'category' => 'c.categoriaNome',
            'brand' => 'm.marcaNome'
        ];
        
        $sort_field = $sort_fields[$sort_field] ?? $sort_field;
        $this->db->order_by($sort_field, $sort_order);
        
        // Executa query
        $products = $this->db->get()->result();
        
        // Processa produtos
        foreach ($products as &$product) {
            $product->images = $this->get_product_images($product->produtoPOID);
            $product->attributes = $this->get_product_attributes($product->produtoPOID);
            $product->stock = $this->get_product_stock($product->produtoPOID);
            $product->prices = $this->get_product_prices($product->produtoPOID);
            $product->related = $this->get_related_products($product->produtoPOID);
        }
        
        return [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
            'products' => $products
        ];
    }
    
    /**
     * Busca imagens de um produto
     */
    private function get_product_images($product_id) {
        $this->db->select('
            imagemPOID as id,
            imagemTipo as type,
            imagemURL as url,
            imagemOrdem as order,
            imagemPrincipal as is_main
        ');
        $this->db->from('ProdutoImagens');
        $this->db->where('produtoPOID', $product_id);
        $this->db->order_by('imagemPrincipal DESC, imagemOrdem ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Busca atributos de um produto
     */
    private function get_product_attributes($product_id) {
        $this->db->select('
            a.atributoPOID as id,
            a.atributoNome as name,
            a.atributoValor as value,
            a.atributoTipo as type,
            a.atributoUnidade as unit
        ');
        $this->db->from('ProdutoAtributos a');
        $this->db->where('a.produtoPOID', $product_id);
        $this->db->order_by('a.atributoOrdem ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Busca estoque de um produto
     */
    private function get_product_stock($product_id) {
        $this->db->select('
            produtoEstoque as total,
            produtoEstoqueLocal_1 as sp_matriz,
            produtoEstoqueLocal_6 as sc,
            produtoEstoqueLocal_8 as sp_bf,
            produtoEstoquePrevisao as forecast_date,
            produtoEstoquePrevisaoQtd as forecast_quantity
        ');
        $this->db->from('Produtos');
        $this->db->where('produtoPOID', $product_id);
        
        $stock = $this->db->get()->row();
        
        // Formata locais de estoque
        $locations = [];
        if ($stock->sp_matriz > 0) {
            $locations[] = [
                'id' => 1,
                'name' => 'SP-Matriz',
                'quantity' => $stock->sp_matriz
            ];
        }
        if ($stock->sc > 0) {
            $locations[] = [
                'id' => 6,
                'name' => 'SC',
                'quantity' => $stock->sc
            ];
        }
        if ($stock->sp_bf > 0) {
            $locations[] = [
                'id' => 8,
                'name' => 'SP-BF',
                'quantity' => $stock->sp_bf
            ];
        }
        
        return [
            'total' => $stock->total,
            'locations' => $locations,
            'forecast' => [
                'date' => $stock->forecast_date,
                'quantity' => $stock->forecast_quantity
            ]
        ];
    }
    
    /**
     * Busca preços de um produto
     */
    private function get_product_prices($product_id) {
        $this->db->select('
            produtoPreco as base_price,
            produtoPrecoPromocional as promo_price,
            produtoPrecoMinimo as min_price,
            produtoPrecoMaximo as max_price,
            produtoPrecoRevenda as resale_price,
            produtoPrecoFob as fob_price
        ');
        $this->db->from('Produtos');
        $this->db->where('produtoPOID', $product_id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Busca produtos relacionados
     */
    private function get_related_products($product_id, $limit = 5) {
        // Busca categoria e marca do produto
        $this->db->select('categoriaPOID, marcaPOID');
        $this->db->from('Produtos');
        $this->db->where('produtoPOID', $product_id);
        $product = $this->db->get()->row();
        
        // Busca produtos similares
        $this->db->select('
            p.produtoPOID as id,
            p.produtoModelo as code,
            p.produtoNome as name,
            p.produtoPreco as price,
            p.produtoEstoque as stock
        ');
        $this->db->from('Produtos p');
        $this->db->where('p.produtoPOID !=', $product_id);
        $this->db->where('p.produtoEstoque >', 0);
        
        // Prioriza mesma categoria/marca
        $this->db->group_start();
        $this->db->where('p.categoriaPOID', $product->categoriaPOID);
        $this->db->or_where('p.marcaPOID', $product->marcaPOID);
        $this->db->group_end();
        
        $this->db->limit($limit);
        $this->db->order_by('RAND()');
        
        return $this->db->get()->result();
    }
    
    /**
     * Busca carrinho do usuário
     */
    public function get_cart($user_id) {
        // Busca lead ativo do usuário
        $this->db->where('usuarioPOID', $user_id);
        $this->db->where('status', 'pending');
        $this->db->order_by('dataEmissao', 'DESC');
        $this->db->limit(1);
        $lead = $this->db->get('Leads')->row();
        
        if (!$lead) {
            return null;
        }
        
        // Busca itens do carrinho
        $this->db->select('
            li.*,
            p.produtoModelo as product_code,
            p.produtoNome as product_name,
            p.produtoDescricao as product_description,
            p.produtoPreco as product_price,
            p.produtoEstoque as product_stock,
            m.marcaNome as brand_name,
            m.marcaFormatado as brand_formatted
        ');
        $this->db->from('LeadItems li');
        $this->db->join('Produtos p', 'li.produtoPOID = p.produtoPOID');
        $this->db->join('Marcas m', 'p.marcaPOID = m.marcaPOID', 'left');
        $this->db->where('li.leadPOID', $lead->id);
        
        $items = $this->db->get()->result();
        
        // Processa itens
        foreach ($items as &$item) {
            $item->images = $this->get_product_images($item->produtoPOID);
            $item->stock = $this->get_product_stock($item->produtoPOID);
        }
        
        // Calcula totais
        $totals = $this->get_lead_totals($lead->id);
        
        return [
            'id' => $lead->id,
            'items' => $items,
            'totals' => $totals,
            'created_at' => $lead->dataEmissao,
            'updated_at' => $lead->dataAtualizacao
        ];
    }
    
    /**
     * Adiciona item ao carrinho
     */
    public function add_to_cart($user_id, $data) {
        // Busca ou cria lead
        $lead_id = $this->get_or_create_lead($user_id);
        
        // Verifica se produto já existe no carrinho
        $this->db->where('leadPOID', $lead_id);
        $this->db->where('produtoPOID', $data['product_id']);
        $existing = $this->db->get('LeadItems')->row();
        
        if ($existing) {
            // Atualiza quantidade
            $this->db->where('id', $existing->id);
            $this->db->update('LeadItems', [
                'quantidade' => $existing->quantidade + $data['quantity']
            ]);
            return $existing->id;
        }
        
        // Busca dados do produto
        $this->db->where('produtoPOID', $data['product_id']);
        $product = $this->db->get('Produtos')->row();
        
        if (!$product) {
            return false;
        }
        
        // Calcula valores
        $price = $data['price'] ?? $product->produtoPreco;
        $discount = $data['discount'] ?? 0;
        $total = ($price * $data['quantity']) - $discount;
        
        // Insere item
        $item_data = [
            'leadPOID' => $lead_id,
            'produtoPOID' => $data['product_id'],
            'quantidade' => $data['quantity'],
            'valorUnitario' => $price,
            'valorDesconto' => $discount,
            'valorTotal' => $total,
            'dataCriacao' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('LeadItems', $item_data);
        return $this->db->insert_id();
    }
    
    /**
     * Atualiza item do carrinho
     */
    public function update_cart_item($item_id, $data) {
        // Busca item
        $this->db->where('id', $item_id);
        $item = $this->db->get('LeadItems')->row();
        
        if (!$item) {
            return false;
        }
        
        // Atualiza dados
        $update = [];
        
        if (isset($data['quantity'])) {
            $update['quantidade'] = $data['quantity'];
        }
        
        if (isset($data['price'])) {
            $update['valorUnitario'] = $data['price'];
        }
        
        if (isset($data['discount'])) {
            $update['valorDesconto'] = $data['discount'];
        }
        
        // Recalcula total
        if (!empty($update)) {
            $price = $update['valorUnitario'] ?? $item->valorUnitario;
            $quantity = $update['quantidade'] ?? $item->quantidade;
            $discount = $update['valorDesconto'] ?? $item->valorDesconto;
            
            $update['valorTotal'] = ($price * $quantity) - $discount;
        }
        
        // Atualiza item
        if (!empty($update)) {
            $this->db->where('id', $item_id);
            return $this->db->update('LeadItems', $update);
        }
        
        return true;
    }
    
    /**
     * Remove item do carrinho
     */
    public function remove_from_cart($item_id) {
        $this->db->where('id', $item_id);
        return $this->db->delete('LeadItems');
    }
    
    /**
     * Limpa carrinho
     */
    public function clear_cart($user_id) {
        // Busca lead ativo
        $this->db->where('usuarioPOID', $user_id);
        $this->db->where('status', 'pending');
        $lead = $this->db->get('Leads')->row();
        
        if (!$lead) {
            return false;
        }
        
        // Remove itens
        $this->db->where('leadPOID', $lead->id);
        return $this->db->delete('LeadItems');
    }
    
    /**
     * Busca ou cria lead para o carrinho
     */
    private function get_or_create_lead($user_id) {
        // Busca lead ativo
        $this->db->where('usuarioPOID', $user_id);
        $this->db->where('status', 'pending');
        $this->db->order_by('dataEmissao', 'DESC');
        $this->db->limit(1);
        $lead = $this->db->get('Leads')->row();
        
        if ($lead) {
            return $lead->id;
        }
        
        // Cria novo lead
        $lead_data = [
            'usuarioPOID' => $user_id,
            'status' => 'pending',
            'dataEmissao' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('Leads', $lead_data);
        return $this->db->insert_id();
    }
    
    /**
     * Busca clientes com filtros
     */
    public function get_customers($user_id, $filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        // Query base
        $this->db->select('
            c.*,
            g.usuarioNome as manager_name,
            COUNT(DISTINCT l.id) as total_leads,
            COUNT(DISTINCT o.id) as total_orders
        ');
        $this->db->from('Clientes c');
        $this->db->join('Usuarios g', 'c.gerentePOID = g.usuarioPOID', 'left');
        $this->db->join('Leads l', 'c.clientePOID = l.clientePOID', 'left');
        $this->db->join('Pedidos o', 'c.clientePOID = o.clientePOID', 'left');
        
        // Filtros de usuário
        $user = $this->get_user($user_id);
        if ($user->role !== 'admin') {
            $this->db->where('c.gerentePOID', $user_id);
        }
        
        // Filtros de busca
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('c.clienteNome', $filters['search']);
            $this->db->or_like('c.clienteEmail', $filters['search']);
            $this->db->or_like('c.clienteDocumento', $filters['search']);
            $this->db->group_end();
        }
        
        // Filtros de status
        if (!empty($filters['status'])) {
            $this->db->where('c.clienteStatus', $filters['status']);
        }
        
        // Filtros de tipo
        if (!empty($filters['type'])) {
            $this->db->where('c.clienteTipo', $filters['type']);
        }
        
        // Filtros de região
        if (!empty($filters['state'])) {
            $this->db->where('c.clienteEstado', $filters['state']);
        }
        if (!empty($filters['city'])) {
            $this->db->where('c.clienteCidade', $filters['city']);
        }
        
        // Agrupamento
        $this->db->group_by('c.clientePOID');
        
        // Total de registros
        $total = $this->db->count_all_results('', false);
        
        // Paginação e ordenação
        $this->db->limit($limit, $offset);
        
        // Ordenação
        $sort_field = $filters['sort_by'] ?? 'c.clienteNome';
        $sort_order = $filters['sort_order'] ?? 'ASC';
        
        // Mapeamento de campos de ordenação
        $sort_fields = [
            'name' => 'c.clienteNome',
            'email' => 'c.clienteEmail',
            'document' => 'c.clienteDocumento',
            'state' => 'c.clienteEstado',
            'city' => 'c.clienteCidade',
            'leads' => 'total_leads',
            'orders' => 'total_orders'
        ];
        
        $sort_field = $sort_fields[$sort_field] ?? $sort_field;
        $this->db->order_by($sort_field, $sort_order);
        
        // Executa query
        $customers = $this->db->get()->result();
        
        // Processa clientes
        foreach ($customers as &$customer) {
            $customer->addresses = $this->get_customer_addresses($customer->clientePOID);
            $customer->contacts = $this->get_customer_contacts($customer->clientePOID);
            $customer->stats = $this->get_customer_stats($customer->clientePOID);
        }
        
        return [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
            'customers' => $customers
        ];
    }
    
    /**
     * Busca detalhes de um cliente
     */
    public function get_customer($id) {
        // Query base
        $this->db->select('
            c.*,
            g.usuarioNome as manager_name,
            g.usuarioEmail as manager_email,
            g.usuarioTelefone as manager_phone
        ');
        $this->db->from('Clientes c');
        $this->db->join('Usuarios g', 'c.gerentePOID = g.usuarioPOID', 'left');
        $this->db->where('c.clientePOID', $id);
        
        $customer = $this->db->get()->row();
        
        if (!$customer) {
            return false;
        }
        
        // Adiciona informações extras
        $customer->addresses = $this->get_customer_addresses($id);
        $customer->contacts = $this->get_customer_contacts($id);
        $customer->stats = $this->get_customer_stats($id);
        $customer->recent_leads = $this->get_customer_recent_leads($id);
        $customer->recent_orders = $this->get_customer_recent_orders($id);
        $customer->payment_info = $this->get_customer_payment_info($id);
        
        return $customer;
    }
    
    /**
     * Busca endereços de um cliente
     */
    private function get_customer_addresses($customer_id) {
        $this->db->select('
            enderecoPOID as id,
            enderecoTipo as type,
            enderecoLogradouro as street,
            enderecoNumero as number,
            enderecoComplemento as complement,
            enderecoBairro as district,
            enderecoCidade as city,
            enderecoEstado as state,
            enderecoPais as country,
            enderecoCEP as zipcode,
            enderecoPrincipal as is_main
        ');
        $this->db->from('ClienteEnderecos');
        $this->db->where('clientePOID', $customer_id);
        $this->db->order_by('enderecoPrincipal DESC, enderecoTipo ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Busca contatos de um cliente
     */
    private function get_customer_contacts($customer_id) {
        $this->db->select('
            contatoPOID as id,
            contatoNome as name,
            contatoCargo as role,
            contatoEmail as email,
            contatoTelefone as phone,
            contatoCelular as mobile,
            contatoPrincipal as is_main
        ');
        $this->db->from('ClienteContatos');
        $this->db->where('clientePOID', $customer_id);
        $this->db->order_by('contatoPrincipal DESC, contatoNome ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Busca estatísticas de um cliente
     */
    private function get_customer_stats($customer_id) {
        // Leads
        $this->db->select('
            COUNT(*) as total_leads,
            COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_leads,
            COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_leads,
            SUM(valorTotal) as total_lead_value
        ');
        $this->db->from('Leads');
        $this->db->where('clientePOID', $customer_id);
        $leads = $this->db->get()->row();
        
        // Pedidos
        $this->db->select('
            COUNT(*) as total_orders,
            COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_orders,
            COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_orders,
            SUM(valorTotal) as total_order_value
        ');
        $this->db->from('Pedidos');
        $this->db->where('clientePOID', $customer_id);
        $orders = $this->db->get()->row();
        
        return [
            'leads' => $leads,
            'orders' => $orders
        ];
    }
    
    /**
     * Busca leads recentes de um cliente
     */
    private function get_customer_recent_leads($customer_id, $limit = 5) {
        $this->db->select('
            l.*,
            u.usuarioNome as user_name
        ');
        $this->db->from('Leads l');
        $this->db->join('Usuarios u', 'l.usuarioPOID = u.usuarioPOID');
        $this->db->where('l.clientePOID', $customer_id);
        $this->db->order_by('l.dataEmissao', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }
    
    /**
     * Busca pedidos recentes de um cliente
     */
    private function get_customer_recent_orders($customer_id, $limit = 5) {
        $this->db->select('
            o.*,
            u.usuarioNome as user_name
        ');
        $this->db->from('Pedidos o');
        $this->db->join('Usuarios u', 'o.usuarioPOID = u.usuarioPOID');
        $this->db->where('o.clientePOID', $customer_id);
        $this->db->order_by('o.dataEmissao', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }
    
    /**
     * Busca informações de pagamento de um cliente
     */
    private function get_customer_payment_info($customer_id) {
        $this->db->select('
            p.*,
            b.bancoNome as bank_name
        ');
        $this->db->from('ClientePagamento p');
        $this->db->join('Bancos b', 'p.bancoPOID = b.bancoPOID', 'left');
        $this->db->where('p.clientePOID', $customer_id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Busca detalhes de um produto
     */
    public function get_product($id) {
        $this->db->where('id', $id);
        $product = $this->db->get('products')->row();
        
        if (!$product) {
            return false;
        }
        
        // Adiciona informações extras
        $product->images = $this->get_product_images($id);
        $product->attributes = $this->get_product_attributes($id);
        $product->stock = $this->get_product_stock($id);
        
        return $product;
    }
    
    /**
     * Busca imagens de um produto
     */
    private function get_product_images($product_id) {
        $this->db->where('product_id', $product_id);
        return $this->db->get('product_images')->result();
    }
    
    /**
     * Busca atributos de um produto
     */
    private function get_product_attributes($product_id) {
        $this->db->where('product_id', $product_id);
        return $this->db->get('product_attributes')->result();
    }
    
    /**
     * Busca estoque de um produto
     */
    private function get_product_stock($product_id) {
        $this->db->where('product_id', $product_id);
        $stock = $this->db->get('product_stock')->result();
        
        $total = 0;
        foreach ($stock as $item) {
            $total += $item->quantity;
        }
        
        return [
            'total' => $total,
            'locations' => $stock
        ];
    }
}
