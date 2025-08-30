<?php
class Controller_Ticket_New extends Controller_Ticket_Base {

    public function action_index()
    {
        $customer = $this->request->param('id');
        $lead     = $this->request->param('segment');

        if ( ! $customer )
        {
            die( s('<h3 align="center" style="margin-top:100px">Sem $customer</h3>'));
        }
        
        //s($customer,$lead);
        //die();

        $array['cliente_id']  = $customer;
        $array['consulta_id'] = $lead;

        $array['evento']   = $this->crm_evento();
        $array['processo'] = $this->crm_processo();
        $array['status']   = $this->crm_status();
        $array['origem']   = $this->crm_origem();
        $array['canal']    = $this->crm_canal();
        $array['today']    = date('Y-m-d');

        $template = $this->render( 'ticket/new', $array );
    }
}