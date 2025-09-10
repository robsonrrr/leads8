<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Customer Dashboard Controller
 * Provides customer analytics and dashboard functionality
 */
class Controller_Customer extends Controller_Website {

    public function before() {
        parent::before();


        if (!isset($_SESSION['MM_Userid']) || $_SESSION['MM_Nivel'] < 5) {
            header('Location: /login/');
            exit;
        }
        
        // Initialize session variables for testing
        if (!isset($_SESSION['MM_Userid'])) {
            $_SESSION['MM_Userid'] = 1;
            $_SESSION['MM_Nivel'] = 5;
            $_SESSION['MM_Username'] = 'Test User';
            $_SESSION['MM_Depto'] = 'VENDAS';
            $_SESSION['MM_Segment'] = 'machines';
        }
        
        // TODO: Re-enable proper authentication after testing
        /*
        if (!isset($_SESSION['MM_Userid']) || $_SESSION['MM_Nivel'] < 2) {
            header('Location: /leads8/');
            exit;
        }
        */
    }

    /**
     * Main dashboard action - displays customer analytics
     */
    public function action_index() {
        $customers_data = $this->get_customers_data();
        
        // Create a view and pass the data to it
        $view = View::factory('customer/dashboard');
        $view->set('customers_data', $customers_data);
        
        return $this->response->body($view->render());
    }

    /**
     * Get customers data using the provided SQL query with caching
     * @return array
     */
    private function get_customers_data() {
        // Try to get data from cache first
        $cache_key = Service_CustomerCache::generate_customer_key('', array(), 0, 10, 'cliente_nome', 'asc');
        $cached_data = Service_CustomerCache::get_customer_data($cache_key);
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        try {
            // Usar conexão direta MySQLi para evitar problemas de configuração do Kohana
            $mysqli = new mysqli('vallery.catmgckfixum.sa-east-1.rds.amazonaws.com', 'robsonrr', 'Best94364811082', 'mak');
            
            if ($mysqli->connect_error) {
                error_log('Connection failed: ' . $mysqli->connect_error);
                return array();
            }
            
            $sql = "SELECT 
                LCASE(c.nome) as cliente_nome,
                LCASE(c.ender) as cliente_endereco,
                LCASE(c.bairro) as bairro,
                LCASE(c.cidade) as cliente_cidade,
                c.estado,
                c.cnpj,
                LCASE(u.nick) as nick,
                c.rfm,
                (SELECT LCASE(class) FROM clientes_class cc WHERE cc.id = c.vip LIMIT 1) AS vip,
                (SELECT COALESCE(SUM(h.valor), 0) 
                 FROM hoje h 
                 WHERE h.idcli = c.id 
                 AND h.prazo <> 15 and nop IN (27,28,51,76)
                 AND YEAR(h.data) = YEAR(CURRENT_DATE) 
                 AND MONTH(h.data) = MONTH(CURRENT_DATE)) AS mes,
                (SELECT COALESCE(SUM(h.valor), 0) 
                 FROM hoje h 
                 WHERE h.idcli = c.id 
                 AND h.prazo <> 15 and nop IN (27,28,51,76)
                 AND YEAR(h.data) = YEAR(CURRENT_DATE)) AS ano_corrente,
                (SELECT COALESCE(SUM(h.valor), 0) 
                 FROM hoje h 
                 WHERE h.idcli = c.id 
                 AND h.prazo <> 15 and nop IN (27,28,51,76)     
                 AND YEAR(h.data) = YEAR(CURRENT_DATE) - 1) AS ano_anterior,
                (SELECT COALESCE(SUM(cq.valor), 0) 
                 FROM cheques cq 
                 WHERE cq.idcli = c.id 
                 AND cq.data < CURRENT_DATE - INTERVAL 5 DAY 
                 AND (cq.datadep IS NULL OR MONTH(cq.datadep) = 0)) AS atrasados,
                (SELECT COALESCE(SUM(cq.valor), 0) 
                 FROM cheques cq 
                 WHERE cq.idcli = c.id 
                 AND (YEAR(cq.datadep) = 0 OR cq.datadep > CURRENT_DATE)) AS pendentes,
                (SELECT COALESCE(SUM(h.valor), 0) 
                 FROM hoje h 
                 WHERE h.idcli = c.id 
                 AND h.prazo <> 15 and nop IN (27,28,51,76)) AS total,
                (SELECT COALESCE(MAX(h.valor), 0) 
                 FROM hoje h 
                 WHERE h.idcli = c.id 
                 AND h.prazo <> 15 and nop IN (27,28,51,76)) AS maior,  
                (SELECT COALESCE(SUM(cq.valor), 0) 
                 FROM cheques cq 
                 WHERE cq.idcli = c.id 
                 AND MONTH(cq.datadep) > 0) AS pago,
                (SELECT u.nick 
                 FROM users u 
                 WHERE u.id = c.vendedor LIMIT 1) AS vendedor,
                (SELECT u.nick 
                 FROM users u 
                 INNER JOIN GerenteClientes g ON u.id = g.GerentePOID 
                 WHERE g.SegmentoPOID = 1 
                 AND g.ClientePOID = c.id 
                 LIMIT 1) AS gerente,
                COALESCE(DATEDIFF(CURRENT_DATE, 
                    (SELECT MAX(h.data) 
                     FROM hoje h 
                     WHERE h.nop in (27,27,51,76) and h.idcli = c.id)), 0) AS dias_ultima_compra,
                (SELECT p.segmento 
                 FROM hoje h 
                 LEFT JOIN hist hi on hi.pedido=h.id 
                 LEFT JOIN i on i.id=hi.isbn 
                 LEFT JOIN produtos p on p.id=i.idcf 
                 WHERE h.nop in (27,27,51,76) and h.idcli = c.id 
                 ORDER BY h.valor DESC
                 LIMIT 1) AS segmento,
                (SELECT DATEDIFF(CURRENT_DATE, cl.data) 
                 FROM Ecommerce.clientes_login cl 
                 WHERE cl.idcli = c.id 
                 ORDER BY cl.id DESC 
                 LIMIT 1) AS ultimo_acesso,
                (SELECT DATEDIFF(CURRENT_DATE, cv.date) 
                 FROM webteam.clientes_visitas cv 
                 WHERE cv.idcli = c.id 
                 ORDER BY cv.id DESC 
                 LIMIT 1) AS ultima_visita,
                (SELECT DATEDIFF(CURRENT_DATE, ch.data) 
                 FROM crm.chamadas ch 
                 WHERE ch.cliente_id = c.id 
                 ORDER BY ch.id DESC 
                 LIMIT 1) AS dias_ultimo_tkt,
                c.fantasia,
                c.fone as telefone,
                c.email_master as email
            FROM 
                clientes c
            LEFT JOIN 
                users u ON u.id = c.vendedor
            WHERE c.nome <> '' AND c.cidade IS NOT NULL AND c.cidade <> '' AND u.id != 9 AND u.id != 1011
            
            LIMIT 10";
            
            $result = $mysqli->query($sql);
            
            if (!$result) {
                error_log('Query failed: ' . $mysqli->error);
                $mysqli->close();
                return array();
            }
            
            $customers = array();
            while ($row = $result->fetch_assoc()) {
                // Apply ucwords to text fields while keeping estado lowercase
                $row['cliente_nome'] = ucwords($row['cliente_nome'] ?? '');
                $row['cliente_endereco'] = ucwords($row['cliente_endereco'] ?? '');
                $row['bairro'] = ucwords($row['bairro'] ?? '');
                $row['cliente_cidade'] = ucwords($row['cliente_cidade'] ?? '');
                $row['nick'] = ucwords($row['nick'] ?? '');
                $row['rfm'] = ucwords($row['rfm'] ?? '');
                
                $customers[] = $row;
            }
            
            $mysqli->close();
            
            // Cache the results
            Service_CustomerCache::set_customer_data($cache_key, $customers);
            
            return $customers;
            
        } catch (Exception $e) {
            error_log('Customer query error: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * API endpoint to get customer data as JSON
     */
    public function action_api() {
        $customers = $this->get_customers_data();
        
        $this->response->headers('Content-Type', 'application/json');
        return $this->response->body(json_encode($customers));
    }

    /**
     * AJAX endpoint for DataTables server-side processing
     */
    public function action_ajax() {
        // Ensure no output before headers
        if (ob_get_length()) ob_clean();
        
        $this->response->headers('Content-Type', 'application/json');
        header('Content-Type: application/json');
        
        try {
            // Debug logging
            error_log('=== AJAX Request received ===');
            error_log('POST data: ' . print_r($_POST, true));
            error_log('GET data: ' . print_r($_GET, true));
            error_log('Request method: ' . $_SERVER['REQUEST_METHOD']);
            error_log('Request URI: ' . $_SERVER['REQUEST_URI']);
            error_log('Content type: ' . (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'not set'));
            

            
            // Check for special actions
            $action = $this->request->post('action');
            
            if ($action === 'get_estados') {
                $estados = $this->get_filter_options('estado');
                return $this->response->body(json_encode(['data' => $estados]));
            }
            
            if ($action === 'get_vendedores') {
                $vendedores = $this->get_filter_options('nick');
                return $this->response->body(json_encode(['data' => $vendedores]));
            }
            
            if ($action === 'get_rfm') {
                $rfm_values = $this->get_filter_options('rfm');
                return $this->response->body(json_encode(['data' => $rfm_values]));
            }
            
            // Get DataTables parameters
            $draw = intval($this->request->post('draw'));
            $start = intval($this->request->post('start'));
            $length = intval($this->request->post('length'));
            $search_value = $this->request->post('search')['value'] ?? '';
            $order_column = intval($this->request->post('order')[0]['column'] ?? 0);
            $order_dir = $this->request->post('order')[0]['dir'] ?? 'asc';
            
            // Get custom filters
            $filter_estado = $this->request->post('filter_estado');
            $filter_vendedor = $this->request->post('filter_vendedor');
            $filter_rfm = $this->request->post('filter_rfm');
            $filter_ultima_compra = $this->request->post('filter_ultima_compra');
            $filter_rua_sao_caetano = $this->request->post('filter_rua_sao_caetano');
            $filter_bom_retiro = $this->request->post('filter_bom_retiro');
            $filter_bras = $this->request->post('filter_bras');
            $filter_vendas_mes_zero = $this->request->post('filter_vendas_mes_zero');
            $filter_vendas_ano_zero = $this->request->post('filter_vendas_ano_zero');
            $filter_vendas_ano_passado_zero = $this->request->post('filter_vendas_ano_passado_zero');
            // City filters
            $filter_sao_paulo = $this->request->post('filter_sao_paulo');
            $filter_sao_paulo_center = $this->request->post('filter_sao_paulo_center');
            $filter_sao_paulo_side = $this->request->post('filter_sao_paulo_side');
            $filter_blumenau = $this->request->post('filter_blumenau');
            $filter_curitiba = $this->request->post('filter_curitiba');
            $filter_maringa = $this->request->post('filter_maringa');
            $filter_bauru = $this->request->post('filter_bauru');
            $filter_birigui = $this->request->post('filter_birigui');
            $filter_belo_horizonte = $this->request->post('filter_belo_horizonte');
            $filter_franca = $this->request->post('filter_franca');
            $filter_fortaleza = $this->request->post('filter_fortaleza');
            $filter_juiz_de_fora = $this->request->post('filter_juiz_de_fora');
            // Vendedor filters
            $filter_vendedor_regiane = $this->request->post('filter_vendedor_regiane');
            $filter_vendedor_rosana = $this->request->post('filter_vendedor_rosana');
            $filter_vendedor_edilene = $this->request->post('filter_vendedor_edilene');
            $filter_vendedor_debora = $this->request->post('filter_vendedor_debora');
            $filter_vendedor_miriam = $this->request->post('filter_vendedor_miriam');
            $filter_vendedor_andrea = $this->request->post('filter_vendedor_andrea');
            $filter_vendedor_cristiane = $this->request->post('filter_vendedor_cristiane');
            $filter_vendedor_luana = $this->request->post('filter_vendedor_luana');
            $filter_vendedor_revisar = $this->request->post('filter_vendedor_revisar');
            // State filters - Norte
            $filter_acre = $this->request->post('filter_acre');
            $filter_amapa = $this->request->post('filter_amapa');
            $filter_amazonas = $this->request->post('filter_amazonas');
            $filter_para = $this->request->post('filter_para');
            $filter_rondonia = $this->request->post('filter_rondonia');
            $filter_roraima = $this->request->post('filter_roraima');
            $filter_tocantins = $this->request->post('filter_tocantins');
            // State filters - Nordeste
            $filter_alagoas = $this->request->post('filter_alagoas');
            $filter_bahia = $this->request->post('filter_bahia');
            $filter_ceara = $this->request->post('filter_ceara');
            $filter_maranhao = $this->request->post('filter_maranhao');
            $filter_paraiba = $this->request->post('filter_paraiba');
            $filter_pernambuco = $this->request->post('filter_pernambuco');
            $filter_piaui = $this->request->post('filter_piaui');
            $filter_rio_grande_do_norte = $this->request->post('filter_rio_grande_do_norte');
            $filter_sergipe = $this->request->post('filter_sergipe');
            // State filters - Centro-Oeste
            $filter_distrito_federal = $this->request->post('filter_distrito_federal');
            $filter_goias = $this->request->post('filter_goias');
            $filter_mato_grosso = $this->request->post('filter_mato_grosso');
            $filter_mato_grosso_do_sul = $this->request->post('filter_mato_grosso_do_sul');
            // State filters - Sudeste
            $filter_espirito_santo = $this->request->post('filter_espirito_santo');
            $filter_minas_gerais = $this->request->post('filter_minas_gerais');
            $filter_rio_de_janeiro = $this->request->post('filter_rio_de_janeiro');
            $filter_sao_paulo_estado = $this->request->post('filter_sao_paulo_estado');
            // State filters - Sul
            $filter_parana = $this->request->post('filter_parana');
            $filter_rio_grande_do_sul = $this->request->post('filter_rio_grande_do_sul');
            $filter_santa_catarina = $this->request->post('filter_santa_catarina');
            
            // Segmento filters
            $filter_machines = $this->request->post('machines');
            $filter_bearings = $this->request->post('bearings');
            $filter_auto = $this->request->post('auto');
            $filter_parts = $this->request->post('parts');
            $filter_faucets = $this->request->post('faucets');
            
            $filters = [
                'estado' => $filter_estado,
                'vendedor' => $filter_vendedor,
                'rfm' => $filter_rfm,
                'ultima_compra' => $filter_ultima_compra,
                'rua_sao_caetano' => $filter_rua_sao_caetano,
                'bom_retiro' => $filter_bom_retiro,
                'bras' => $filter_bras,
                'vendas_mes_zero' => $filter_vendas_mes_zero,
                'vendas_ano_zero' => $filter_vendas_ano_zero,
                'vendas_ano_passado_zero' => $filter_vendas_ano_passado_zero,
                'sao_paulo' => $filter_sao_paulo,
                'sao_paulo_center' => $filter_sao_paulo_center,
                'sao_paulo_side' => $filter_sao_paulo_side,
                'blumenau' => $filter_blumenau,
                'curitiba' => $filter_curitiba,
                'maringa' => $filter_maringa,
                'bauru' => $filter_bauru,
                'birigui' => $filter_birigui,
                'belo_horizonte' => $filter_belo_horizonte,
                'franca' => $filter_franca,
                'fortaleza' => $filter_fortaleza,
                'juiz_de_fora' => $filter_juiz_de_fora,
                // Vendedor filters
                'vendedor_regiane' => $filter_vendedor_regiane,
                'vendedor_rosana' => $filter_vendedor_rosana,
                'vendedor_edilene' => $filter_vendedor_edilene,
                'vendedor_debora' => $filter_vendedor_debora,
                'vendedor_miriam' => $filter_vendedor_miriam,
                'vendedor_andrea' => $filter_vendedor_andrea,
                'vendedor_cristiane' => $filter_vendedor_cristiane,
                'vendedor_luana' => $filter_vendedor_luana,
                'vendedor_revisar' => $filter_vendedor_revisar,
                // State filters - Norte
                'acre' => $filter_acre,
                'amapa' => $filter_amapa,
                'amazonas' => $filter_amazonas,
                'para' => $filter_para,
                'rondonia' => $filter_rondonia,
                'roraima' => $filter_roraima,
                'tocantins' => $filter_tocantins,
                // State filters - Nordeste
                'alagoas' => $filter_alagoas,
                'bahia' => $filter_bahia,
                'ceara' => $filter_ceara,
                'maranhao' => $filter_maranhao,
                'paraiba' => $filter_paraiba,
                'pernambuco' => $filter_pernambuco,
                'piaui' => $filter_piaui,
                'rio_grande_do_norte' => $filter_rio_grande_do_norte,
                'sergipe' => $filter_sergipe,
                // State filters - Centro-Oeste
                'distrito_federal' => $filter_distrito_federal,
                'goias' => $filter_goias,
                'mato_grosso' => $filter_mato_grosso,
                'mato_grosso_do_sul' => $filter_mato_grosso_do_sul,
                // State filters - Sudeste
                'espirito_santo' => $filter_espirito_santo,
                'minas_gerais' => $filter_minas_gerais,
                'rio_de_janeiro' => $filter_rio_de_janeiro,
                'sao_paulo_estado' => $filter_sao_paulo_estado,
                // State filters - Sul
                'parana' => $filter_parana,
                'rio_grande_do_sul' => $filter_rio_grande_do_sul,
                'santa_catarina' => $filter_santa_catarina,
                // Segmento filters
                'machines' => $filter_machines,
                'bearings' => $filter_bearings,
                'auto' => $filter_auto,
                'parts' => $filter_parts,
                'faucets' => $filter_faucets
            ];
            
            // Column mapping for ordering
            $columns = [
                0 => 'c.nome',
                1 => 'c.cidade', 
                2 => 'c.estado',
                3 => 'c.bairro',
                4 => 'c.ender',
                5 => 'u.nick',
                6 => 'segmento',
                7 => 'c.rfm',
                8 => 'mes',
                9 => 'ano_corrente',
                10 => 'ano_anterior',
                11 => 'total',
                12 => 'dias_ultima_compra'
            ];
            
            $order_column_name = $columns[$order_column] ?? 'c.nome';
            
            // Generate cache keys
            $data_cache_key = Service_CustomerCache::generate_customer_key($search_value, $filters, $start, $length, $order_column_name, $order_dir);
            $count_cache_key = Service_CustomerCache::generate_count_key($search_value, $filters);
            
            // Try to get data from cache
            $customers_data = Service_CustomerCache::get_customer_data($data_cache_key);
            $filtered_records = Service_CustomerCache::get_customer_count($count_cache_key);
            $total_records = Service_CustomerCache::get_total_count();
            
            // If any cache miss, fetch from database
            if ($customers_data === false) {
                $customers_data = $this->get_customers_data_ajax($start, $length, $search_value, $order_column_name, $order_dir, $filters);
                Service_CustomerCache::set_customer_data($data_cache_key, $customers_data);
            }
            
            if ($filtered_records === false) {
                $filtered_records = $this->get_filtered_customers_count($search_value, $filters);
                Service_CustomerCache::set_customer_count($count_cache_key, $filtered_records);
            }
            
            if ($total_records === false) {
                $total_records = $this->get_total_customers_count();
                Service_CustomerCache::set_total_count($total_records);
            }
            
            $response = [
                'draw' => $draw,
                'recordsTotal' => $total_records,
                'recordsFiltered' => $filtered_records,
                'data' => $customers_data
            ];
            
            // Debug the response before sending
            error_log('Response data: ' . json_encode($response));
            error_log('Total records: ' . $total_records);
            error_log('Filtered records: ' . $filtered_records);
            
            // Ensure valid JSON
            $json_response = json_encode($response, JSON_PARTIAL_OUTPUT_ON_ERROR);
            if ($json_response === false) {
                error_log('JSON encode error: ' . json_last_error_msg());
                $json_response = json_encode([
                    'draw' => $draw ?? 0,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Erro de codificação JSON'
                ]);
            }
            
            error_log('Final JSON response: ' . $json_response);
            return $this->response->body($json_response);
            
        } catch (Exception $e) {
            error_log('AJAX error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            $error_response = json_encode([
                'draw' => $draw ?? 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Erro ao carregar dados: ' . $e->getMessage()
            ]);
            
            error_log('Error JSON response: ' . $error_response);
            return $this->response->body($error_response);
        }
    }

    /**
     * Get customer details by ID
     */
    public function action_detail() {
        $customerID = $this->request->param('id');
        
        if (!$customerID) {
            die('<h3 align="center" style="margin-top:100px">Customer ID é obrigatório</h3>');
        }
        
        // Get specific customer data
        $customer = $this->get_customer_by_id($customerID);
        
        if (!$customer) {
            die('<h3 align="center" style="margin-top:100px">Cliente não encontrado</h3>');
        }
        
        $data = array(
            'customer' => $customer,
            'page_title' => 'Detalhes do Cliente: ' . $customer['nome']
        );
        
        return $this->render('customer/detail', $data);
    }

    /**
     * Get customer by ID
     * @param int $id
     * @return array|null
     */
    private function get_customer_by_id($id) {
        $customers = $this->get_customers_data();
        
        foreach ($customers as $customer) {
            if (isset($customer['id']) && $customer['id'] == $id) {
                return $customer;
            }
        }
        
        return null;
    }

    /**
     * Get filter options for dropdowns
     */
    private function get_filter_options($field) {
        try {
            $mysqli = new mysqli('vallery.catmgckfixum.sa-east-1.rds.amazonaws.com', 'robsonrr', 'Best94364811082', 'mak');
            
            if ($mysqli->connect_error) {
                return array();
            }
            
            $sql = "";
            switch ($field) {
                case 'estado':
                    $sql = "SELECT DISTINCT c.estado FROM clientes c WHERE c.estado IS NOT NULL AND c.estado <> '' AND c.cidade IS NOT NULL AND c.cidade <> '' ORDER BY c.estado";
                    break;
                case 'nick':
                    $sql = "SELECT DISTINCT u.nick FROM users u INNER JOIN clientes c ON u.id = c.vendedor WHERE u.nick IS NOT NULL AND u.nick <> '' AND c.cidade IS NOT NULL AND c.cidade <> '' ORDER BY u.nick";
                    break;
                case 'rfm':
                    $sql = "SELECT DISTINCT c.rfm FROM clientes c WHERE c.rfm IS NOT NULL AND c.rfm <> '' AND c.cidade IS NOT NULL AND c.cidade <> '' ORDER BY c.rfm";
                    break;
                default:
                    return array();
            }
            
            $result = $mysqli->query($sql);
            $options = array();
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $value = array_values($row)[0];
                    if (!empty($value)) {
                        $options[] = $value;
                    }
                }
            }
            
            $mysqli->close();
            return $options;
            
        } catch (Exception $e) {
            error_log('Filter options error: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Get customers data with AJAX parameters (pagination, search, ordering)
     */
    private function get_customers_data_ajax($start, $length, $search, $order_column, $order_dir, $filters = array()) {
        try {
            $mysqli = new mysqli('vallery.catmgckfixum.sa-east-1.rds.amazonaws.com', 'robsonrr', 'Best94364811082', 'mak');
            
            if ($mysqli->connect_error) {
                error_log('Connection failed: ' . $mysqli->connect_error);
                return array();
            }
            
            $where_clause = "WHERE c.nome <> '' AND c.cidade IS NOT NULL AND c.cidade <> '' AND u.id != 9 AND u.id != 1011";
            
            // Add search filter
            if (!empty($search)) {
                $search = $mysqli->real_escape_string($search);
                $where_clause .= " AND (c.nome LIKE '%$search%' OR c.cidade LIKE '%$search%' OR c.estado LIKE '%$search%' OR c.bairro LIKE '%$search%' OR c.ender LIKE '%$search%' OR u.nick LIKE '%$search%')";
            }
            
            // Add custom filters
            if (!empty($filters['estado'])) {
                $estado = $mysqli->real_escape_string($filters['estado']);
                $where_clause .= " AND c.estado = '$estado'";
            }
            
            if (!empty($filters['vendedor'])) {
                $vendedor = $mysqli->real_escape_string($filters['vendedor']);
                $where_clause .= " AND u.nick = '$vendedor'";
            }
            
            if (!empty($filters['rfm'])) {
                $rfm = $mysqli->real_escape_string($filters['rfm']);
                $where_clause .= " AND c.rfm = '$rfm'";
            }
            
            if (!empty($filters['ultima_compra'])) {
                if ($filters['ultima_compra'] === '30-60') {
                    $where_clause .= " AND (SELECT COALESCE(DATEDIFF(CURRENT_DATE, MAX(h.data)), 999) 
                                          FROM hoje h 
                                          WHERE h.nop IN (27,28,51,76) AND h.idcli = c.id) BETWEEN 30 AND 60";
                } elseif ($filters['ultima_compra'] === '60-90') {
                    $where_clause .= " AND (SELECT COALESCE(DATEDIFF(CURRENT_DATE, MAX(h.data)), 999) 
                                          FROM hoje h 
                                          WHERE h.nop IN (27,28,51,76) AND h.idcli = c.id) BETWEEN 60 AND 90";
                } elseif ($filters['ultima_compra'] === '90-180') {
                    $where_clause .= " AND (SELECT COALESCE(DATEDIFF(CURRENT_DATE, MAX(h.data)), 999) 
                                          FROM hoje h 
                                          WHERE h.nop IN (27,28,51,76) AND h.idcli = c.id) BETWEEN 90 AND 180";
                } elseif ($filters['ultima_compra'] === '180-365') {
                    $where_clause .= " AND (SELECT COALESCE(DATEDIFF(CURRENT_DATE, MAX(h.data)), 999) 
                                          FROM hoje h 
                                          WHERE h.nop IN (27,28,51,76) AND h.idcli = c.id) BETWEEN 180 AND 365";
                } elseif ($filters['ultima_compra'] === '>365') {
                    $where_clause .= " AND (SELECT COALESCE(DATEDIFF(CURRENT_DATE, MAX(h.data)), 999) 
                                          FROM hoje h 
                                          WHERE h.nop IN (27,28,51,76) AND h.idcli = c.id) > 365";
                }
            }
            
            if (!empty($filters['rua_sao_caetano']) && $filters['rua_sao_caetano'] === 'sao_caetano') {
                $where_clause .= " AND c.ender LIKE '%sao caetano%' AND c.cidade = 'sao paulo'";
            }
            
            if (!empty($filters['bom_retiro']) && $filters['bom_retiro'] === 'bom_retiro') {
                $where_clause .= " AND c.bairro LIKE '%bom retiro%' AND c.cidade = 'sao paulo'";
            }
            
            if (!empty($filters['bras']) && $filters['bras'] === 'bras') {
                $where_clause .= " AND c.bairro LIKE '%bras%' AND c.cidade = 'sao paulo'";
            }
            
            if (!empty($filters['bras']) && $filters['bras'] === 'bras') {
                $where_clause .= " AND c.bairro = 'bras' AND c.cidade = 'sao paulo'";
            }
            
            if (!empty($filters['bras']) && $filters['bras'] === 'bras') {
                $where_clause .= " AND c.bairro = 'bras' AND c.cidade = 'sao paulo'";
            }
            
            // Segmento filter
            if (!empty($filters['segmento'])) {
                $segmento = $mysqli->real_escape_string($filters['segmento']);
                if ($segmento === 'only_parts') {
                    $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'parts'";
                } elseif ($segmento !== 'all') {
                    $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = '$segmento'";
                }
            }
            
            if (!empty($filters['vendas_mes_zero'])) {
                if ($filters['vendas_mes_zero'] === 'mes_zero') {
                    $where_clause .= " AND (SELECT COALESCE(SUM(h.valor), 0) 
                                          FROM hoje h 
                                          WHERE h.idcli = c.id 
                                          AND h.prazo <> 15 and nop IN (27,28,51,76)
                                          AND YEAR(h.data) = YEAR(CURRENT_DATE) 
                                          AND MONTH(h.data) = MONTH(CURRENT_DATE)) = 0";
                } elseif ($filters['vendas_mes_zero'] === 'mes_positive') {
                    $where_clause .= " AND (SELECT COALESCE(SUM(h.valor), 0) 
                                          FROM hoje h 
                                          WHERE h.idcli = c.id 
                                          AND h.prazo <> 15 and nop IN (27,28,51,76)
                                          AND YEAR(h.data) = YEAR(CURRENT_DATE) 
                                          AND MONTH(h.data) = MONTH(CURRENT_DATE)) > 0";
                }
            }
            
            if (!empty($filters['vendas_ano_zero'])) {
                if ($filters['vendas_ano_zero'] === 'ano_zero') {
                    $where_clause .= " AND (SELECT COALESCE(SUM(h.valor), 0) 
                                          FROM hoje h 
                                          WHERE h.idcli = c.id 
                                          AND h.prazo <> 15 and nop IN (27,28,51,76)
                                          AND YEAR(h.data) = YEAR(CURRENT_DATE)) = 0";
                } elseif ($filters['vendas_ano_zero'] === 'ano_positive') {
                    $where_clause .= " AND (SELECT COALESCE(SUM(h.valor), 0) 
                                          FROM hoje h 
                                          WHERE h.idcli = c.id 
                                          AND h.prazo <> 15 and nop IN (27,28,51,76)
                                          AND YEAR(h.data) = YEAR(CURRENT_DATE)) > 0";
                }
            }
            
            if (!empty($filters['vendas_ano_passado_zero'])) {
                if ($filters['vendas_ano_passado_zero'] === 'ano_passado_zero') {
                    $where_clause .= " AND (SELECT COALESCE(SUM(h.valor), 0) 
                                          FROM hoje h 
                                          WHERE h.idcli = c.id 
                                          AND h.prazo <> 15 and nop IN (27,28,51,76)
                                          AND YEAR(h.data) = YEAR(CURRENT_DATE) - 1) = 0";
                } elseif ($filters['vendas_ano_passado_zero'] === 'ano_passado_positive') {
                    $where_clause .= " AND (SELECT COALESCE(SUM(h.valor), 0) 
                                          FROM hoje h 
                                          WHERE h.idcli = c.id 
                                          AND h.prazo <> 15 and nop IN (27,28,51,76)
                                          AND YEAR(h.data) = YEAR(CURRENT_DATE) - 1) > 0";
                }
            }
            
            if (!empty($filters['vendas_ano_passado_zero'])) {
                if ($filters['vendas_ano_passado_zero'] === 'ano_passado_zero') {
                    $where_clause .= " AND (SELECT COALESCE(SUM(h.valor), 0) 
                                          FROM hoje h 
                                          WHERE h.idcli = c.id 
                                          AND h.prazo <> 15 and nop IN (27,28,51,76)
                                          AND YEAR(h.data) = YEAR(CURRENT_DATE) - 1) = 0";
                } elseif ($filters['vendas_ano_passado_zero'] === 'ano_passado_positive') {
                    $where_clause .= " AND (SELECT COALESCE(SUM(h.valor), 0) 
                                          FROM hoje h 
                                          WHERE h.idcli = c.id 
                                          AND h.prazo <> 15 and nop IN (27,28,51,76)
                                          AND YEAR(h.data) = YEAR(CURRENT_DATE) - 1) > 0";
                }
            }
            
            // City filters
            if (!empty($filters['sao_paulo']) && $filters['sao_paulo'] === 'sao_paulo') {
                $where_clause .= " AND c.cidade = 'sao paulo'";
            }
            
            if (!empty($filters['sao_paulo_center']) && $filters['sao_paulo_center'] === 'sao_paulo_center') {
                $where_clause .= " AND c.cidade = 'sao paulo' AND (c.bairro LIKE '%luz%' OR c.bairro LIKE '%bras%' OR c.bairro LIKE '%bom retiro%')";
            }
            
            if (!empty($filters['sao_paulo_side']) && $filters['sao_paulo_side'] === 'sao_paulo_side') {
                $where_clause .= " AND c.cidade = 'sao paulo' AND c.bairro NOT LIKE '%luz%' AND c.bairro NOT LIKE '%bras%' AND c.bairro NOT LIKE '%bom retiro%'";
            }
            
            if (!empty($filters['blumenau']) && $filters['blumenau'] === 'blumenau') {
                $where_clause .= " AND c.cidade = 'blumenau'";
            }
            
            if (!empty($filters['curitiba']) && $filters['curitiba'] === 'curitiba') {
                $where_clause .= " AND c.cidade = 'curitiba'";
            }
            
            if (!empty($filters['maringa']) && $filters['maringa'] === 'maringa') {
                $where_clause .= " AND c.cidade = 'maringa'";
            }
            
            if (!empty($filters['bauru']) && $filters['bauru'] === 'bauru') {
                $where_clause .= " AND c.cidade = 'Bauru'";
            }
            
            if (!empty($filters['birigui']) && $filters['birigui'] === 'birigui') {
                $where_clause .= " AND c.cidade = 'Birigui'";
            }
            
            if (!empty($filters['belo_horizonte']) && $filters['belo_horizonte'] === 'belo_horizonte') {
                $where_clause .= " AND c.cidade = 'belo horizonte'";
            }
            
            if (!empty($filters['franca']) && $filters['franca'] === 'franca') {
                $where_clause .= " AND c.cidade = 'franca'";
            }
            
            if (!empty($filters['fortaleza']) && $filters['fortaleza'] === 'fortaleza') {
                $where_clause .= " AND c.cidade = 'Fortaleza'";
            }
            
            if (!empty($filters['juiz_de_fora']) && $filters['juiz_de_fora'] === 'juiz_de_fora') {
                $where_clause .= " AND c.cidade = 'juiz de fora'";
            }
            
            if (!empty($filters['bauru']) && $filters['bauru'] === 'bauru') {
                $where_clause .= " AND c.cidade = 'Bauru'";
            }
            
            if (!empty($filters['birigui']) && $filters['birigui'] === 'birigui') {
                $where_clause .= " AND c.cidade = 'Birigui'";
            }
            
            if (!empty($filters['belo_horizonte']) && $filters['belo_horizonte'] === 'belo_horizonte') {
                $where_clause .= " AND c.cidade = 'belo horizonte'";
            }
            
            if (!empty($filters['franca']) && $filters['franca'] === 'franca') {
                $where_clause .= " AND c.cidade = 'franca'";
            }
            
            if (!empty($filters['fortaleza']) && $filters['fortaleza'] === 'fortaleza') {
                $where_clause .= " AND c.cidade = 'Fortaleza'";
            }
            
            if (!empty($filters['juiz_de_fora']) && $filters['juiz_de_fora'] === 'juiz_de_fora') {
                $where_clause .= " AND c.cidade = 'juiz de fora'";
            }
            
            // State filters - Norte
            if (!empty($filters['acre']) && $filters['acre'] === 'acre') {
                $where_clause .= " AND c.estado = 'AC'";
            }
            
            if (!empty($filters['amapa']) && $filters['amapa'] === 'amapa') {
                $where_clause .= " AND c.estado = 'AP'";
            }
            
            if (!empty($filters['amazonas']) && $filters['amazonas'] === 'amazonas') {
                $where_clause .= " AND c.estado = 'AM'";
            }
            
            if (!empty($filters['para']) && $filters['para'] === 'para') {
                $where_clause .= " AND c.estado = 'PA'";
            }
            
            if (!empty($filters['rondonia']) && $filters['rondonia'] === 'rondonia') {
                $where_clause .= " AND c.estado = 'RO'";
            }
            
            if (!empty($filters['roraima']) && $filters['roraima'] === 'roraima') {
                $where_clause .= " AND c.estado = 'RR'";
            }
            
            if (!empty($filters['tocantins']) && $filters['tocantins'] === 'tocantins') {
                $where_clause .= " AND c.estado = 'TO'";
            }
            
            // State filters - Nordeste
            if (!empty($filters['alagoas']) && $filters['alagoas'] === 'alagoas') {
                $where_clause .= " AND c.estado = 'AL'";
            }
            
            if (!empty($filters['bahia']) && $filters['bahia'] === 'bahia') {
                $where_clause .= " AND c.estado = 'BA'";
            }
            
            if (!empty($filters['ceara']) && $filters['ceara'] === 'ceara') {
                $where_clause .= " AND c.estado = 'CE'";
            }
            
            if (!empty($filters['maranhao']) && $filters['maranhao'] === 'maranhao') {
                $where_clause .= " AND c.estado = 'MA'";
            }
            
            if (!empty($filters['paraiba']) && $filters['paraiba'] === 'paraiba') {
                $where_clause .= " AND c.estado = 'PB'";
            }
            
            if (!empty($filters['pernambuco']) && $filters['pernambuco'] === 'pernambuco') {
                $where_clause .= " AND c.estado = 'PE'";
            }
            
            if (!empty($filters['piaui']) && $filters['piaui'] === 'piaui') {
                $where_clause .= " AND c.estado = 'PI'";
            }
            
            if (!empty($filters['rio_grande_do_norte']) && $filters['rio_grande_do_norte'] === 'rio_grande_do_norte') {
                $where_clause .= " AND c.estado = 'RN'";
            }
            
            if (!empty($filters['sergipe']) && $filters['sergipe'] === 'sergipe') {
                $where_clause .= " AND c.estado = 'SE'";
            }
            
            // State filters - Centro-Oeste
            if (!empty($filters['distrito_federal']) && $filters['distrito_federal'] === 'distrito_federal') {
                $where_clause .= " AND c.estado = 'DF'";
            }
            
            if (!empty($filters['goias']) && $filters['goias'] === 'goias') {
                $where_clause .= " AND c.estado = 'GO'";
            }
            
            if (!empty($filters['mato_grosso']) && $filters['mato_grosso'] === 'mato_grosso') {
                $where_clause .= " AND c.estado = 'MT'";
            }
            
            if (!empty($filters['mato_grosso_do_sul']) && $filters['mato_grosso_do_sul'] === 'mato_grosso_do_sul') {
                $where_clause .= " AND c.estado = 'MS'";
            }
            
            // State filters - Sudeste
            if (!empty($filters['espirito_santo']) && $filters['espirito_santo'] === 'espirito_santo') {
                $where_clause .= " AND c.estado = 'ES'";
            }
            
            if (!empty($filters['minas_gerais']) && $filters['minas_gerais'] === 'minas_gerais') {
                $where_clause .= " AND c.estado = 'MG'";
            }
            
            if (!empty($filters['rio_de_janeiro']) && $filters['rio_de_janeiro'] === 'rio_de_janeiro') {
                $where_clause .= " AND c.estado = 'RJ'";
            }
            
            if (!empty($filters['sao_paulo_estado']) && $filters['sao_paulo_estado'] === 'sao_paulo_estado') {
                $where_clause .= " AND c.estado = 'SP'";
            }
            
            // State filters - Sul
            if (!empty($filters['parana']) && $filters['parana'] === 'parana') {
                $where_clause .= " AND c.estado = 'PR'";
            }
            
            if (!empty($filters['rio_grande_do_sul']) && $filters['rio_grande_do_sul'] === 'rio_grande_do_sul') {
                $where_clause .= " AND c.estado = 'RS'";
            }
            
            if (!empty($filters['santa_catarina']) && $filters['santa_catarina'] === 'santa_catarina') {
                $where_clause .= " AND c.estado = 'SC'";
            }
            
            // New Segmento filters
            if (!empty($filters['machines']) && $filters['machines'] === 'machines') {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'machines'";
            }
            
            if (!empty($filters['bearings']) && $filters['bearings'] === 'bearings') {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'bearings'";
            }
            
            if (!empty($filters['auto']) && $filters['auto'] === 'auto') {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'auto'";
            }
            
            if (!empty($filters['parts']) && $filters['parts'] === 'parts') {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'parts'";
            }
            
            if (!empty($filters['faucets']) && $filters['faucets'] === 'faucets') {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'faucets'";
            }
            
            // Vendedor filters
            if (!empty($filters['vendedor_regiane']) && $filters['vendedor_regiane'] === 'vendedor_regiane') {
                $where_clause .= " AND c.vendedor = 107";
            }
            
            if (!empty($filters['vendedor_rosana']) && $filters['vendedor_rosana'] === 'vendedor_rosana') {
                $where_clause .= " AND c.vendedor = 131";
            }
            
            if (!empty($filters['vendedor_edilene']) && $filters['vendedor_edilene'] === 'vendedor_edilene') {
                $where_clause .= " AND c.vendedor = 32";
            }
            
            if (!empty($filters['vendedor_debora']) && $filters['vendedor_debora'] === 'vendedor_debora') {
                $where_clause .= " AND c.vendedor = 89";
            }
            
            if (!empty($filters['vendedor_miriam']) && $filters['vendedor_miriam'] === 'vendedor_miriam') {
                $where_clause .= " AND c.vendedor = 117";
            }
            
            if (!empty($filters['vendedor_andrea']) && $filters['vendedor_andrea'] === 'vendedor_andrea') {
                $where_clause .= " AND c.vendedor = 218";
            }
            
            if (!empty($filters['vendedor_cristiane']) && $filters['vendedor_cristiane'] === 'vendedor_cristiane') {
                $where_clause .= " AND c.vendedor = 197";
            }
            
            if (!empty($filters['vendedor_luana']) && $filters['vendedor_luana'] === 'vendedor_luana') {
                $where_clause .= " AND c.vendedor = 1030";
            }
            
            if (!empty($filters['vendedor_revisar']) && $filters['vendedor_revisar'] === 'vendedor_revisar') {
                $where_clause .= " AND c.vendedor = 8";
            }
            
            $sql = "SELECT 
                c.id as cliente_id,
                LCASE(c.nome) as cliente_nome,
                LCASE(c.bairro) as bairro,
                LCASE(c.cidade) as cliente_cidade,
                c.estado,
                LCASE(c.ender) as cliente_endereco,
                c.cnpj,
                LCASE(u.nick) as nick,
                (SELECT p.segmento 
                 FROM hoje h 
                 LEFT JOIN hist hi on hi.pedido=h.id 
                 LEFT JOIN inv i on i.id=hi.isbn 
                 LEFT JOIN produtos p on p.id=i.idcf 
                 WHERE h.nop in (27,27,51,76) and h.idcli = c.id 
                 ORDER BY h.valor DESC
                 LIMIT 1) AS segmento,
                c.rfm,
                (SELECT LCASE(class) FROM clientes_class cc WHERE cc.id = c.vip LIMIT 1) AS vip,
                (SELECT COALESCE(SUM(h.valor), 0) 
                 FROM hoje h 
                 WHERE h.idcli = c.id 
                 AND h.prazo <> 15 and nop IN (27,28,51,76)
                 AND YEAR(h.data) = YEAR(CURRENT_DATE) 
                 AND MONTH(h.data) = MONTH(CURRENT_DATE)) AS mes,
                (SELECT COALESCE(SUM(h.valor), 0) 
                 FROM hoje h 
                 WHERE h.idcli = c.id 
                 AND h.prazo <> 15 and nop IN (27,28,51,76)
                 AND YEAR(h.data) = YEAR(CURRENT_DATE)) AS ano_corrente,
                (SELECT COALESCE(SUM(h.valor), 0) 
                 FROM hoje h 
                 WHERE h.idcli = c.id 
                 AND h.prazo <> 15 and nop IN (27,28,51,76)
                 AND YEAR(h.data) = YEAR(CURRENT_DATE) - 1) AS ano_anterior,
                (SELECT COALESCE(SUM(h.valor), 0) 
                 FROM hoje h 
                 WHERE h.idcli = c.id 
                 AND h.prazo <> 15 and nop IN (27,28,51,76)) AS total,
                COALESCE(DATEDIFF(CURRENT_DATE, 
                    (SELECT MAX(h.data) 
                     FROM hoje h 
                     WHERE h.nop in (27,27,51,76) and h.idcli = c.id)), 0) AS dias_ultima_compra
            FROM 
                clientes c
            LEFT JOIN 
                users u ON u.id = c.vendedor
            $where_clause
            ORDER BY $order_column $order_dir
            LIMIT $start, $length";
            
            $result = $mysqli->query($sql);
            
            if (!$result) {
                error_log('Query failed: ' . $mysqli->error);
                $mysqli->close();
                return array();
            }
            
            $customers = array();
            while ($row = $result->fetch_assoc()) {
                // Sanitize and validate all values
                $cliente_id = isset($row['cliente_id']) ? intval($row['cliente_id']) : 0;
                $cliente_nome = isset($row['cliente_nome']) ? mb_convert_encoding($row['cliente_nome'], 'UTF-8', 'UTF-8') : 'N/A';
                $cliente_cidade = isset($row['cliente_cidade']) ? mb_convert_encoding($row['cliente_cidade'], 'UTF-8', 'UTF-8') : 'N/A';
                $estado = isset($row['estado']) ? mb_convert_encoding($row['estado'], 'UTF-8', 'UTF-8') : 'N/A';
                $bairro = isset($row['bairro']) ? mb_convert_encoding($row['bairro'], 'UTF-8', 'UTF-8') : 'N/A';
                $cliente_endereco = isset($row['cliente_endereco']) ? mb_convert_encoding($row['cliente_endereco'], 'UTF-8', 'UTF-8') : 'N/A';
                $nick = isset($row['nick']) ? mb_convert_encoding($row['nick'], 'UTF-8', 'UTF-8') : 'N/A';
                $segmento = isset($row['segmento']) ? mb_convert_encoding($row['segmento'], 'UTF-8', 'UTF-8') : 'N/A';
                $rfm = isset($row['rfm']) ? mb_convert_encoding($row['rfm'], 'UTF-8', 'UTF-8') : 'N/A';
                
                // Format numeric values
                $mes = floatval($row['mes'] ?? 0);
                $ano_corrente = floatval($row['ano_corrente'] ?? 0);
                $ano_anterior = floatval($row['ano_anterior'] ?? 0);
                $total = floatval($row['total'] ?? 0);
                $dias_ultima_compra = intval($row['dias_ultima_compra'] ?? 0);
                
                $customers[] = [
                    '<span class="text-xs cursor-pointer text-blue-600 hover:text-blue-800 hover:underline" onclick="openClienteDetails(' . $cliente_id . ', \'' . addslashes(ucwords($cliente_nome)) . '\')">👤 ' . ucwords($cliente_nome) . '</span>',
                    '<span class="text-xs">' . ucwords($cliente_cidade) . '</span>',
                    '<span class="text-xs">' . $estado . '</span>',
                    '<span class="text-xs">' . ucwords($bairro) . '</span>',
                    '<span class="text-xs">' . ucwords($cliente_endereco) . '</span>',
                    '<span class="text-xs">' . ucwords($nick) . '</span>',
                    '<span class="text-xs">' . ucwords($segmento) . '</span>',
                    '<span class="text-xs">' . ucwords($rfm) . '</span>',
                    '<span class="text-xs">' . number_format($mes, 0, ',', '.') . '</span>',
                    '<span class="text-xs">' . number_format($ano_corrente, 0, ',', '.') . '</span>',
                    '<span class="text-xs">' . number_format($ano_anterior, 0, ',', '.') . '</span>',
                    '<span class="text-xs">' . number_format($total, 0, ',', '.') . '</span>',
                    '<span class="text-xs">' . $dias_ultima_compra . ' dias</span>'
                ];
            }
            
            $mysqli->close();
            return $customers;
            
        } catch (Exception $e) {
            error_log('Customer AJAX query error: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Get total count of customers
     */
    private function get_total_customers_count() {
        try {
            $mysqli = new mysqli('vallery.catmgckfixum.sa-east-1.rds.amazonaws.com', 'robsonrr', 'Best94364811082', 'mak');
            
            if ($mysqli->connect_error) {
                error_log('Connection failed: ' . $mysqli->connect_error);
                return 0;
            }
            
            $sql = "SELECT COUNT(*) as total FROM clientes c LEFT JOIN users u ON u.id = c.vendedor WHERE c.nome <> '' AND c.cidade IS NOT NULL AND c.cidade <> '' AND u.id != 9 AND u.id != 1011";
            $result = $mysqli->query($sql);
            
            if ($result === false) {
                error_log('Query failed: ' . $mysqli->error);
                $mysqli->close();
                return 0;
            }
            
            if ($result->num_rows === 0) {
                $mysqli->close();
                return 0;
            }
            
            $row = $result->fetch_assoc();
            $mysqli->close();
            
            return intval($row['total'] ?? 0);
        } catch (Exception $e) {
            error_log('Total count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get filtered count of customers
     */
    private function get_filtered_customers_count($search, $filters = array()) {
        try {
            $mysqli = new mysqli('vallery.catmgckfixum.sa-east-1.rds.amazonaws.com', 'robsonrr', 'Best94364811082', 'mak');
            
            if ($mysqli->connect_error) {
                error_log('Connection failed: ' . $mysqli->connect_error);
                return 0;
            }
            
            $where_clause = "WHERE c.nome <> '' AND c.cidade IS NOT NULL AND c.cidade <> '' AND u.id != 9 AND u.id != 1011";
            
            // Add search filter
            if (!empty($search)) {
                $search = $mysqli->real_escape_string($search);
                $where_clause .= " AND (c.nome LIKE '%$search%' OR c.cidade LIKE '%$search%' OR c.estado LIKE '%$search%' OR c.bairro LIKE '%$search%' OR c.ender LIKE '%$search%' OR u.nick LIKE '%$search%')";
            }
            
            // Add custom filters
            if (!empty($filters['estado'])) {
                $estado = $mysqli->real_escape_string($filters['estado']);
                $where_clause .= " AND c.estado = '$estado'";
            }
            
            if (!empty($filters['vendedor'])) {
                $vendedor = $mysqli->real_escape_string($filters['vendedor']);
                $where_clause .= " AND u.nick = '$vendedor'";
            }
            
            if (!empty($filters['rfm'])) {
                $rfm = $mysqli->real_escape_string($filters['rfm']);
                $where_clause .= " AND c.rfm = '$rfm'";
            }
            
            if (!empty($filters['ultima_compra'])) {
                if ($filters['ultima_compra'] === '30-60') {
                    $where_clause .= " AND (SELECT COALESCE(DATEDIFF(CURRENT_DATE, MAX(h.data)), 999) 
                                          FROM hoje h 
                                          WHERE h.nop IN (27,28,51,76) AND h.idcli = c.id) BETWEEN 30 AND 60";
                } elseif ($filters['ultima_compra'] === '60-90') {
                    $where_clause .= " AND (SELECT COALESCE(DATEDIFF(CURRENT_DATE, MAX(h.data)), 999) 
                                          FROM hoje h 
                                          WHERE h.nop IN (27,28,51,76) AND h.idcli = c.id) BETWEEN 60 AND 90";
                } elseif ($filters['ultima_compra'] === '90-180') {
                    $where_clause .= " AND (SELECT COALESCE(DATEDIFF(CURRENT_DATE, MAX(h.data)), 999) 
                                          FROM hoje h 
                                          WHERE h.nop IN (27,28,51,76) AND h.idcli = c.id) BETWEEN 90 AND 180";
                } elseif ($filters['ultima_compra'] === '180-365') {
                    $where_clause .= " AND (SELECT COALESCE(DATEDIFF(CURRENT_DATE, MAX(h.data)), 999) 
                                          FROM hoje h 
                                          WHERE h.nop IN (27,28,51,76) AND h.idcli = c.id) BETWEEN 180 AND 365";
                } elseif ($filters['ultima_compra'] === '>365') {
                    $where_clause .= " AND (SELECT COALESCE(DATEDIFF(CURRENT_DATE, MAX(h.data)), 999) 
                                          FROM hoje h 
                                          WHERE h.nop IN (27,28,51,76) AND h.idcli = c.id) > 365";
                }
            }
            
            if (!empty($filters['rua_sao_caetano']) && $filters['rua_sao_caetano'] === 'sao_caetano') {
                $where_clause .= " AND c.ender LIKE '%sao caetano%' AND c.cidade = 'sao paulo'";
            }
            
            if (!empty($filters['bom_retiro']) && $filters['bom_retiro'] === 'bom_retiro') {
                $where_clause .= " AND c.bairro LIKE '%bom retiro%' AND c.cidade = 'sao paulo'";
            }
            
            // Segmento filter
            if (!empty($filters['segmento'])) {
                $segmento = $mysqli->real_escape_string($filters['segmento']);
                if ($segmento === 'exclude_parts') {
                    $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) != 'parts'";
                } elseif ($segmento === 'only_parts') {
                    $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'parts'";
                } elseif ($segmento !== 'all') {
                    $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = '$segmento'";
                }
            }
            
            // New Segmento filters
            if (!empty($filters['machines'])) {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'machines'";
            }
            
            if (!empty($filters['bearings'])) {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'bearings'";
            }
            
            if (!empty($filters['auto'])) {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'auto'";
            }
            
            if (!empty($filters['parts'])) {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'parts'";
            }
            
            if (!empty($filters['faucets'])) {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'faucets'";
            }
            
            if (!empty($filters['vendas_mes_zero'])) {
                if ($filters['vendas_mes_zero'] === 'mes_zero') {
                    $where_clause .= " AND (SELECT COALESCE(SUM(h.valor), 0) 
                                          FROM hoje h 
                                          WHERE h.idcli = c.id 
                                          AND h.prazo <> 15 and nop IN (27,28,51,76)
                                          AND YEAR(h.data) = YEAR(CURRENT_DATE) 
                                          AND MONTH(h.data) = MONTH(CURRENT_DATE)) = 0";
                } elseif ($filters['vendas_mes_zero'] === 'mes_positive') {
                    $where_clause .= " AND (SELECT COALESCE(SUM(h.valor), 0) 
                                          FROM hoje h 
                                          WHERE h.idcli = c.id 
                                          AND h.prazo <> 15 and nop IN (27,28,51,76)
                                          AND YEAR(h.data) = YEAR(CURRENT_DATE) 
                                          AND MONTH(h.data) = MONTH(CURRENT_DATE)) > 0";
                }
            }
            
            if (!empty($filters['vendas_ano_zero'])) {
                if ($filters['vendas_ano_zero'] === 'ano_zero') {
                    $where_clause .= " AND (SELECT COALESCE(SUM(h.valor), 0) 
                                          FROM hoje h 
                                          WHERE h.idcli = c.id 
                                          AND h.prazo <> 15 and nop IN (27,28,51,76)
                                          AND YEAR(h.data) = YEAR(CURRENT_DATE)) = 0";
                } elseif ($filters['vendas_ano_zero'] === 'ano_positive') {
                    $where_clause .= " AND (SELECT COALESCE(SUM(h.valor), 0) 
                                          FROM hoje h 
                                          WHERE h.idcli = c.id 
                                          AND h.prazo <> 15 and nop IN (27,28,51,76)
                                          AND YEAR(h.data) = YEAR(CURRENT_DATE)) > 0";
                }
            }
            
            // City filters
            if (!empty($filters['sao_paulo']) && $filters['sao_paulo'] === 'sao_paulo') {
                $where_clause .= " AND c.cidade = 'sao paulo'";
            }
            
            if (!empty($filters['sao_paulo_center']) && $filters['sao_paulo_center'] === 'sao_paulo_center') {
                $where_clause .= " AND c.cidade = 'sao paulo' AND (c.bairro LIKE '%luz%' OR c.bairro LIKE '%bras%' OR c.bairro LIKE '%bom retiro%')";
            }
            
            if (!empty($filters['sao_paulo_side']) && $filters['sao_paulo_side'] === 'sao_paulo_side') {
                $where_clause .= " AND c.cidade = 'sao paulo' AND c.bairro NOT LIKE '%luz%' AND c.bairro NOT LIKE '%bras%' AND c.bairro NOT LIKE '%bom retiro%'";
            }
            
            if (!empty($filters['blumenau']) && $filters['blumenau'] === 'blumenau') {
                $where_clause .= " AND c.cidade = 'blumenau'";
            }
            
            if (!empty($filters['curitiba']) && $filters['curitiba'] === 'curitiba') {
                $where_clause .= " AND c.cidade = 'curitiba'";
            }
            
            if (!empty($filters['maringa']) && $filters['maringa'] === 'maringa') {
                $where_clause .= " AND c.cidade = 'maringa'";
            }
            
            if (!empty($filters['bauru']) && $filters['bauru'] === 'bauru') {
                $where_clause .= " AND c.cidade = 'Bauru'";
            }
            
            if (!empty($filters['birigui']) && $filters['birigui'] === 'birigui') {
                $where_clause .= " AND c.cidade = 'Birigui'";
            }
            
            if (!empty($filters['belo_horizonte']) && $filters['belo_horizonte'] === 'belo_horizonte') {
                $where_clause .= " AND c.cidade = 'belo horizonte'";
            }
            
            if (!empty($filters['franca']) && $filters['franca'] === 'franca') {
                $where_clause .= " AND c.cidade = 'franca'";
            }
            
            if (!empty($filters['fortaleza']) && $filters['fortaleza'] === 'fortaleza') {
                $where_clause .= " AND c.cidade = 'Fortaleza'";
            }
            
            if (!empty($filters['juiz_de_fora']) && $filters['juiz_de_fora'] === 'juiz_de_fora') {
                $where_clause .= " AND c.cidade = 'juiz de fora'";
            }
            
            // State filters - Norte
            if (!empty($filters['acre']) && $filters['acre'] === 'acre') {
                $where_clause .= " AND c.estado = 'AC'";
            }
            
            if (!empty($filters['amapa']) && $filters['amapa'] === 'amapa') {
                $where_clause .= " AND c.estado = 'AP'";
            }
            
            if (!empty($filters['amazonas']) && $filters['amazonas'] === 'amazonas') {
                $where_clause .= " AND c.estado = 'AM'";
            }
            
            if (!empty($filters['para']) && $filters['para'] === 'para') {
                $where_clause .= " AND c.estado = 'PA'";
            }
            
            if (!empty($filters['rondonia']) && $filters['rondonia'] === 'rondonia') {
                $where_clause .= " AND c.estado = 'RO'";
            }
            
            if (!empty($filters['roraima']) && $filters['roraima'] === 'roraima') {
                $where_clause .= " AND c.estado = 'RR'";
            }
            
            if (!empty($filters['tocantins']) && $filters['tocantins'] === 'tocantins') {
                $where_clause .= " AND c.estado = 'TO'";
            }
            
            // State filters - Nordeste
            if (!empty($filters['alagoas']) && $filters['alagoas'] === 'alagoas') {
                $where_clause .= " AND c.estado = 'AL'";
            }
            
            if (!empty($filters['bahia']) && $filters['bahia'] === 'bahia') {
                $where_clause .= " AND c.estado = 'BA'";
            }
            
            if (!empty($filters['ceara']) && $filters['ceara'] === 'ceara') {
                $where_clause .= " AND c.estado = 'CE'";
            }
            
            if (!empty($filters['maranhao']) && $filters['maranhao'] === 'maranhao') {
                $where_clause .= " AND c.estado = 'MA'";
            }
            
            if (!empty($filters['paraiba']) && $filters['paraiba'] === 'paraiba') {
                $where_clause .= " AND c.estado = 'PB'";
            }
            
            if (!empty($filters['pernambuco']) && $filters['pernambuco'] === 'pernambuco') {
                $where_clause .= " AND c.estado = 'PE'";
            }
            
            if (!empty($filters['piaui']) && $filters['piaui'] === 'piaui') {
                $where_clause .= " AND c.estado = 'PI'";
            }
            
            if (!empty($filters['rio_grande_do_norte']) && $filters['rio_grande_do_norte'] === 'rio_grande_do_norte') {
                $where_clause .= " AND c.estado = 'RN'";
            }
            
            if (!empty($filters['sergipe']) && $filters['sergipe'] === 'sergipe') {
                $where_clause .= " AND c.estado = 'SE'";
            }
            
            // State filters - Centro-Oeste
            if (!empty($filters['distrito_federal']) && $filters['distrito_federal'] === 'distrito_federal') {
                $where_clause .= " AND c.estado = 'DF'";
            }
            
            if (!empty($filters['goias']) && $filters['goias'] === 'goias') {
                $where_clause .= " AND c.estado = 'GO'";
            }
            
            if (!empty($filters['mato_grosso']) && $filters['mato_grosso'] === 'mato_grosso') {
                $where_clause .= " AND c.estado = 'MT'";
            }
            
            if (!empty($filters['mato_grosso_do_sul']) && $filters['mato_grosso_do_sul'] === 'mato_grosso_do_sul') {
                $where_clause .= " AND c.estado = 'MS'";
            }
            
            // State filters - Sudeste
            if (!empty($filters['espirito_santo']) && $filters['espirito_santo'] === 'espirito_santo') {
                $where_clause .= " AND c.estado = 'ES'";
            }
            
            if (!empty($filters['minas_gerais']) && $filters['minas_gerais'] === 'minas_gerais') {
                $where_clause .= " AND c.estado = 'MG'";
            }
            
            if (!empty($filters['rio_de_janeiro']) && $filters['rio_de_janeiro'] === 'rio_de_janeiro') {
                $where_clause .= " AND c.estado = 'RJ'";
            }
            
            if (!empty($filters['sao_paulo_estado']) && $filters['sao_paulo_estado'] === 'sao_paulo_estado') {
                $where_clause .= " AND c.estado = 'SP'";
            }
            
            // State filters - Sul
            if (!empty($filters['parana']) && $filters['parana'] === 'parana') {
                $where_clause .= " AND c.estado = 'PR'";
            }
            
            if (!empty($filters['rio_grande_do_sul']) && $filters['rio_grande_do_sul'] === 'rio_grande_do_sul') {
                $where_clause .= " AND c.estado = 'RS'";
            }
            
            if (!empty($filters['santa_catarina']) && $filters['santa_catarina'] === 'santa_catarina') {
                $where_clause .= " AND c.estado = 'SC'";
            }
            
            // New Segmento filters
            if (!empty($filters['machines']) && $filters['machines'] === 'machines') {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'machines'";
            }
            
            if (!empty($filters['bearings']) && $filters['bearings'] === 'bearings') {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'bearings'";
            }
            
            if (!empty($filters['auto']) && $filters['auto'] === 'auto') {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'auto'";
            }
            
            if (!empty($filters['parts']) && $filters['parts'] === 'parts') {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'parts'";
            }
            
            if (!empty($filters['faucets']) && $filters['faucets'] === 'faucets') {
                $where_clause .= " AND (SELECT p.segmento FROM hoje h LEFT JOIN hist hi on hi.pedido=h.id LEFT JOIN inv i on i.id=hi.isbn LEFT JOIN produtos p on p.id=i.idcf WHERE h.nop in (27,28,51,76) and h.idcli = c.id ORDER BY h.valor DESC LIMIT 1) = 'faucets'";
            }
            
            // Vendedor filters
            if (!empty($filters['vendedor_regiane']) && $filters['vendedor_regiane'] === 'vendedor_regiane') {
                $where_clause .= " AND c.vendedor = 107";
            }
            
            if (!empty($filters['vendedor_rosana']) && $filters['vendedor_rosana'] === 'vendedor_rosana') {
                $where_clause .= " AND c.vendedor = 131";
            }
            
            if (!empty($filters['vendedor_edilene']) && $filters['vendedor_edilene'] === 'vendedor_edilene') {
                $where_clause .= " AND c.vendedor = 32";
            }
            
            if (!empty($filters['vendedor_debora']) && $filters['vendedor_debora'] === 'vendedor_debora') {
                $where_clause .= " AND c.vendedor = 89";
            }
            
            if (!empty($filters['vendedor_miriam']) && $filters['vendedor_miriam'] === 'vendedor_miriam') {
                $where_clause .= " AND c.vendedor = 117";
            }
            
            if (!empty($filters['vendedor_andrea']) && $filters['vendedor_andrea'] === 'vendedor_andrea') {
                $where_clause .= " AND c.vendedor = 218";
            }
            
            if (!empty($filters['vendedor_cristiane']) && $filters['vendedor_cristiane'] === 'vendedor_cristiane') {
                $where_clause .= " AND c.vendedor = 197";
            }
            
            if (!empty($filters['vendedor_luana']) && $filters['vendedor_luana'] === 'vendedor_luana') {
                $where_clause .= " AND c.vendedor = 1030";
            }
            
            if (!empty($filters['vendedor_revisar']) && $filters['vendedor_revisar'] === 'vendedor_revisar') {
                $where_clause .= " AND c.vendedor = 8";
            }
            
            $sql = "SELECT COUNT(*) as total FROM clientes c LEFT JOIN users u ON u.id = c.vendedor $where_clause";
            $result = $mysqli->query($sql);
            
            if ($result === false) {
                error_log('Query failed: ' . $mysqli->error);
                $mysqli->close();
                return 0;
            }
            
            if ($result->num_rows === 0) {
                $mysqli->close();
                return 0;
            }
            
            $row = $result->fetch_assoc();
            $mysqli->close();
            
            return intval($row['total'] ?? 0);
        } catch (Exception $e) {
            error_log('Filtered count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get monthly sales data for charts
     */
    public function action_monthly_sales() {
        try {
            // Usar conexão direta MySQLi
            $mysqli = new mysqli('vallery.catmgckfixum.sa-east-1.rds.amazonaws.com', 'robsonrr', 'Best94364811082', 'mak');
            
            if ($mysqli->connect_error) {
                error_log('Connection failed: ' . $mysqli->connect_error);
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode(['error' => 'Database connection failed']));
                return;
            }
            
            // Construir cláusula WHERE básica (sem filtros por enquanto)
             $where_clause = "";
            
            // Query para dados mensais do ano atual
            $sql_current_year = "SELECT 
                MONTH(h.data) as mes,
                SUM(h.valor) as total_vendas
            FROM hoje h
            LEFT JOIN clientes c ON c.id = h.idcli
            LEFT JOIN users u ON u.id = c.vendedor
            WHERE YEAR(h.data) = YEAR(CURRENT_DATE)
                AND h.prazo <> 15 
                AND h.nop IN (27,28,51,76)
                $where_clause
            GROUP BY MONTH(h.data)
            ORDER BY MONTH(h.data)";
            
            // Query para dados mensais do ano passado
            $sql_last_year = "SELECT 
                MONTH(h.data) as mes,
                SUM(h.valor) as total_vendas
            FROM hoje h
            LEFT JOIN clientes c ON c.id = h.idcli
            LEFT JOIN users u ON u.id = c.vendedor
            WHERE YEAR(h.data) = YEAR(CURRENT_DATE) - 1
                AND h.prazo <> 15 
                AND h.nop IN (27,28,51,76)
                $where_clause
            GROUP BY MONTH(h.data)
            ORDER BY MONTH(h.data)";
            
            // Executar queries
            $result_current = $mysqli->query($sql_current_year);
            $result_last = $mysqli->query($sql_last_year);
            
            if (!$result_current) {
                error_log('Current year query failed: ' . $mysqli->error);
            }
            
            if (!$result_last) {
                error_log('Last year query failed: ' . $mysqli->error);
            }
            
            // Inicializar arrays com 12 meses zerados
            $current_year_data = array_fill(1, 12, 0);
            $last_year_data = array_fill(1, 12, 0);
            
            // Preencher dados do ano atual
            if ($result_current) {
                while ($row = $result_current->fetch_assoc()) {
                    $current_year_data[intval($row['mes'])] = floatval($row['total_vendas']);
                }
            }
            
            // Preencher dados do ano passado
            if ($result_last) {
                while ($row = $result_last->fetch_assoc()) {
                    $last_year_data[intval($row['mes'])] = floatval($row['total_vendas']);
                }
            }
            
            $mysqli->close();
            
            // Retornar dados em formato JSON
            $response = [
                'current_year' => array_values($current_year_data),
                'last_year' => array_values($last_year_data),
                'months' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
            ];
            
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode($response));
            
        } catch (Exception $e) {
            error_log('Monthly sales error: ' . $e->getMessage());
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['error' => 'Failed to get monthly sales data']));
        }
    }
}