<?php
class Controller_Order_Done extends Controller_Website {

    public function before()
    { 
        parent::before();
    }

    public function action_index()
    {
        $orderID = $this->request->param('id');

        if ( ! $orderID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $orderID</h3>'));

        $json = Request::factory( '/order/build/'.$orderID )
            ->method('GET')
            ->execute()
            ->body();

        $response = json_decode($json,true);

        //s($response);
        //die();
        //echo $profile = View::factory('profiler/stats');

        return $this->render( 'order/done', $response );
    }

}