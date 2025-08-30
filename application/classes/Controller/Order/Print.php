<?php
class Controller_Order_Print extends Controller_Website {

    public function before()
    { 
        parent::before();
    }

    public function action_index()
    {
        $orderID = $this->request->param('id');

        if ( ! $orderID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $orderID</h3>'));

        
        $url = 'https://api-vallery-test.vallery.com.br/v1/Pedidos/'.$orderID;

        $json = Request::factory( $url )
            ->method('GET')
            ->execute()
            ->body();

        $response = json_decode($json,true);

        //$profile = View::factory('profiler/stats');
        //d($lead);

        //$template = $this->render( 'mustache/home', $lead );
        return $this->render( 'order/print', $response );
    }
}