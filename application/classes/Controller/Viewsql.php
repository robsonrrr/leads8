<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Viewsql extends Controller_Website
{
    public function before()
    {
        parent::before();
        error_log('SQLEditor Controller - before() called');
    }

    public function action_index()
    {
        error_log('ViewSQL Controller - action_index() called');
        
        $views_directory = APPPATH . 'config';
        $sql_files = $this->get_sql_files($views_directory);
        
        $response = [
            'page_title' => 'Editor de Views SQL',
            'sql_files' => $sql_files,
            'views_directory' => $views_directory,
            'total_files' => count($sql_files)
        ];
        
        error_log('ViewSQL Controller - About to render view with ' . count($sql_files) . ' files');
        
        return $this->render('viewsql/index', $response);
    }

    public function action_edit()
    {
        $filename = $this->request->param('id');
        
        if (!$filename) {
            HTTP::redirect('viewsql');
        }
        
        $file_path = APPPATH . 'config/' . $filename;
        
        // Verificar se o arquivo existe
        if (!file_exists($file_path)) {
            throw new HTTP_Exception_404('Arquivo não encontrado');
        }
        
        // Verificar se é um arquivo SQL
        if (pathinfo($file_path, PATHINFO_EXTENSION) !== 'sql') {
            throw new HTTP_Exception_400('Apenas arquivos SQL são permitidos');
        }
        
        $content = file_get_contents($file_path);
        $view_name = $this->extract_view_name($content);
        
        $response = [
            'page_title' => 'Editando: ' . $filename,
            'filename' => $filename,
            'content' => $content,
            'view_name' => $view_name,
            'file_path' => $file_path,
            'file_size' => filesize($file_path),
            'file_size_formatted' => $this->format_bytes(filesize($file_path)),
            'last_modified' => date('d/m/Y H:i:s', filemtime($file_path))
        ];
        
        return $this->render('viewsql/edit', $response);
    }

    public function action_save()
    {
        // Limpar qualquer output anterior
        if (ob_get_level()) {
            ob_clean();
        }
        
        if ($this->request->method() !== Request::POST) {
            $response = [
                'success' => false,
                'message' => 'Método não permitido'
            ];
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode($response));
            return;
        }
        
        $filename = $this->request->post('filename');
        $content = $this->request->post('content');
        $apply_changes = $this->request->post('apply_changes') === '1';
        
        if (!$filename || !$content) {
            $response = [
                'success' => false,
                'message' => 'Dados insuficientes'
            ];
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode($response));
            return;
        }
        
        $file_path = APPPATH . 'config/' . $filename;
        
        try {
            file_put_contents($file_path, $content);
            
            $response = [
                'success' => true,
                'message' => 'Arquivo salvo com sucesso!',
                'file_saved' => true,
                'filename' => $filename
            ];
            
            if ($apply_changes) {
                $result = $this->apply_view_changes($content);
                $response = array_merge($response, $result);
            }
            
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Erro ao salvar arquivo: ' . $e->getMessage(),
                'file_saved' => false
            ];
        }
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode($response));
        return;
    }

    public function action_test()
    {
        // Limpar qualquer output anterior
        if (ob_get_level()) {
            ob_clean();
        }
        
        try {
            // Log para debug
            error_log('ViewSQL test action called');
            
            if ($this->request->method() !== Request::POST) {
                $response = [
                    'success' => false,
                    'message' => 'Método não permitido'
                ];
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode($response));
                return;
            }
            
            $sql_content = $this->request->post('content');
            error_log('SQL content received: ' . substr($sql_content, 0, 100));
            
            if (!$sql_content) {
                $response = [
                    'success' => false,
                    'message' => 'Conteúdo SQL vazio'
                ];
            } else {
                $response = $this->test_sql_syntax($sql_content);
            }
            
            error_log('Test response: ' . json_encode($response));
            
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode($response));
            return;
            
        } catch (Exception $e) {
            error_log('Error in action_test: ' . $e->getMessage());
            $response = [
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode($response));
            return;
        }
    }

    private function test_sql_syntax($sql_content)
    {
        try {
            error_log('Testing SQL syntax...');
            
            $view_name = $this->extract_view_name($sql_content);
            error_log('Extracted view name: ' . $view_name);
            
            if (!$view_name) {
                return [
                    'success' => false,
                    'message' => 'Nome da view não encontrado no SQL. Certifique-se que o SQL começa com "CREATE VIEW nome_da_view AS"'
                ];
            }
            
            // Validação básica de sintaxe SQL
            $sql_lower = strtolower(trim($sql_content));
            
            // Verificar se é uma declaração CREATE VIEW válida
            if (!preg_match('/^\s*create\s+view\s+/i', $sql_lower)) {
                return [
                    'success' => false,
                    'message' => 'O SQL deve começar com "CREATE VIEW"'
                ];
            }
            
            // Verificar se tem AS
            if (!preg_match('/\s+as\s+/i', $sql_content)) {
                return [
                    'success' => false,
                    'message' => 'Sintaxe inválida: faltando palavra-chave "AS" após o nome da view'
                ];
            }
            
            // Verificar se tem SELECT
            if (!preg_match('/\bselect\b/i', $sql_content)) {
                return [
                    'success' => false,
                    'message' => 'Sintaxe inválida: faltando comando "SELECT" na definição da view'
                ];
            }
            
            // Contagem básica de parênteses
            $open_parens = substr_count($sql_content, '(');
            $close_parens = substr_count($sql_content, ')');
            if ($open_parens !== $close_parens) {
                return [
                    'success' => false,
                    'message' => 'Sintaxe inválida: parênteses não balanceados'
                ];
            }
            
            error_log('Basic syntax validation passed');
            
            return [
                'success' => true,
                'message' => 'Sintaxe SQL aparenta estar válida!',
                'view_name' => $view_name,
                'note' => 'Validação básica realizada. Teste completo será feito ao aplicar a view.'
            ];
            
        } catch (Exception $e) {
            error_log('Error in test_sql_syntax: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro na validação: ' . $e->getMessage()
            ];
        }
    }

    private function get_sql_files($directory)
    {
        $files = [];
        
        if (is_dir($directory)) {
            $iterator = new DirectoryIterator($directory);
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'sql') {
                    $files[] = [
                        'filename' => $file->getFilename(),
                        'path' => $file->getPathname(),
                        'size' => $file->getSize(),
                        'modified' => $file->getMTime(),
                        'modified_formatted' => date('d/m/Y H:i:s', $file->getMTime())
                    ];
                }
            }
        }
        
        usort($files, function($a, $b) {
            return strcmp($a['filename'], $b['filename']);
        });
        
        return $files;
    }

    private function apply_view_changes($sql_content)
    {
        try {
            $view_name = $this->extract_view_name($sql_content);
            
            if (!$view_name) {
                throw new Exception('Nome da view não encontrado no SQL');
            }
            
            $db = Database::instance();
            
            // Remover view se existir
            $drop_sql = "DROP VIEW IF EXISTS `{$view_name}`";
            $db->query(Database::DELETE, $drop_sql);
            
            // Criar nova view
            $db->query(Database::INSERT, $sql_content);
            
            return [
                'view_applied' => true,
                'view_name' => $view_name,
                'message' => 'View aplicada com sucesso!'
            ];
            
        } catch (Exception $e) {
            return [
                'view_applied' => false,
                'message' => 'Erro ao aplicar view: ' . $e->getMessage()
            ];
        }
    }

    private function extract_view_name($sql_content)
    {
        if (preg_match('/CREATE\s+VIEW\s+`?([a-zA-Z0-9_]+)`?\s+AS/i', $sql_content, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function format_bytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($size, 1024);
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }
}
