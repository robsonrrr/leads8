<?php
class Controller_Ticket_Edit extends Controller_Website {

    public function action_index()
    {
        $customer = $this->request->param('id');

        if ( ! $customer )
        {
            die( s('<h3 align="center" style="margin-top:100px">Sem $customer</h3>'));
        }

        $where = null;

        if ( $customer )
        {
            $where.=sprintf(" AND chamadas.cliente_id = %s", $customer);
        }

        $sql= sprintf(" SELECT *
                        FROM chamadas 
                        WHERE 1=1
                        %s
                        ORDER BY chamadas.id DESC
                        LIMIT 10", $where); 

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        //$profile = View::factory('profiler/stats');
        //s($response);
        //die();

        $count = 0;

        foreach ( $response as $k => $v )
        {
            $count++;
            $response[$k]['count'] = $count;
        }

        $array['response'] = $response;

        $template = $this->render( 'ticket/edit', $array );
    }

}