<?php
class Controller_Dashboard extends Controller_Website {

    public function before()
    {
        parent::before();
    }

    public function action_index()
    {
        s($_SERVER,$_SESSION, $_ENV);
    }

    public function action_index_old()
    {
        $leadID = $this->request->param('id');

        if ($_SESSION['MM_Nivel'] < 4)
        {
            //die('<h1>Acesso não autorizado</h1>');
        }

        if ( ! $leadID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $leadID</h3>'));

        $json = Request::factory( '/lead/build/'.$leadID )
            ->method('GET')
            ->execute()
            ->body();

        $response = json_decode($json,true);

        //s($response);

        $response['today']  = date('Y-m-d');
        $response['query']  = $this->request->query('q');
        $response['leadID'] = $leadID;
        $response['Lead']['Financeiro'] = self::pending($response['Lead']['Cliente']['id']);

        $profile = View::factory('profiler/stats');
        //d($_SESSION);

        return $this->render( 'dashboard', $response );
    }

}