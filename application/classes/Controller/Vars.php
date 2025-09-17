<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Vars extends Controller_Website {
    
    public function before() {
        parent::before();
        
        if (!isset($_SESSION['MM_Userid']) || $_SESSION['MM_Nivel'] < 2) {
            header('Location: /leads8/');
            exit;
        }
    }

    public function action_index() {
        $vars = $this->getAllVars();
        $total_records = count($vars);
        $page = (int) $this->request->query('page') ?: 1;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;
        $vars_page = array_slice($vars, $offset, $per_page);
        $total_pages = ceil($total_records / $per_page);

        // Process each variable record to format fields
        $processed_vars = [];
        foreach ($vars_page as $var) {
            $processed_var = $var;
            $id = $var['id'];
            
            // Format all editable fields
            $editable_fields = [
                'Dolar', 'Yuan', 'Cash', 'FixPlus', 'FixLess', 'Buildings',
                'TargetSetsMachinesDaily', 'Target_Machines', 'Target_Bearings',
                'Target_Parts', 'Target_Auto', 'TargetMachinesCustomers',
                'TargetMachinesCidades', 'TargetBearingsCustomers', 'TargetBearingsCidades',
                'TargetAutoCidades', 'TargetPartsCidades', 'TargetAutoCustomers',
                'TargetPartsCustomers', 'PayableExporter', 'PaidExporter', 'ReceivedExporter'
            ];
            
            foreach ($editable_fields as $field) {
                if (isset($var[$field])) {
                    $processed_var[$field] = $this->format_field_value($field, $var[$field], $id);
                }
            }
            
            $processed_vars[] = $processed_var;
        }

        $data = [
            'vars' => $processed_vars,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total_records,
            'per_page' => $per_page
        ];

        $data = $this->acesso_geral($data);
        return $this->render('vars/index', $data);
    }

    public function action_edit() {
        $id = $this->request->param('id');
        
        if ($this->request->method() === Request::POST) {
            $this->updateVar($id);
            header('Location: /leads8/vars/');
            exit;
        }

        $var = $this->getVarById($id);
        if (!$var) {
            header('Location: /leads8/vars/');
            exit;
        }

        $array = array();
        $array['var'] = $var;
        $array['page_title'] = 'Editar Variável';
        $array['field_categories'] = $this->getFieldCategories();
        
        return $this->render('vars/edit', $array);
    }

    public function action_view() {
        $id = $this->request->param('id');
        $var = $this->getVarById($id);
        
        if (!$var) {
            header('Location: /leads8/vars/');
            exit;
        }

        $array = array();
        $array['var'] = $var;
        $array['page_title'] = 'Visualizar Variável';
        $array['field_categories'] = $this->getFieldCategories();
        
        return $this->render('vars/view', $array);
    }

    public function action_delete() {
        $id = $this->request->param('id');
        $this->deleteVar($id);
        header('Location: /leads8/vars/');
        exit;
    }

    public function action_edit_field() {
        if (!isset($_SESSION['MM_Userid']) || $_SESSION['MM_Nivel'] < 2) {
            die('Acesso negado');
        }

        $id = $this->request->param('id');
        $field = $this->request->param('field');
        
        // Lista de campos permitidos para edição
        $allowed_fields = [
            'Dolar', 'Yuan', 'Cash', 'FixPlus', 'FixLess', 'Buildings',
            'TargetSetsMachinesDaily', 'Target_Machines', 'Target_Bearings',
            'Target_Parts', 'Target_Auto', 'TargetMachinesCustomers',
            'TargetMachinesCidades', 'TargetBearingsCustomers', 'TargetBearingsCidades',
            'TargetAutoCidades', 'TargetPartsCidades', 'TargetAutoCustomers',
            'TargetPartsCustomers', 'PayableExporter', 'PaidExporter',
            'ReceivedExporter', 'Workers_Machines', 'Workers_Bearings',
            'Workers_Parts', 'Workers_Auto',
            'PayExporter1', 'PayExporter2', 'PayExporter3', 'PayExporter4',
            'PayExporter5', 'PayExporter6', 'PayExporter7', 'PayExporter8',
            'PayExporter9', 'PayExporter10', 'PayExporter11', 'PayExporter12'
        ];

        if (!in_array($field, $allowed_fields)) {
            die('Campo inválido');
        }

        $var = $this->getVarById($id);
        if (!$var) {
            die('Variável não encontrada');
        }

        $current_value = $var[$field];
        
        // Retorna o formulário de edição
        echo '<input type="text" name="' . $field . '" value="' . htmlspecialchars($current_value) . '" 
               class="form-control form-control-sm" 
               autofocus>';
    }

    public function action_update_field() {
        if (!isset($_SESSION['MM_Userid']) || $_SESSION['MM_Nivel'] < 2) {
            die('Acesso negado');
        }

        if ($this->request->method() !== Request::POST) {
            die('Método não permitido');
        }

        $id = $this->request->post('id');
        $field = $this->request->post('field');
        $value = $this->request->post($field);

        // Lista de campos permitidos para edição
        $allowed_fields = [
            'Dolar', 'Yuan', 'Cash', 'FixPlus', 'FixLess', 'Buildings',
            'TargetSetsMachinesDaily', 'Target_Machines', 'Target_Bearings',
            'Target_Parts', 'Target_Auto', 'TargetMachinesCustomers',
            'TargetMachinesCidades', 'TargetBearingsCustomers', 'TargetBearingsCidades',
            'TargetAutoCidades', 'TargetPartsCidades', 'TargetAutoCustomers',
            'TargetPartsCustomers', 'PayableExporter', 'PaidExporter',
            'ReceivedExporter', 'Workers_Machines', 'Workers_Bearings',
            'Workers_Parts', 'Workers_Auto',
            'PayExporter1', 'PayExporter2', 'PayExporter3', 'PayExporter4',
            'PayExporter5', 'PayExporter6', 'PayExporter7', 'PayExporter8',
            'PayExporter9', 'PayExporter10', 'PayExporter11', 'PayExporter12'
        ];

        if (!in_array($field, $allowed_fields)) {
            die('Campo inválido');
        }

        try {
            $sql = "UPDATE mak.Vars SET $field = :value WHERE id = :id";
            $query = DB::query(Database::UPDATE, $sql);
            $query->parameters([
                ':value' => $value,
                ':id' => $id
            ]);
            $query->execute();

            // Retorna o valor formatado de acordo com o tipo do campo
            $formatted_value = $this->format_field_value($field, $value, $id);
            echo $formatted_value;
        } catch (Exception $e) {
            error_log('Erro ao atualizar campo: ' . $e->getMessage());
            die('Erro ao atualizar');
        }
    }

    private function getBadgeClassForField($field) {
        // Define badge classes for different field types
        $field_classes = [
            'Cash' => 'bg-success',
            'Dolar' => 'bg-warning',
            'Yuan' => 'bg-warning',
            'FixPlus' => 'bg-primary',
            'FixLess' => 'bg-primary',
            'Buildings' => 'bg-secondary',
            'PayableExporter' => 'bg-danger',
            'PaidExporter' => 'bg-success',
            'ReceivedExporter' => 'bg-info'
        ];
        
        // Target fields get info class
        $target_fields = [
            'TargetSetsMachinesDaily', 'Target_Machines', 'Target_Bearings',
            'Target_Parts', 'Target_Auto', 'TargetMachinesCustomers',
            'TargetMachinesCidades', 'TargetBearingsCustomers', 'TargetBearingsCidades',
            'TargetAutoCidades', 'TargetPartsCidades', 'TargetAutoCustomers',
            'TargetPartsCustomers'
        ];
        
        if (in_array($field, $target_fields)) {
            return 'bg-info';
        }
        
        return isset($field_classes[$field]) ? $field_classes[$field] : 'bg-secondary';
    }

    private function format_field_value($field, $value, $id = null) {
        // Campos monetários
        $money_fields = ['Dolar', 'Yuan', 'PayableExporter', 'PaidExporter', 'ReceivedExporter'];
        $payment_fields = ['PayExporter1', 'PayExporter2', 'PayExporter3', 'PayExporter4', 
                          'PayExporter5', 'PayExporter6', 'PayExporter7', 'PayExporter8',
                          'PayExporter9', 'PayExporter10', 'PayExporter11', 'PayExporter12'];

        if (in_array($field, $money_fields) || in_array($field, $payment_fields)) {
            $formatted_value = 'R$ ' . number_format($value, 2, '.', ',');
            $badge_class = $this->getBadgeClassForField($field);
            return '<div style="cursor: pointer; padding: 8px; border-radius: 4px;" class="editable-field" onmouseover="this.style.backgroundColor=\'#f8f9fa\'" onmouseout="this.style.backgroundColor=\'transparent\'" ><span class="badge ' . $badge_class . ' fs-6">' . $formatted_value . '</span></div>';
        }

        // Campos numéricos simples
        $numeric_fields = ['Cash', 'FixPlus', 'FixLess', 'Buildings'];
        if (in_array($field, $numeric_fields)) {
            $formatted_value = number_format($value, 0, '.', ',');
            $badge_class = $this->getBadgeClassForField($field);
            return '<div style="cursor: pointer; padding: 8px; border-radius: 4px;" class="editable-field" onmouseover="this.style.backgroundColor=\'#f8f9fa\'" onmouseout="this.style.backgroundColor=\'transparent\'" ><span class="badge ' . $badge_class . ' fs-6">' . $formatted_value . '</span></div>';
        }

        // Campos de metas (com badge)
        $target_fields = [
            'TargetSetsMachinesDaily', 'Target_Machines', 'Target_Bearings',
            'Target_Parts', 'Target_Auto', 'TargetMachinesCustomers',
            'TargetMachinesCidades', 'TargetBearingsCustomers', 'TargetBearingsCidades',
            'TargetAutoCidades', 'TargetPartsCidades', 'TargetAutoCustomers',
            'TargetPartsCustomers'
        ];
        if (in_array($field, $target_fields)) {
            $badge_class = $this->getBadgeClassForField($field);
            return '<div style="cursor: pointer; padding: 8px; border-radius: 4px;" class="editable-field" onmouseover="this.style.backgroundColor=\'#f8f9fa\'" onmouseout="this.style.backgroundColor=\'transparent\'" ><span class="badge ' . $badge_class . ' fs-6">' . $value . '</span></div>';
        }

        // Campos de funcionários (com badge)
        $workers_fields = [
            'Workers_Machines' => 'bg-primary',
            'Workers_Bearings' => 'bg-dark', 
            'Workers_Parts' => 'bg-success',
            'Workers_Auto' => 'bg-info'
        ];
        if (array_key_exists($field, $workers_fields)) {
            $badge_class = $workers_fields[$field];
            return '<div style="cursor: pointer; padding: 8px; border-radius: 4px;" class="editable-field" onmouseover="this.style.backgroundColor=\'#f8f9fa\'" onmouseout="this.style.backgroundColor=\'transparent\'" ><span class="badge ' . $badge_class . ' fs-6">' . $value . '</span></div>';
        }

        return $value;
    }

    private function getAllVars() {
        $sql = "SELECT * FROM mak.Vars ORDER BY id DESC";
        $query = DB::query(Database::SELECT, $sql);
        return $query->execute('mak')->as_array();
    }

    private function getVarById($id) {
        $sql = "SELECT * FROM mak.Vars WHERE id = :id";
        $query = DB::query(Database::SELECT, $sql);
        $query->parameters([':id' => $id]);
        $result = $query->execute('mak')->as_array();
        return count($result) ? $result[0] : null;
    }

    private function updateVar($id) {
        error_log('Vars Update - Starting update for ID: ' . $id);
        $data = $this->request->post();
        error_log('Vars Update - POST data received: ' . print_r($data, true));
        
        $sql = "UPDATE mak.Vars SET 
                Dolar = :dolar,
                Yuan = :yuan,
                Cash = :cash,
                FixPlus = :fixplus,
                FixLess = :fixless,
                Buildings = :buildings,
                TargetSetsMachinesDaily = :targetsetsmachinesdaily,
                Target_Machines = :target_machines,
                Target_Bearings = :target_bearings,
                Target_Parts = :target_parts,
                Target_Auto = :target_auto,
                TargetMachinesCustomers = :targetmachinescustomers,
                TargetMachinesCidades = :targetmachinescidades,
                TargetBearingsCustomers = :targetbearingscustomers,
                TargetBearingsCidades = :targetbearingscidades,
                TargetAutoCidades = :targetautocidades,
                TargetPartsCidades = :targetpartscidades,
                TargetAutoCustomers = :targetautocustomers,
                TargetPartsCustomers = :targetpartscustomers,
                PayableExporter = :payableexporter,
                PayExporter1 = :payexporter1,
                PayExporter2 = :payexporter2,
                PayExporter3 = :payexporter3,
                PayExporter4 = :payexporter4,
                PayExporter5 = :payexporter5,
                PayExporter6 = :payexporter6,
                PayExporter7 = :payexporter7,
                PayExporter8 = :payexporter8,
                PayExporter9 = :payexporter9,
                PayExporter10 = :payexporter10,
                PayExporter11 = :payexporter11,
                PayExporter12 = :payexporter12,
                Workers_Machines = :workers_machines,
                Workers_Bearings = :workers_bearings,
                Workers_Parts = :workers_parts,
                Workers_Auto = :workers_auto,
                PaidExporter = :paidexporter,
                ReceivedExporter = :receivedexporter
                WHERE id = :id";

        $query = DB::query(Database::UPDATE, $sql);
        $query->parameters([
            ':dolar' => $data['Dolar'],
            ':yuan' => $data['Yuan'],
            ':cash' => $data['Cash'],
            ':fixplus' => $data['FixPlus'],
            ':fixless' => $data['FixLess'],
            ':buildings' => $data['Buildings'],
            ':targetsetsmachinesdaily' => $data['TargetSetsMachinesDaily'],
            ':target_machines' => $data['Target_Machines'],
            ':target_bearings' => $data['Target_Bearings'],
            ':target_parts' => $data['Target_Parts'],
            ':target_auto' => $data['Target_Auto'],
            ':targetmachinescustomers' => $data['TargetMachinesCustomers'],
            ':targetmachinescidades' => $data['TargetMachinesCidades'],
            ':targetbearingscustomers' => $data['TargetBearingsCustomers'],
            ':targetbearingscidades' => $data['TargetBearingsCidades'],
            ':targetautocidades' => $data['TargetAutoCidades'],
            ':targetpartscidades' => $data['TargetPartsCidades'],
            ':targetautocustomers' => $data['TargetAutoCustomers'],
            ':targetpartscustomers' => $data['TargetPartsCustomers'],
            ':payableexporter' => $data['PayableExporter'],
            ':payexporter1' => $data['PayExporter1'],
            ':payexporter2' => $data['PayExporter2'],
            ':payexporter3' => $data['PayExporter3'],
            ':payexporter4' => $data['PayExporter4'],
            ':payexporter5' => $data['PayExporter5'],
            ':payexporter6' => $data['PayExporter6'],
            ':payexporter7' => $data['PayExporter7'],
            ':payexporter8' => $data['PayExporter8'],
            ':payexporter9' => $data['PayExporter9'],
            ':payexporter10' => $data['PayExporter10'],
            ':payexporter11' => $data['PayExporter11'],
            ':payexporter12' => $data['PayExporter12'],
            ':workers_machines' => $data['Workers_Machines'],
            ':workers_bearings' => $data['Workers_Bearings'],
            ':workers_parts' => $data['Workers_Parts'],
            ':workers_auto' => $data['Workers_Auto'],
            ':paidexporter' => $data['PaidExporter'],
            ':receivedexporter' => $data['ReceivedExporter'],
            ':id' => $id
        ]);

        error_log('Vars Update - Executing SQL query');
        $query->execute('mak');
        error_log('Vars Update - SQL query executed successfully');
    }

    private function deleteVar($id) {
        $sql = "DELETE FROM mak.Vars WHERE id = :id";
        $query = DB::query(Database::DELETE, $sql);
        $query->parameters([':id' => $id]);
        $query->execute('mak');
    }

    private function getFieldCategories() {
        return [
            'Taxas de Câmbio' => ['Dolar', 'Yuan'],
            'Dados Financeiros' => ['Cash', 'FixPlus', 'FixLess', 'Buildings'],
            'Metas de Produção' => [
                'TargetSetsMachinesDaily',
                'Target_Machines',
                'Target_Bearings',
                'Target_Parts',
                'Target_Auto'
            ],
            'Metas Detalhadas por Setor' => [
                'TargetMachinesCustomers',
                'TargetMachinesCidades',
                'TargetBearingsCustomers',
                'TargetBearingsCidades',
                'TargetAutoCidades',
                'TargetPartsCidades',
                'TargetAutoCustomers',
                'TargetPartsCustomers'
            ],
            'Dados do Exportador' => [
                'PayableExporter',
                'PaidExporter',
                'ReceivedExporter'
            ],
            'Pagamentos por Mês' => [
                'PayExporter1', 'PayExporter2', 'PayExporter3',
                'PayExporter4', 'PayExporter5', 'PayExporter6',
                'PayExporter7', 'PayExporter8', 'PayExporter9',
                'PayExporter10', 'PayExporter11', 'PayExporter12'
            ],
            'Funcionários por Setor' => [
                'Workers_Machines',
                'Workers_Bearings',
                'Workers_Parts',
                'Workers_Auto'
            ]
        ];
    }

    private function getLatestVar() {
        $sql = "SELECT * FROM mak.Vars ORDER BY id DESC LIMIT 1";
        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute('mak')->as_array();
        return count($result) ? $result[0] : null;
    }

    public function action_fetch_rates() {
        if (!isset($_SESSION['MM_Userid']) || $_SESSION['MM_Nivel'] < 2) {
            http_response_code(403);
            echo 'Acesso negado';
            exit;
        }

        $api_key = ''; // Set your currencyapi.com API key here. Obtain a free key from https://currencyapi.com and configure securely (e.g., via environment variables).
        if (empty($api_key)) {
            http_response_code(500);
            echo 'API key not configured';
            exit;
        }

        $url = "https://api.currencyapi.com/v3/latest?apikey=" . $api_key . "&currencies=BRL%2CCNY";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            http_response_code(500);
            echo 'cURL Error: ' . $err;
            exit;
        }

        $data = json_decode($response, true);

        if (!isset($data['data']['BRL']) || !isset($data['data']['CNY'])) {
            http_response_code(500);
            echo 'Invalid API response';
            exit;
        }

        $dolar = $data['data']['BRL']['value'];
        $yuan = $data['data']['BRL']['value'] / $data['data']['CNY']['value'];

        $latest_var = $this->getLatestVar();
        if (!$latest_var) {
            http_response_code(500);
            echo 'No variable record found';
            exit;
        }

        $id = $latest_var['id'];

        $sql = "UPDATE mak.Vars SET Dolar = :dolar, Yuan = :yuan WHERE id = :id";
        $query = DB::query(Database::UPDATE, $sql);
        $query->parameters([
            ':dolar' => number_format($dolar, 2, '.', ''),
            ':yuan' => number_format($yuan, 2, '.', ''),
            ':id' => $id
        ]);
        $query->execute('mak');

        echo 'Rates updated successfully';
    }
}