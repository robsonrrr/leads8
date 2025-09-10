<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Website extends Controller_Session  {

    protected $baseUrl;

    public function before()
    {
        parent::before();

        // Redirect to leads8 if Userid is 11111111
        // if (isset($_SESSION['MM_Userid']) && $_SESSION['MM_Userid'] == 1) {
        //     // Get the dynamic number from the current request
        //     $dynamicNumber = $this->request->param('id');
        //     if ($dynamicNumber) {
        //         $redirectUrl = "https://dev.office.internut.com.br/leads8/lead/index/{$dynamicNumber}/1";
        //         $this->redirect($redirectUrl);
        //     }
        // }

        //echo self::get_date_new();

        //if( !isset( $_SESSION['Authorization'] ))
            //self::api_login();

        //s($_SERVER);

        $this->baseUrl = 'http://office.vallery.com.br/leads/leads5/';
    }

    public function menu($title)
    {
        return $response = Request::factory('http://vallery-menu/index/index/'.$title )
            ->method('POST')
            ->post($_SESSION)
            ->execute()
            ->body();
    }

    public function bench($array = [])
    {

        // $profile = View::factory('profiler/stats');
        if ( ($_SESSION['MM_Userid'] == 1 or $_SESSION['MM_Userid'] == 84) and isset($_GET['bench'])  )
        {
            echo( '<h1 align="center" style="margin-top:100px">Liberado para 1 e 84</h1>');
            d($array);
            echo View::factory('profiler/stats');
            exit;
        }
    }

    public function get_date_new()
    {
        return date('Y-m-d G:i:s');
    }


    public function config_mail($response)
    {
        // Informações comuns a todos os segmentos
        $commonInfo = [
            'title' => '',
            'color' => '',
            'mainColorText' => 'white',
            'logo' => $_ENV['cdn'] . "media/rolemak-branco.png"
        ];

        switch ($response['Lead']['segmentoPOID']) {
            case 1: // Máquinas
                $mailInfo = [
                    'title' => "Zoje Máquina de Costura Industrial",
                    'color' => "#00a353",
                    'mainColor' => "#22c55e",
                    'topTableImg' => 'top-maquinas.png',
                    'bottomTableImg' => 'bottom-maquinas.png'
                ];
                break;
            case 2: // Rolamentos
                $mailInfo = [
                    'title' => "Rolemak Rolamentos em Geral",
                    'color' => "#ef8200",
                    'mainColor' => "#f59e0b",
                    'topTableImg' => 'top-rolamentos.png',
                    'bottomTableImg' => 'bottom-rolamentos.png'
                ];
                break;
            case 3: // Peças
                $mailInfo = [
                    'title' => "Rolemak Peças e Acessórios",
                    'color' => "#04a1cd",
                    'mainColor' => "#0ea5e9",
                    'topTableImg' => 'top-pecas.png',
                    'bottomTableImg' => 'bottom-pecas.png'
                ];
                break;
            case 4: // Metais
                $mailInfo = [
                    'title' => "Mak Metais",
                    'color' => "#408ab7",
                    'mainColor' => "#0891b2",
                    'topTableImg' => 'top-metais.png',
                    'bottomTableImg' => 'bottom-metais.png'
                ];
                break;
            case 5: // Autopeças
                $mailInfo = [
                    'title' => "Mak Automotive",
                    'color' => "#fdb812",
                    'mainColor' => "#fde047",
                    'mainColorText' => "#713f12",
                    'topTableImg' => 'top-autopecas.png',
                    'bottomTableImg' => 'bottom-autopecas.png'
                ];
                break;
            case 6: // Moto
                $mailInfo = [
                    'title' => "PPK Motoparts",
                    'color' => "#db1414",
                    'mainColor' => "#ff2121",
                    'topTableImg' => 'top-motopecas.png',
                    'bottomTableImg' => 'bottom-motopecas.png',
                    'logo' => $_ENV['cdn'] . "media/r6/logotipo-ppk-motoparts.png",
                ];
                break;
            case 'boakostura':
                $mailInfo = [
                    'title' => "BoaKostura",
                    'color' => "#00cc50",
                    'logo' => "https://www.marketformation.com.br/cdn/svg/logotipo/boakostura.svg",
                ];
                break;
            default:
                $mailInfo = [];
                break;
        }

        // Combinar informações comuns e específicas do segmento dentro do índice 'Mail'
        $response['Mail'] = array_merge($commonInfo, $mailInfo);

        return $response;
    }

    public function bank ( $segmento, $emitente = false )
    {
        //return array();

        switch ( $segmento )
        {
            case "1":
                $url = $_ENV['cdn'].'template/dados-bancos-rolemak.mustache';
                break;
            case "2":
                $url = $_ENV['cdn'].'template/dados-bancos-matriz.mustache';
                break;
            case "3":
                $url = $_ENV['cdn'].'template/dados-bancos-rolemak.mustache';
                break;
            case "4":
                $url = $_ENV['cdn'].'template/dados-bancos-rolemak.mustache';
                break;
            case "5":
                $url = $_ENV['cdn'].'template/dados-bancos-rolemak.mustache';
                break;
            case "6":
                $url = $_ENV['cdn'].'template/dados-bancos-matriz.mustache';
                break;
        }

        switch ( $emitente )
        {
            case "1":
                $url = $_ENV['cdn'].'template/dados-bancarios/Matriz-SP.mustache';
                break;
            case "3":
                $url = $_ENV['cdn'].'template/dados-bancarios/Loja-SP.mustache';
                break;
            case "6":
                $url = $_ENV['cdn'].'template/dados-bancarios/Blumenau-SC.mustache';
                break;
            case "8":
                $url = $_ENV['cdn'].'template/dados-bancarios/Barra_Funda-SP.mustache';
                break;
        }

        $html = Request::factory( $url )
            ->method('GET')
            ->execute()
            ->body();

        return $html;
    }

    public function logSlack( $msg )
    {
        $url = "https://slack.com/api/chat.postMessage";

        $data = array(
            "token"    => $_ENV['SLACK_BOT_TOKEN'] ?? 'your-slack-token-here', //rolemak-bot
            "channel"  => $_ENV['SLACK_CHANNEL'] ?? 'CJ9EZH13M',
            "text"     => $msg,
            "username" => "rolemak-bot",
        );

        $slack = Request::factory($url)
            ->method(Request::POST)
            ->post($data)
            ->execute()
            ->body();

        //s($slack);

        return $slack;
    }

    public function sendMailService( $fields )
    {
        //SEND EMAIL VIA WEBSERVICE


        $email = Request::factory( $_ENV['sendmail'] )
            ->method(Request::POST)
            ->post($fields)
            ->execute()
            ->body();

        //s($email);

        return $email;
    }

    public function logTwilio( $to, $from, $message)
    {
        //$to      = '5511995206263';
        //$from    = '14155238886';
        //$message = sprintf('Your Yummy Cupcakes Company order of 1 dozen frosted cupcakes has shipped and should be delivered on July 10, 2019');

        $account = $_ENV['TWILIO_ACCOUNT_SID'] ?? 'your-twilio-account-sid';
        $token   = $_ENV['TWILIO_AUTH_TOKEN'] ?? 'your-twilio-auth-token';

        $url     = sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', $account);

        $run = sprintf( 'curl "%s"  -X POST --data-urlencode "To=whatsapp:+%s" --data-urlencode "From=whatsapp:+%s" --data-urlencode "Body=%s" -u %s:%s ', $url, $to, $from, $message, $account, $token);
        $run.= " > /dev/null 2>&1 &";

        exec($run);

        return true;
    }

    public function log( $TableName, $RecordName, $PrimaryRecordName, $PrimaryRecordId, $PreviousContent, $NewContent)
    {
        $date = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $sql = sprintf("INSERT INTO `history`.`jeditable`
                                           ( `TableName`, `RecordName`, `PrimaryRecordName`, `PrimaryRecordId`, `PreviousContent`, `NewContent`, UserId, Datetime)
                                    VALUES ('%s','%s','%s','%s','%s','%s','%s','%s');",
                       $TableName, $RecordName, $PrimaryRecordName, $PrimaryRecordId, $PreviousContent, trim($NewContent), $_SESSION["MM_Userid"], $date);

        $result = $this->action_connect_INSERT($sql);

        return $result;
    }

    public function clean_string( $string, $separator = '-' )
    {
        $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
        $special_cases = array( '&' => 'and', "'" => '');
        $string = mb_strtolower( trim( $string ), 'UTF-8' );
        $string = str_replace( array_keys($special_cases), array_values( $special_cases), $string );
        $string = preg_replace( $accents_regex, '$1', htmlentities( $string, ENT_QUOTES, 'UTF-8' ) );
        $string = preg_replace("/[^a-z0-9]/u", "$separator", $string);
        $string = preg_replace("/[$separator]+/u", "$separator", $string);
        return $string;
    }

    public function acesso_geral( $array )
    {
        $array = $array;

        //acessoDiretoria
        if ( $_SESSION['MM_Nivel'] > 4 )
            $array['SESSION']['acessoDiretoria'] = true;

        //acessoGerencial
        if ( $_SESSION['MM_Nivel'] > 3 or $_SESSION['MM_Depto'] == 'GERENCIA' or $_SESSION['MM_Depto'] == 'PRODUTOS' or $_SESSION['MM_Userid'] == 225 or $_SESSION['MM_Userid'] == 77 or $_SESSION['MM_Userid'] == 73 or $_SESSION['MM_Userid'] == 111 )
            $array['SESSION']['acessoGerencial'] = true;

        //acessoFinanceiro
        if ( $_SESSION['MM_Depto'] == 'FINANCEIRO' or $_SESSION['MM_Nivel'] > 4 or $_SESSION['MM_Userid'] == 225 )
            $array['SESSION']['acessoFinanceiro'] = true;

        //acessoComercial
        if ( $_SESSION['MM_Depto'] == 'VENDAS' or $_SESSION['MM_Nivel'] > 4 or $_SESSION['MM_Userid'] == 225 )
            $array['SESSION']['acessoComercial'] = true;

        //acessoComercial
        if ( $_SESSION['MM_Depto'] == 'WEB' or $_SESSION['MM_Userid'] == 84 or $_SESSION['MM_Userid'] == 225 )
            $array['SESSION']['acessoWebmaster'] = true;

        //acessoRolamentos
        if ( $_SESSION['MM_Segment'] == 'bearings' or $_SESSION['MM_Nivel'] > 4 or $_SESSION['MM_Userid'] == 225)
            $array['SESSION']['acessoRolamentos'] = true;

        //acessoMetais
        if ( $_SESSION['MM_Segment'] == 'faucets' or $_SESSION['MM_Nivel'] > 4 or $_SESSION['MM_Userid'] == 225)
            $array['SESSION']['acessoMetais'] = true;

        //acessoAutopecas
        if ( $_SESSION['MM_Segment'] == 'auto' or $_SESSION['MM_Nivel'] > 4 or $_SESSION['MM_Userid'] == 225 )
            $array['SESSION']['acessoAutopecas'] = true;

        //acessoMaquinas
        if ( $_SESSION['MM_Segment'] == 'machines' or $_SESSION['MM_Nivel'] > 4 or $_SESSION['MM_Userid'] == 225 )
            $array['SESSION']['acessoMaquinas'] = true;

        //acessoPecas
        if ( $_SESSION['MM_Segment'] == 'parts' or $_SESSION['MM_Nivel'] > 4 or $_SESSION['MM_Userid'] == 225 )
            $array['SESSION']['acessoPecas'] = true;

        //acessoMotopecas
        if ( $_SESSION['MM_Segment'] == 'moto' or $_SESSION['MM_Nivel'] > 4 or $_SESSION['MM_Userid'] == 225 )
            $array['SESSION']['acessoMotopecas'] = true;

        if (  $_SESSION['MM_Userid'] == 1 or  $_SESSION['MM_Userid'] == 2 or $_SESSION['MM_Userid'] == 84 )
            $array['SESSION']['acessoBoaKostura'] = true;

        return $array;
    }

    public function render( $view, $array )
    {
        $mustache = new Mustache_Engine(
            array(
                'loader' => new Mustache_Loader_FilesystemLoader( APPPATH.'/views/' ),
            ));

        //s($_ENV,$_SESSION);
        //die();



        $array['VERSION']   = '1.11';
        $array['ENV']       = $_ENV;
        //$array['ENV']['CDN'] = 'http://cdn-test.rolemak.com.br';
        //$array['ENV']['IMG'] = 'http://img-test.rolemak.com.br';
        $array['SESSION']   = $_SESSION;
        $array['CONTAINER'] = '/leads/leads5/';
        $array['BASE']      = "https://office.vallery.com.br/leads/leads5/";

        $array = self::acesso_geral($array);

        //s($array);

        $template = $mustache->render( $view, $array );

        //s($template);

        $this->response->body( $template );
        return $this->response;
    }

    public function action_connect_SELECT($sql)
    {
        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute()->as_array();
        return  $result;
    }

    public function action_connect_INSERT($sql)
    {
        $query = DB::query(Database::INSERT, $sql);
        $result = $query->execute();
        return $result;
    }

    public function action_connect_UPDATE($sql)
    {
        $query = DB::query(Database::UPDATE, $sql);
        $result = $query->execute();
        return $result;
    }

    public function action_connect_DELETE($sql)
    {
        $query = DB::query(Database::DELETE, $sql);
        $result = $query->execute();
        return $result;
    }

    public function formatPrice( $number, $precision='2')
    {
        return number_format( $number, $precision, ',', '.');
    }

    function api_login()
    {
        $json = Request::factory($_ENV["api_vallery_v1"].'/v1/Users/login')
            ->method('POST')
            ->post( array(
                'email' 		=> 'rogeriobbvn@rolemak.com.br',
                'password' 		=> 'er4y5ha7*',
            ))
            ->execute()
            ->body();

        $token = json_decode($json,true);

        $_SESSION['Authorization'] = $token['id'];
    }

}