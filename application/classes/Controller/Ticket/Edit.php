<?php
class Controller_Ticket_Edit extends Controller_Ticket_Base {

    public function action_index()
    {
        $ticket = $this->request->param('id');
        $lead   = $this->request->param('id2');

        die();

        if ( ! $ticket )
        {
            die( s('<h3 align="center" style="margin-top:100px">Sem $ticket</h3>'));
        }

        $where = null;

        if ( $ticket )
        {
            $where.=sprintf(" AND chamadas.id = %s", $ticket);
        }

        $sql= sprintf(" SELECT *
                        FROM crm.chamadas 
                        WHERE 1=1
                        %s
                        ORDER BY chamadas.id DESC", $where); 

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        //$profile = View::factory('profiler/stats');
        d($response);
        //die();

        $count = 0;

        foreach ( $response as $k => $v )
        {
            $count++;
            $response[$k]['count']        = $count;
            $response[$k]['consulta_id']  = $lead;

            $evento_id   = $v['evento_id'];
            $origem_id   = $v['origem_id'];
            $status_id   = $v['status_id'];
            $canal_id    = $v['canal_id'];
            $processo_id = $v['processo_id'];
        }

        $array['response'] = $response;
        $array['evento']   = $this->crm_evento();

        foreach ( $array['evento'] as $k => $v )
        {
            if ( $v['id'] == $evento_id)
            {
                $array['evento'][$k]['selected'] = true;
            }
        }

        $array['canal'] = $this->crm_canal();

        foreach ( $array['canal'] as $k => $v )
        {
            if ( $v['id'] == $canal_id )
            {
                $array['canal'][$k]['selected'] = true;
            }
        }

        $array['origem']   = $this->crm_origem();

        foreach ( $array['origem'] as $k => $v )
        {
            if ( $v['id'] == $origem_id )
            {
                $array['origem'][$k]['selected'] = true;
            }
        }

        $array['status']   = $this->crm_status();

        foreach ( $array['status'] as $k => $v )
        {
            if ( $v['id'] == $status_id )
            {
                $array['status'][$k]['selected'] = true;
            }
        }

        $array['processo'] = $this->crm_processo();

        foreach ( $array['processo'] as $k => $v )
        {
            if ( $v['id'] == $processo_id )
            {
                $array['processo'][$k]['selected'] = true;
            }
        }

        $template = $this->render( 'ticket/edit', $array );
    }



}