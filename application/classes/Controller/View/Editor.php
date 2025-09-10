<?php defined('SYSPATH') or die('No direct script access.');

class Controller_View_Editor extends Controller_Website
{
    
    public function before()
    {
        parent::before();
        
        // Debug: Log que o controller foi chamado
        error_log('ViewEditor Controller - before() called');
        
        // Verificar se o usuário tem permissão (adicionar verificação de auth se necessário)
        // if (!Auth::instance()->logged_in('admin')) {
        //     throw new HTTP_Exception_403('Acesso negado');
        // }
    }

    /**
     * Página principal do editor de views
     */
    public function action_index()
    {
        error_log('ViewEditor Controller - action_index() called');
        
        // Teste simples primeiro
        echo '<h1>🗄️ Editor de Views SQL</h1>';
        echo '<p>Controller funcionando! Agora implementando interface completa...</p>';
        
        $views_directory = APPPATH . 'config';
        $sql_files = $this->get_sql_files($views_directory);
        
        echo '<h2>Arquivos SQL encontrados:</h2>';
        echo '<ul>';
        foreach ($sql_files as $file) {
            echo '<li><a href="/leads8/view/editor/edit/' . $file['filename'] . '">' . $file['filename'] . '</a></li>';
        }
        echo '</ul>';
        
        // Não renderizar a view completa ainda para debug
        return;
    }

    /**
     * Editar uma view específica
     */
    public function action_edit()
    {
        $filename = $this->request->param('id');
        
        if (!$filename) {
            HTTP::redirect('view/editor');
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
            'filename' => $filename,
            'content' => $content,
            'view_name' => $view_name,
            'file_path' => $file_path
        ];
        
        return $this->render('view/editor/edit', $response);
    }

    /**
     * Salvar e aplicar mudanças na view
     */
    public function action_save()
    {
        if ($this->request->method() !== Request::POST) {
            throw new HTTP_Exception_405('Método não permitido');
        }
        
        $filename = $this->request->post('filename');
        $content = $this->request->post('content');
        $apply_changes = $this->request->post('apply_changes') === '1';
        
        if (!$filename || !$content) {
            HTTP::redirect('view/editor');
        }
        
        $file_path = APPPATH . 'config/' . $filename;
        
        // Verificar se o arquivo existe
        if (!file_exists($file_path)) {
            throw new HTTP_Exception_404('Arquivo não encontrado');
        }
        
        try {
            // Salvar o arquivo
            file_put_contents($file_path, $content);
            
            $response = [
                'success' => true,
                'message' => 'Arquivo salvo com sucesso!',
                'file_saved' => true
            ];
            
            // Se solicitado, aplicar as mudanças no banco
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
        
        // Se for uma requisição AJAX, retornar JSON
        if ($this->request->is_ajax()) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode($response));
            return;
        }
        
        // Redirecionar com mensagem
        Session::instance()->set('flash_message', $response['message']);
        Session::instance()->set('flash_type', $response['success'] ? 'success' : 'error');
        HTTP::redirect('view/editor/edit/' . $filename);
    }

    /**
     * Aplicar mudanças da view no banco de dados
     */
    private function apply_view_changes($sql_content)
    {
        try {
            $view_name = $this->extract_view_name($sql_content);
            
            if (!$view_name) {
                throw new Exception('Nome da view não encontrado no SQL');
            }
            
            // Iniciar transação
            $db = Database::instance();
            $db->begin();
            
            try {
                // Primeiro, tentar remover a view se existir
                $drop_sql = "DROP VIEW IF EXISTS `{$view_name}`";
                $db->query(Database::DELETE, $drop_sql);
                
                // Criar a nova view
                $db->query(Database::INSERT, $sql_content);
                
                // Confirmar transação
                $db->commit();
                
                return [
                    'view_applied' => true,
                    'view_name' => $view_name,
                    'message' => 'Arquivo salvo e view aplicada com sucesso!'
                ];
                
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            return [
                'view_applied' => false,
                'message' => 'Arquivo salvo, mas erro ao aplicar view: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Testar SQL sem aplicar mudanças
     */
    public function action_test()
    {
        if ($this->request->method() !== Request::POST) {
            throw new HTTP_Exception_405('Método não permitido');
        }
        
        $sql_content = $this->request->post('content');
        
        if (!$sql_content) {
            $response = [
                'success' => false,
                'message' => 'Conteúdo SQL vazio'
            ];
        } else {
            $response = $this->test_sql_syntax($sql_content);
        }
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode($response));
    }

    /**
     * Testar sintaxe SQL
     */
    private function test_sql_syntax($sql_content)
    {
        try {
            $view_name = $this->extract_view_name($sql_content);
            
            if (!$view_name) {
                throw new Exception('Nome da view não encontrado no SQL');
            }
            
            // Criar uma view temporária para testar a sintaxe
            $temp_view_name = $view_name . '_temp_' . time();
            $temp_sql = str_replace("CREATE VIEW {$view_name}", "CREATE VIEW {$temp_view_name}", $sql_content);
            
            $db = Database::instance();
            
            try {
                // Tentar criar a view temporária
                $db->query(Database::INSERT, $temp_sql);
                
                // Se chegou até aqui, a sintaxe está correta
                // Remover a view temporária
                $db->query(Database::DELETE, "DROP VIEW `{$temp_view_name}`");
                
                return [
                    'success' => true,
                    'message' => 'Sintaxe SQL válida!',
                    'view_name' => $view_name
                ];
                
            } catch (Exception $e) {
                // Tentar remover a view temporária em caso de erro
                try {
                    $db->query(Database::DELETE, "DROP VIEW IF EXISTS `{$temp_view_name}`");
                } catch (Exception $cleanup_e) {
                    // Ignorar erros de limpeza
                }
                
                throw $e;
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na sintaxe SQL: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obter lista de arquivos SQL
     */
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
        
        // Ordenar por nome
        usort($files, function($a, $b) {
            return strcmp($a['filename'], $b['filename']);
        });
        
        return $files;
    }

    /**
     * Extrair nome da view do SQL
     */
    private function extract_view_name($sql_content)
    {
        // Regex para encontrar "CREATE VIEW nome_da_view"
        if (preg_match('/CREATE\s+VIEW\s+`?([a-zA-Z0-9_]+)`?\s+AS/i', $sql_content, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Listar views existentes no banco
     */
    public function action_list_views()
    {
        try {
            $db = Database::instance();
            $result = $db->query(Database::SELECT, "SHOW FULL TABLES WHERE Table_type = 'VIEW'");
            $views = $result->as_array();
            
            $response = [
                'success' => true,
                'views' => $views
            ];
            
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Erro ao listar views: ' . $e->getMessage()
            ];
        }
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode($response));
    }

    /**
     * Backup de uma view antes de modificar
     */
    private function backup_view($view_name)
    {
        try {
            $db = Database::instance();
            $result = $db->query(Database::SELECT, "SHOW CREATE VIEW `{$view_name}`");
            $view_definition = $result->as_array();
            
            if (!empty($view_definition)) {
                $backup_content = $view_definition[0]['Create View'];
                $backup_file = APPPATH . 'config/backups/' . $view_name . '_backup_' . date('Y-m-d_H-i-s') . '.sql';
                
                // Criar diretório de backup se não existir
                $backup_dir = dirname($backup_file);
                if (!is_dir($backup_dir)) {
                    mkdir($backup_dir, 0755, true);
                }
                
                file_put_contents($backup_file, $backup_content);
                
                return $backup_file;
            }
            
        } catch (Exception $e) {
            // Log do erro, mas não interromper o processo
            error_log('Erro ao fazer backup da view: ' . $e->getMessage());
        }
        
        return false;
    }
}
