<?php defined('SYSPATH') or die('No direct script access.');

class Controller_VarsSimple extends Controller_Website {
    
    public function before() {
        parent::before();
        
        if (!isset($_SESSION['MM_Userid']) || $_SESSION['MM_Nivel'] < 2) {
            // Check if this is an HTMX request
            if (isset($_SERVER['HTTP_HX_REQUEST'])) {
                $this->response->status(401);
                $this->response->body('Unauthorized');
                return;
            }
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

        $data = [
            'vars' => $vars_page,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total_records,
            'per_page' => $per_page,
            'field_categories' => $this->getFieldCategories()
        ];

        $data = $this->acesso_geral($data);
        return $this->render('vars_simple/index', $data);
    }

    public function action_view() {
        $id = $this->request->param('id');
        $var = $this->getVarById($id);
        
        if (!$var) {
            header('Location: /leads8/vars_simple/');
            exit;
        }

        $array = array();
        $array['var'] = $var;
        $array['page_title'] = 'Visualizar Variável';
        $array['field_categories'] = $this->getFieldCategories();
        
        return $this->render('vars_simple/view', $array);
    }

    public function action_edit_field() {
        $field = $this->request->post('field');
        $id = $this->request->post('id');
        $var = $this->getVarById($id);
        
        if (!$var || !$field) {
            $this->response->status(400);
            return;
        }
        
        $value = isset($var[$field]) ? $var[$field] : '';
        
        $html = '<input type="text" class="form-control form-control-sm" '
              . 'name="value" value="' . htmlspecialchars($value) . '" '
              . 'hx-post="/leads8/vars_simple/update_field" '
              . 'hx-vals=\'{"field": "' . $field . '", "id": "' . $id . '"}\''
              . 'hx-trigger="blur, keyup[key==\"Enter\"]" '
              . 'hx-target="#field-' . $field . '-' . $id . '" '
              . 'hx-swap="outerHTML" '
              . 'autofocus>';
        
        $this->response->headers('Content-Type', 'text/html');
        $this->response->body($html);
        return;
    }

    public function action_update_field() {
        $field = $this->request->post('field');
        $id = $this->request->post('id');
        $value = $this->request->post('value');
        
        if (!$field || !$id) {
            $this->response->status(400);
            return;
        }
        
        // Update the database
        $sql = "UPDATE mak.Vars SET `{$field}` = :value WHERE id = :id";
        $query = DB::query(Database::UPDATE, $sql);
        $query->parameters([':value' => $value, ':id' => $id]);
        $query->execute('mak');
        
        // Return the formatted display value
        $formatted_value = $this->formatValue($field, $value);
        $badge_class = $this->getBadgeClass($field);
        
        $html = '<span id="field-' . $field . '-' . $id . '" '
              . 'class="badge ' . $badge_class . ' editable-field" '
              . 'style="cursor: pointer; font-size: 0.9em;" '
              . 'hx-post="/leads8/vars_simple/edit_field" '
              . 'hx-vals=\'{"field": "' . $field . '", "id": "' . $id . '"}\''
              . 'hx-target="#field-' . $field . '-' . $id . '" '
              . 'hx-swap="outerHTML" '
              . 'title="Clique para editar">' . htmlspecialchars($formatted_value) . '</span>';
        
        $this->response->headers('Content-Type', 'text/html');
        $this->response->body($html);
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

    private function formatValue($field, $value) {
        // Campos monetários
        $money_fields = ['Dolar', 'Yuan', 'PayableExporter', 'PaidExporter', 'ReceivedExporter'];
        $payment_fields = ['PayExporter1', 'PayExporter2', 'PayExporter3', 'PayExporter4', 
                          'PayExporter5', 'PayExporter6', 'PayExporter7', 'PayExporter8',
                          'PayExporter9', 'PayExporter10', 'PayExporter11', 'PayExporter12'];

        if (in_array($field, $money_fields) || in_array($field, $payment_fields)) {
            return 'R$ ' . number_format($value, 2, '.', ',');
        }

        // Campos numéricos simples
        $numeric_fields = ['Cash', 'FixPlus', 'FixLess', 'Buildings'];
        if (in_array($field, $numeric_fields)) {
            return number_format($value, 0, '.', ',');
        }

        return $value;
    }

    private function getBadgeClass($field) {
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
        
        // Workers fields
        $workers_fields = [
            'Workers_Machines' => 'bg-primary',
            'Workers_Bearings' => 'bg-dark', 
            'Workers_Parts' => 'bg-success',
            'Workers_Auto' => 'bg-info'
        ];
        
        if (array_key_exists($field, $workers_fields)) {
            return $workers_fields[$field];
        }
        
        return isset($field_classes[$field]) ? $field_classes[$field] : 'bg-secondary';
    }
}