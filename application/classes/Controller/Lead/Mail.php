<?php
class Controller_Lead_Mail extends Controller_Lead_Base {

    public function before()
    {
        parent::before();
    }

    public function action_index()
    {
        $leadID    = $this->request->param('id');
        $segmentID = $this->request->param('segment');
        $sendmail  = $this->request->query('sendmail');
        $layout    = $this->request->query('layout');

        if(!isset($this->baseUrl))
        {
          $this->baseUrl = sprintf('%s://%s%s',$_SERVER['HTTP_X_FORWARDED_PROTO'], $_SERVER['HTTP_HOST'], $_SERVER['HTTP_X_FORWARDED_PREFIX'] );
        }

        //s($sendmail);

        if ($_SESSION['MM_Nivel'] < 4)
        {
            //die('<h1>Acesso não autorizado, falar com Rogério</h1>');
        }

        if ( ! $leadID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $leadID</h3>'));

        if ( ! $segmentID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $segmentID</h3>'));

        if ( ! isset($_SESSION['check']) )
            $_SESSION['check'] = true;

        $data = array(
            'check'   => $_SESSION['check'] ,
            'segment' => $segmentID
        );

        $json = Request::factory( '/lead/build/'.$leadID )
            ->method('GET')
            ->query($data)
            ->execute()
            ->body();

        $response = json_decode( $json, true);

        foreach($response['Lead']['Produtos'] as $key => $value ){
            $revendaValue = $value['Detalhe']['produtoRevenda'];
            
            // Handle different possible formats
            if (is_string($revendaValue)) {
                // Remove any non-numeric characters except comma and dot
                $cleanValue = preg_replace('/[^\d,.]/', '', $revendaValue);
                
                // Check if it's Brazilian format (has both . and ,)
                if (strpos($cleanValue, '.') !== false && strpos($cleanValue, ',') !== false) {
                    // Brazilian format: 1.234,56
                    $cleanValue = str_replace('.', '', $cleanValue); // Remove thousand separators
                    $cleanValue = str_replace(',', '.', $cleanValue); // Convert decimal comma
                } elseif (strpos($cleanValue, ',') !== false) {
                    // Only comma: 1234,56
                    $cleanValue = str_replace(',', '.', $cleanValue);
                }
                
                $v = (float) $cleanValue;
            } else {
                $v = (float) $revendaValue;
            }
            
            // echo '<br>'.$v;
            // s($v);
            // Use strict comparison for zero
            if ($v <= 0) {
                //s($v);
                unset($response['Lead']['Produtos'][$key]);
            }
        }
        
        // Reindex array to remove gaps after unsetting elements
        $response['Lead']['Produtos'] = array_values($response['Lead']['Produtos']);
        // s($response)        ;
        //Pega Nome e Email do comprador
        $response['crm'] = self::getContact( $response );

        //Dados Bancários
        $response['dadosBancarios'] = $this->bank( $response['Lead']['segmentoPOID'], $response['Lead']['unidadeEmitentePOID'] );

        if ( $sendmail )
        {
            //$ticket = self::addTicket();
            $mail = self::sendMail( $response );
            echo sprintf( "<center> email enviado p/ %s </hr></center>", $this->request->query('email') ) ;

            //Slack
            $msg = '<'.$this->baseUrl.'lead/mail/'.$leadID.'/'.$segmentID.'/|ver email> '.$_SESSION['MM_Nick'].' Email enviado referente ao Lead nº'.$leadID;
            $log = $this->logSlack( $msg );
        }else{


              $url  = $this->baseUrl.'lead/mail/'.$leadID.'/'.$segmentID.'/?sendmail=true' ;

              foreach ( $response['crm'] as $key => $value )
              {
                   echo sprintf( "<center class='noprint'> <a class='noprint' href='%s&email=%s&nome=%s' target='_self'>mandar email p/ o cliente %s</a><hr></center> ", $url, $value['contatoEmail'], $value['contatoNome'], $value['contatoEmail'] ) ;
              }



        }

        //s($response);
        //$profile = View::factory('profiler/stats');
        //s($profile);
        //die();

        if ( ! isset($response['Lead']['segmentoPOID']))
        {
            return false;
        }

        //In Controller Website
        $response = $this->config_mail( $response );

        //s($response);
        //die();

        return $this->render( 'lead/mail', $response );
    }

    private function getContact( $data )
    {
        $sql= sprintf('SELECT
                        contatos.id,
                        COALESCE( contatos.email, clientes.email ) as contatoEmail,
                        COALESCE( contatos.nome, clientes.atenc ) as contatoNome
                       FROM mak.`clientes`
                       LEFT JOIN  crm.`contatos` ON ( clientes.id  = contatos.cliente_id )
                       WHERE clientes.id = %s AND COALESCE( contatos.email, clientes.email ) IS NOT NULL
                       LIMIT 10', $data['Lead']['clientePOID'] );

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        if ( isset($response[0]['contatoEmail']) and isset($response[0]['contatoNome']) )
        {
            return $response;
        }else{
            die('<h1>Favor Cadastrar Informações no CRM *contatoEmail* e *contatoNome* para usar a ferramenta de enviar email</h1>');
        }
    }

    private function sendMail( $data )
    {
        $segmento = $data['Lead']['segmentoPOID'];

        $url= sprintf('/lead/mail/%s/%s', $data['Lead']['id'] , $segmento);

        $html = Request::factory($url)
            ->execute()
            ->body();

        if (isset($data['Lead']['Cliente']['Gerente']['UsuarioEmailInterno']))
            $fields["addReplyTo"][] = $data['Lead']['Cliente']['Gerente']['UsuarioEmailInterno'];

        $fields["addBCC"][]     = $data['Lead']['Vendedor']['UsuarioEmailInterno'];
        //Para o cliente
        $fields["addAddress"][] = $this->request->query('email');

        switch ($segmento) {
            case 1:
                $fields["Subject"] = utf8_decode(sprintf('%s segue proposta de Máquinas Nº %s', $this->request->query('nome') , $data['Lead']['id'] ));
                break;
            case 2:
                $fields["Subject"] = utf8_decode(sprintf('%s segue proposta de Rolamentos Nº %s', $this->request->query('nome') , $data['Lead']['id'] ));
                break;
            case 3:
                $fields["Subject"] = utf8_decode(sprintf('%s segue proposta de Peças Nº %s', $this->request->query('nome') , $data['Lead']['id'] ));
                break;
            case 4:
                $fields["Subject"] = utf8_decode(sprintf('%s segue proposta de Metais Nº %s', $this->request->query('nome') , $data['Lead']['id'] ));
                break;
            case 5:
                $fields["Subject"] = utf8_decode(sprintf('%s segue proposta de Autopeças Nº %s', $this->request->query('nome') , $data['Lead']['id'] ));
                break;
            case 6:
                $fields["Subject"] = utf8_decode(sprintf('%s segue proposta de Motopeças Nº %s', $this->request->query('nome') , $data['Lead']['id'] ));
                break;
        }

        $fields["SMTPDebug"] = 0;
        $fields["isHTML"]    = true; // Set email format to HTML
        $fields["Body"]      = utf8_decode($html);
        $fields["AltBody"]   = 'Rolemak';

        //SEND EMAIL VIA WEBSERVICE
        //s($fields);
        //die();

        $email = Request::factory( $_ENV['sendmail'] )
            ->method(Request::POST)
            ->post($fields)
            ->execute()
            ->body();

        s($email);

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
