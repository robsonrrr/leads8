<?php
class Controller_Lead_Print extends Controller_Website {

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
            'segment' => $segmentID
        );

        $json = Request::factory( '/lead/build/'.$leadID )
            ->method('GET')
            ->query($data)
            ->execute()
            ->body();

        $response = json_decode($json,true);

        $response['query']  = $this->request->query('q');
        $response['leadID'] = $leadID;

        /* $run = sprintf( " curl --request GET \ 
                          --url 'https://sms.comtele.com.br/api/v2/send?Sender=2323&Receivers=11995206263&Content=teste api referente ao Lead número %s' \
                          --header 'auth-key: 8f4c9c3d-1b51-45a6-a6dd-a9221c6bd05f' ", $leadID);       
        $run.= " > /dev/null 2>&1 &";

        exec($run); */

        //$profile = View::factory('profiler/stats');
        //d($response);

        $response = $this->config_mail( $response );

        //$template = $this->render( 'mustache/home', $lead );
        return $this->render( 'lead/print', $response );
    }
}