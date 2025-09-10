<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Tickets extends Controller_Website {
    
    public function before() {
        parent::before();
        
        if (!isset($_SESSION['MM_Userid']) || $_SESSION['MM_Nivel'] < 2) {
         //   header('Location: /leads8/');
         //   exit;
         die('not allows or no session');
        }
    }

    public function action_index() {
        // Data padrão: hoje
        $data = $this->request->query('data') ?: date('Y-m-d');
        $status = $this->request->query('status');
        $canal = $this->request->query('canal');
        $evento = $this->request->query('evento');
        $origem = $this->request->query('origem');
        $processo = $this->request->query('processo');
        $situacao = $this->request->query('situacao');
        $limit = $this->request->query('limit') ?: 50;
        $offset = $this->request->query('offset') ?: 0;
        
        // Construir filtros WHERE
        $where = " WHERE DATE(data) = '$data' AND status = 0";
        
        if ($status) {
            $where .= " AND status = '$status'";
        }
        if ($canal) {
            $where .= " AND canal_id = '$canal'";
        }
        if ($evento) {
            $where .= " AND evento_id = '$evento'";
        }
        if ($origem) {
            $where .= " AND origem_id = '$origem'";
        }
        if ($processo) {
            $where .= " AND processo_id = '$processo'";
        }
        if ($situacao) {
            $where .= " AND situacao = '$situacao'";
        }

        // Estatísticas gerais
        $stats = $this->getGeneralStats($where);
        
        // Tickets do dia
        $tickets = $this->getTickets($where, $limit, $offset);

        $response = [
            'page_title' => 'Dashboard de Tickets',
            'stats' => $stats,
            'tickets' => $tickets['data'],
            'ticketsPagination' => $tickets['pagination'],
            'filtros' => [
                'data' => $data,
                'status' => $status,
                'canal' => $canal,
                'evento' => $evento,
                'origem' => $origem,
                'processo' => $processo,
                'situacao' => $situacao,
                'limit' => $limit,
                'offset' => $offset
            ]
        ];

        return $this->render('tickets/index', $response);
    }

    private function getGeneralStats($where) {
        $sql = "
            SELECT 
                COUNT(*) as total_tickets,
                COUNT(DISTINCT cliente_id) as total_clientes,
                COUNT(DISTINCT user_id) as total_usuarios,
                COUNT(CASE WHEN consulta_id IS NOT NULL THEN 1 END) as total_consultas,
                COUNT(CASE WHEN pedido_id IS NOT NULL THEN 1 END) as total_pedidos,
                COUNT(CASE WHEN status = 'Aberto' THEN 1 END) as tickets_abertos,
                COUNT(CASE WHEN status = 'Fechado' THEN 1 END) as tickets_fechados,
                COUNT(CASE WHEN data_retorno IS NOT NULL THEN 1 END) as tickets_com_retorno
            FROM mak.tickets_hoje
            $where
        ";

        $query = DB::query(Database::SELECT, $sql);
        return $query->execute('mak')->as_array()[0];
    }

    private function getTickets($where, $limit, $offset) {
        $sql = "
            SELECT 
                id,
                timestamp,
                DATE(data) as data,
                TIME(data) as hora,
                DATE(data_retorno) as data_retorno,
                TIME(data_retorno) as hora_retorno,
                cliente_id,
                user_id,
                contato_id,
                origem_id,
                status_id,
                evento_id,
                canal_id,
                processo_id,
                consulta_id,
                pedido_id,
                assunto,
                detalhes,
                status,
                usuario_nome,
                cliente_nome,
                canal,
                evento,
                origem,
                processo,
                situacao
            FROM mak.tickets_hoje
            $where
            ORDER BY data DESC, hora DESC
            LIMIT $limit OFFSET $offset
        ";

        // Contar total de registros para paginação
        $countSql = "
            SELECT COUNT(*) as total
            FROM mak.tickets_hoje
            $where
        ";

        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute('mak')->as_array();
        
        // Executar consulta de contagem
        $countQuery = DB::query(Database::SELECT, $countSql);
        $countResult = $countQuery->execute('mak')->as_array();
        $totalRecords = $countResult[0]['total'];
        
        return [
            'data' => $result,
            'pagination' => [
                'total' => $totalRecords,
                'limit' => $limit,
                'offset' => $offset,
                'current_page' => $totalRecords > 0 ? floor($offset / $limit) + 1 : 1,
                'total_pages' => max(1, ceil($totalRecords / $limit)),
                'has_previous' => $offset > 0,
                'has_next' => ($offset + $limit) < $totalRecords
            ]
        ];
    }

    public function action_edit() {
        $id = $this->request->param('id');
        
        if ($this->request->method() === Request::POST) {
            $this->updateTicket($id);
            header('Location: /leads8/tickets/');
            exit;
        }

        $ticket = $this->getTicketById($id);
        if (!$ticket) {
            header('Location: /leads8/tickets/');
            exit;
        }

        $response = [
            'ticket' => $ticket,
            'page_title' => 'Editar Ticket'
        ];
        
        return $this->render('tickets/edit', $response);
    }

    private function getTicketById($id) {
        $sql = "SELECT * FROM mak.tickets_hoje WHERE id = :id";
        $query = DB::query(Database::SELECT, $sql);
        $query->parameters([':id' => $id]);
        $result = $query->execute('mak')->as_array();
        return count($result) ? $result[0] : null;
    }

    private function updateTicket($id) {
        $data = $this->request->post();
        
        $sql = "UPDATE crm.chamadas SET 
                data_retorno = :data_retorno,
                status_id = :status_id,
                status = :status,
                detalhes = :detalhes
                WHERE id = :id";

        $query = DB::query(Database::UPDATE, $sql);
        $query->parameters([
            ':data_retorno' => $data['data_retorno'],
            ':status_id' => $data['status_id'],
            ':status' => $data['status'],
            ':detalhes' => $data['detalhes'],
            ':id' => $id
        ]);
        $query->execute('mak');
    }

    public function action_delete() {
        $id = $this->request->param('id');
        $this->deleteTicket($id);
        header('Location: /leads8/tickets/');
        exit;
    }

    private function deleteTicket($id) {
        $sql = "DELETE FROM crm.chamadas WHERE id = :id";
        $query = DB::query(Database::DELETE, $sql);
        $query->parameters([':id' => $id]);
        $query->execute('mak');
    }

    public function action_updateStatus() {
        if ($this->request->method() !== Request::POST) {
            $this->response->status(405);
            return $this->response->body(json_encode(['success' => false, 'error' => 'Method not allowed']));
        }
        
        $id = $this->request->post('id');
        
        if (!$id) {
            $this->response->status(400);
            return $this->response->body(json_encode(['success' => false, 'error' => 'ID is required']));
        }
        
        try {
            $sql = "UPDATE crm.chamadas SET status = 1 WHERE id = :id";
            $query = DB::query(Database::UPDATE, $sql);
            $query->parameters([':id' => $id]);
            $query->execute('mak');
            
            
            return $this->response->body(json_encode(['success' => true]));
        } catch (Exception $e) {
            $this->response->status(500);
            return $this->response->body(json_encode(['success' => false, 'error' => 'Database error']));
        }
    }
}
