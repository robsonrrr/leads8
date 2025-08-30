<?php
class Controller_Lead_Total extends Controller_Website {

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

        //$profile = View::factory('profiler/stats');
        //d($lead);
        //die();

        //$template = $this->render( 'mustache/home', $lead );
        $template = $this->render( 'partials/total', $lead['Lead'] );
    }

}