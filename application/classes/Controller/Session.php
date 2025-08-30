<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Session extends Controller  {

    public function before()
    {
        $this->action_session();		
    }

    private function  session_zebra_start()
    {
        $ihost = 'vallery.catmgckfixum.sa-east-1.rds.amazonaws.com';
        $iusername = 'robsonrr';
        $ipassword = 'Best94364811082';
        $idatabase = 'session';
        // try to connect to the MySQL server
        $ilink = mysqli_connect($ihost, $iusername, $ipassword, $idatabase) or die('Could not connect to database!');
        //mysqli_query($link,"SET NAMES 'utf8'");

        //require_once('/project/Zebra_Session/Zebra_Session.php');
        require APPPATH.'classes/Zebra_Session.php';       

        // instantiate the class
        // note that you don't need to call the session_start() function
        // as it is called automatically when the object is instantiated
        // also note that we're passing the database connection link as the first argument
        $session = new Zebra_Session($ilink, 'sEcUr1tY_c0dE', 43200);
        //$session->session_lifetime= 43200;
        //  get default settings
        //print_r('<pre>');
        //print_r($session->get_settings());
    }

    public function action_session()
    {
        strlen(session_id()) or self::session_zebra_start();
        
        if( !isset($_SESSION['MM_Userid']) && isset($_SERVER['HTTP_AUTHORIZATION']) )
        {
            $token = explode('Bearer ', $_SERVER['HTTP_AUTHORIZATION']);
            
            $token = isset($token[1]) ? $token[1] : null;
            
            if($token)
            {
                $json = Request::factory( 'http://login/jwt/session' )
                    ->method('GET')
                    ->headers('Authorization', "Bearer $token")
                    ->execute()
                    ->body();
                $response = json_decode($json,true);
                
                if(isset($response['session']))
                {
                    $_SESSION = $response['session'];
                }
            }
        }
        
        //Session::instance();
        // *** Restrict Access To Page: Grant or deny access to this page
        $FF_authorizedUsers=" ";
        $FF_authFailedURL= "/login/" ;
        $FF_grantAccess=0;

        if (isset($_SESSION["MM_Username"])) {
            if (true || !(isset($_SESSION["MM_UserAuthorization"])) || $_SESSION["MM_UserAuthorization"]=="" || strpos($FF_authorizedUsers, $_SESSION["MM_UserAuthorization"])) {
                $FF_grantAccess = 1;
            }
        }
        if (!$FF_grantAccess and isset($_SERVER['HTTP_X_FORWARDED_PREFIX']) ) {
            $FF_qsChar = "?";
            if (strpos($FF_authFailedURL, "?")) $FF_qsChar = "&";
            $FF_referrer = $_SERVER['PHP_SELF'];
            if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) $FF_referrer .= "?" . $_SERVER['QUERY_STRING'];
            $FF_authFailedURL = $FF_authFailedURL . $FF_qsChar . "accessdenied=" . $_SERVER['HTTP_X_FORWARDED_PREFIX'] . $_SERVER['REQUEST_URI'] ; //urlencode($FF_referrer);
            header("Location: $FF_authFailedURL");
            exit;
        }

        if (isset($_SESSION["thisPage"]) and !isset($_GET['back'])) {
            $_SESSION["lastPage"] = $_SESSION["thisPage"];					
        }

        if( isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING'])>0 ) {
            $querystring = '?' . $_SERVER['QUERY_STRING'] . '&back';
        }else{
            $querystring = '?back';	
        }

        $_SESSION["thisPage"] = $_SERVER['PHP_SELF']. $querystring ;

    }

}