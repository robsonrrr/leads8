<?php

class Controller_Lead_Cart extends Controller_Website {

    public function before()
    {
        parent::before();
    }

    public function action_index()
    {
        $leadID    = $this->request->param('id');
        $segmentID = $this->request->param('segment');

        if ( ! $leadID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $leadID</h3>'));

        $data = array( 
            'check'   => true,
            'segment' => $segmentID
        );

        $json = Request::factory( '/lead/build/'.$leadID )
            ->method('GET')
            ->query($data)
            ->execute()
            ->body();

        $lead = json_decode($json,true);
        
        //s($lead['Lead']['segmento']);
        //die();

        //$template = $this->render( 'mustache/home', $lead );
        $template = $this->render( 'cart/'.$lead['Lead']['segmento'] , $lead['Lead'] );
    }

}
