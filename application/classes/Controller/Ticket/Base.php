<?php
class Controller_Ticket_Base extends Controller_Website {


    public function crm_evento()
    {
        $sql= sprintf(" SELECT eventos.id, eventos.nome,
                        status.nome as statusNoem, processos.nome as processoNome
                        FROM crm.eventos 
                        LEFT JOIN crm.processos on (processos.id=eventos.processo)
                        LEFT JOIN crm.status  on (status.id=eventos.status)
                        WHERE 1=1
                        ORDER BY eventos.nome"); 

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        return $response;
    }

    public function crm_canal()
    {
        $sql= sprintf(" SELECT id, nome, descricao
                        FROM crm.canais 
                        WHERE 1=1
                        AND status = 1
                        ORDER BY nome"); 

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        return $response;
    }

    public function crm_origem()
    {
        $sql= sprintf(" SELECT id, nome, detalhe
                        FROM crm.origem 
                        WHERE 1=1
                        AND status = 1
                        ORDER BY nome"); 

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        return $response;
    }

    public function crm_status()
    {
        $sql= sprintf(" SELECT id, nome, detalhe
                        FROM crm.status 
                        WHERE 1=1
                        AND status = 1
                        ORDER BY nome"); 

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        return $response;
    }

    public function crm_processo()
    {
        $sql= sprintf(" SELECT id, nome, detalhe
                        FROM crm.processos 
                        WHERE 1=1
                        ORDER BY id"); 

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        return $response;
    }



}