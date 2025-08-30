<?php
class Controller_Lead_Aviseme extends Controller_Lead_Base {

    public function before()
    {
        parent::before();
    }

    public function action_index()
    {
        $leadID = $this->request->param('id');

        if ( ! $leadID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $leadID</h3>'));

        if ( ! isset($_SESSION['check']) )
        {
            $_SESSION['check'] = true;
        }

        $data = array( 
            'check' => $_SESSION['check']
        );

        $json = Request::factory( '/lead/build/'.$leadID )
            ->method('GET')
            ->query($data)
            ->execute()
            ->body();
        $response = json_decode($json,true);
        
        //d($response);

        //s($_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST']..$_SERVER['HTTP_X_FORWARDED_PREFIX']  );
        //die();
        
        $response['dadosBancarios'] = $this->bank( $response['Lead']['segmentoPOID']);

        $sendmail = $this->request->query('sendmail');

        if ( $sendmail )
        {
            //$ticket = self::addTicket();
            $mail = self::sendMail( $response );
            echo sprintf( "<center> email enviado p/ %s </hr></center>", $response['Lead']['Cliente']['clienteEmail'] ) ;

            //Slack
            $msg = '<'.$this->baseUrl.'lead/aviseme/'.$leadID.'|ver email> '.$_SESSION['MM_Nick'].' Email enviado referente ao Lead nº'.$leadID;
            $log = $this->logSlack( $msg );
        }

        //s($mail);

        $profile = View::factory('profiler/stats');
        //s($profile);
        //die();

        if ( ! isset($response['Lead']['segmentoPOID']))
        {
            return false;
        }

        if ( ! $sendmail )
        {
            $url  = $this->baseUrl.'lead/aviseme/'.$leadID.'?sendmail=true' ;
            echo sprintf( "<center> <a href='%s' target='_self'>mandar email p/ o cliente %s</a><hr></center> ", $url, $response['Lead']['Cliente']['clienteEmail'] ) ;
        }

        //In Controller Website
        $response = $this->config_mail( $response );
        
        //s($response);
        //die();

        return $this->render( 'lead/aviseme', $response );
    }

    private function sendMail( $data )
    {
        $url= sprintf('/lead/aviseme/%s', $data['Lead']['id'] );

        $html = Request::factory($url)
            ->execute()
            ->body();

        $segmento = $data['Lead']['segmentoPOID'];

        switch ($segmento) {
            case 5:
                $fields["Subject"]      = utf8_decode(sprintf('Lead de Autopeças Nº %s', $data['Lead']['id'] ));
                $fields["addAddress"][] = 'pedidos-ecommerce-autopecas@vallery.com.br';
                $fields["addAddress"][] = $data['Lead']['Cliente']['clienteEmail'];

                if (isset($data['Lead']['Cliente']['Gerente']['UsuarioEmailInterno']))
                    $fields["addReplyTo"][] = $data['Lead']['Cliente']['Gerente']['UsuarioEmailInterno'];

                $fields["addBCC"][] = $data['Lead']['Vendedor']['UsuarioEmail'];

                /* if ( isset($data['Lead']['Vendedor']['UsuarioSegmento']) and $data['Lead']['Vendedor']['UsuarioSegmento'] == 'bearings')
                {
$fields["addAddress"][] = 'camilacb@vallery.com.br';
                } */

                break;
            case 1:
                echo "i equals 1";
                break;
            case 2:
                $fields["Subject"]      = utf8_decode(sprintf('Proposta de Rolamentos Nº %s', $data['Lead']['id'] ));
                $fields["addAddress"][] = $data['Lead']['Cliente']['clienteEmail'];

                if (isset($data['Lead']['Cliente']['Gerente']['UsuarioEmailInterno']))
                    $fields["addReplyTo"][] = $data['Lead']['Cliente']['Gerente']['UsuarioEmailInterno'];

                $fields["addBCC"][] = $data['Lead']['Vendedor']['UsuarioEmail'];
                break;
        }

        //$fields["addAddress"][] = 'camilacb@vallery.com.br';
        //$fields["addReplyTo"][] = 'pedidos-ecommerce-autopecas@vallery.com.br';
        //$fields["addBCC"][] = 'pedidos-ecommerce-autopecas@vallery.com.br';

        $fields["SMTPDebug"] = 0;
        $fields["isHTML"]    = true; // Set email format to HTML
        $fields["Body"]      = utf8_decode($html);
        $fields["AltBody"]   = 'Rolemak';

        //SEND EMAIL VIA WEBSERVICE

        $email = Request::factory( $_ENV['sendmail'] )
            ->method(Request::POST)
            ->post($fields)
            ->execute()
            ->body();

        //s($email); 

        return $email;
    }

    ///-----------------------------------------------------------------
    ///   Description:    <Função para Trazer os tickets por vendedor>
    ///   Author:         <Rogério>                 Date: <26-09-2018>
    ///   Notes:          <>
    ///   Revision History:
    ///   Name: ##           Date: ##        Description: ##
    ///-----------------------------------------------------------------
    // http://www.internut.com.br/crm/v3/tickets/vendedor/?order=asc&offset=0&limit=100
    public function addTicket()
    {
        $data = array(
            'ticketData'        => date("Y-m-d H:i:s"),
            'ticketClienteId'   => $post['ticketClienteId'],
            'ticketUserId'      => $_SESSION['MM_Userid'],
            'ticketContatoId'   => 0,
            'ticketOrigemId'    => $post['ticketOrigemId'],
            'ticketAssunto'     => $post['ticketAssunto'],
            'ticketConsultaId'  => $post['ticketConsultaId'] ,
            'ticketPedidoId'    => $post['ticketPedidoId'] ,
            'ticketDetalhes'    => $post['ticketDetalhes'],
            'ticketDataRetorno' => $post['ticketDataRetorno'],
            'ticketStatusId'    => $post['ticketStatusId'],
            'ticketEventoId'    => $post['ticketEventoId'],
            'ticketProcessoId'  => $post['ticketProcessoId'],
            'ticketCanalId'     => $post['ticketCanalId'],
        );

        $url = $_ENV['api_vallery_v1'].'//crmTickets/';

        $response = self::put( $url , $data );

        s($response);
        die();

        if ( $_SESSION['MM_Userid'] == 132 )
        {
            //s($response);
            //die();
        }

        if ( $response )
        {
            $data = array(
                'TicketId'             => $response['id'],
                'historicoData'        => date("Y-m-d H:i:s"),
                'historicoUserId'      => $_SESSION['MM_Userid'],
                'historicoDataRetorno' => $post['ticketDataRetorno'],
                'historicoOrigemId'    => $post['ticketOrigemId'],
                'historicoProcessoId'  => $post['ticketProcessoId'],
                'historicoDetalhes'    => $post['ticketDetalhes'],
                'historicoCanalId'     => $post['ticketCanalId'],
                'historicoStatusId'    => $post['ticketStatusId'],
                'historicoEventoId'    => $post['ticketEventoId'],
            );

            $url = $_ENV['api_vallery_v1'].'//crmHistorico/';

            self::put( $url , $data );
        }

        return $this->response->body( true );
    }

    private function put( $url, $data)
    {
        $json = Request::factory($url)
            ->method('PUT')
            ->post($data)
            ->execute()
            ->body();

        return $response = json_decode( $json, true );
    }



}