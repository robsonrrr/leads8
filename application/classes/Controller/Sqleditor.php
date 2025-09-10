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
        error_log('SQLEditor Controller - action_index() called');
        
        echo '<h1>🗄️ Editor de Views SQL - FUNCIONANDO!</h1>';
        echo '<p>Controller está funcionando corretamente.</p>';
        
        $views_directory = APPPATH . 'config';
        $sql_files = $this->get_sql_files($views_directory);
        
        echo '<h2>Arquivos SQL encontrados:</h2>';
        echo '<ul>';
        foreach ($sql_files as $file) {
            echo '<li><a href="/leads8/sqleditor/edit/' . $file['filename'] . '">' . $file['filename'] . '</a></li>';
        }
        echo '</ul>';
        
        return;
    }

    public function action_edit()
    {
        $filename = $this->request->param('id');
        
        if (!$filename) {
            echo '<h1>❌ Erro</h1><p>Nome do arquivo não fornecido</p>';
            return;
        }
        
        $file_path = APPPATH . 'config/' . $filename;
        
        if (!file_exists($file_path)) {
            echo '<h1>❌ Erro</h1><p>Arquivo não encontrado: ' . $file_path . '</p>';
            return;
        }
        
        if (pathinfo($file_path, PATHINFO_EXTENSION) !== 'sql') {
            echo '<h1>❌ Erro</h1><p>Apenas arquivos SQL são permitidos</p>';
            return;
        }
        
        $content = file_get_contents($file_path);
        
        echo '<h1>✏️ Editando: ' . $filename . '</h1>';
        echo '<form method="POST" action="/leads8/sqleditor/save">';
        echo '<input type="hidden" name="filename" value="' . $filename . '">';
        echo '<textarea name="content" style="width:100%;height:400px;font-family:monospace;">' . htmlspecialchars($content) . '</textarea><br>';
        echo '<input type="submit" value="Salvar Arquivo" style="padding:10px;margin:5px;background:#007cba;color:white;border:none;border-radius:4px;">';
        echo '<input type="submit" name="apply_changes" value="Salvar e Aplicar View" style="padding:10px;margin:5px;background:#28a745;color:white;border:none;border-radius:4px;">';
        echo '</form>';
        
        return;
    }

    public function action_save()
    {
        if ($this->request->method() !== Request::POST) {
            echo '<h1>❌ Erro</h1><p>Método não permitido</p>';
            return;
        }
        
        $filename = $this->request->post('filename');
        $content = $this->request->post('content');
        $apply_changes = $this->request->post('apply_changes');
        
        if (!$filename || !$content) {
            echo '<h1>❌ Erro</h1><p>Dados insuficientes</p>';
            return;
        }
        
        $file_path = APPPATH . 'config/' . $filename;
        
        try {
            file_put_contents($file_path, $content);
            echo '<h1>✅ Sucesso</h1><p>Arquivo salvo: ' . $filename . '</p>';
            
            if ($apply_changes) {
                $result = $this->apply_view_changes($content);
                if ($result['view_applied']) {
                    echo '<p>✅ View aplicada no banco: ' . $result['view_name'] . '</p>';
                } else {
                    echo '<p>❌ Erro ao aplicar view: ' . $result['message'] . '</p>';
                }
            }
            
            echo '<p><a href="/leads8/sqleditor">← Voltar à lista</a></p>';
            
        } catch (Exception $e) {
            echo '<h1>❌ Erro</h1><p>Erro ao salvar: ' . $e->getMessage() . '</p>';
        }
        
        return;
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
}
