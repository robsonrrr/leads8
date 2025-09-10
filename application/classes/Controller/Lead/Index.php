<?php
class Controller_Lead_Index extends Controller_Website {

    public function before()
    {
        parent::before();
    }

    public function after()
    {
        parent::after();
    }

    public function action_index()
    {
        $leadID    = $this->request->param('id');
        $page      = $this->request->query('page');
        $segmentID = $this->request->param('segment');

        if ( ! $leadID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $leadID</h3>'));


        if ( ! isset($_SESSION['check']) )
        {
            $_SESSION['check'] = true;
        }
        
        $detect = new Mobile_Detect;
        //s($detect->isMobile() );
        // Exclude tablets.
        if( ( $detect->isMobile() or $detect->isTablet() ) and 5 == $segmentID )
        {
           $protocol = isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) ? $_SERVER["HTTP_X_FORWARDED_PROTO"] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
           $host = isset($_SERVER["HTTP_X_FORWARDED_HOST"]) ? $_SERVER["HTTP_X_FORWARDED_HOST"] : $_SERVER['HTTP_HOST'];
           return $this->redirect( $protocol.'://'.$host.'/leads/mobile/'.$leadID.'/'.$segmentID, 302 );  
        }
        
        if ( 1 != $segmentID and 3 != $segmentID )
        {
          $protocol = isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) ? $_SERVER["HTTP_X_FORWARDED_PROTO"] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
          $host = isset($_SERVER["HTTP_X_FORWARDED_HOST"]) ? $_SERVER["HTTP_X_FORWARDED_HOST"] : $_SERVER['HTTP_HOST'];
          return $this->redirect( $protocol.'://'.$host.'/leads/leads4/'.$leadID.'/'.$segmentID, 302 );   
        }

        $data = array( 
            'check'   => $_SESSION['check'] ,
            'page'    => $page,
            'segment' => $segmentID
        );

        // Build the URL with segment parameter if it exists
        $buildUrl = '/lead/build/'.$leadID;
        if ($segmentID !== null) {
            $buildUrl .= '/'.$segmentID;
        }

        $json = Request::factory( $buildUrl )
            ->method('GET')
            ->query($data)
            ->execute()
            ->body();

        $response = json_decode($json,true);

        // s($response);
        // die();

        if ($response === null) {
            $response = array();
        }

        $response['today']   = date('Y-m-d');
        $response['query']   = $this->request->query('query');
        $response['leadID']  = $leadID;

        $alert = $this->request->query('alert');

        if ( isset($alert))
        {
            $response['alert'] = $this->request->query('alert');
            $response['alert_'.$this->request->query('alert')] = true;
            $response['alert_title']   = $this->request->query('title');
            $response['alert_message'] = $this->request->query('message');
        }

        // $profile = View::factory('profiler/stats');
        if ( ($_SESSION['MM_Userid'] == 1 or $_SESSION['MM_Userid'] == 84) and isset($_GET['bench'])  )
        {
            echo( '<h1 align="center" style="margin-top:100px">Liberado para 1 e 84</h1>');
            d($response);
            echo View::factory('profiler/stats');
            exit;
        }
        // echo View::factory('profiler/stats') ;
        // die();

        //d($_SESSION);

        return $this->render( 'index', $response );
    }

}