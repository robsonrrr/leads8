<?php
class Controller_Order_Mail extends Controller_Order_Base {

    public function before()
    {
        parent::before();
    }

    public function action_index()
    {
        $orderID = $this->request->param('id');
        
        $email = $this->request->query('email');
        
        if ( ! $orderID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $orderID</h3>'));

        $json = Request::factory( '/order/build/'.$orderID )
            ->method('GET')
            ->execute()
            ->body();

        $response = json_decode($json,true);

        //$profile = View::factory('profiler/stats');
        //d($response);
        //die();

        //In Controller Website
        $response = $this->config_mail_order( $response );

        $response['dadosBancarios'] = $this->bank( $response['segmentoPOID'], $response['unidadeEmitentePOID'] );

        $sendmail = $this->request->query('sendmail');

        if ( $sendmail )
        {
            $mail = self::sendMail( $response, $email );
            echo sprintf( "<center> email enviado p/ %s </hr></center>", $email ) ;

            //Slack
            $msg = '<'.$this->baseUrl.'order/mail/'.$orderID.'|ver email> '.$_SESSION['MM_Nick'].' Email enviado referente ao Pedido nº'.$orderID;
            $log = $this->logSlack( $msg );
        }

        //$template = $this->render( 'mustache/home', $lead );
        return $this->render( 'order/mail', $response );
    }

    private function sendMail( $order, $email )
    {
        if(!$email) {
            $email = $order['Cliente']['clienteEmail'];
        }
        
        $url= sprintf('/order/mail/%s', $order['id'] );

        $html = Request::factory($url)
            ->execute()
            ->body();

       switch ( $order['segmentoPOID'] )
        {
            case 1:
                $name = 'Máquinas';
                $fields["addBCC"][] = 'pedidos-ecommerce-maquinas@vallery.com.br';
                break;
            case 2:
                $name = 'Rolamentos';
                $fields["addBCC"][] = 'pedidos-ecommerce-rolamentos@vallery.com.br';
                break;
            case 3:
                $name = 'Peças';
                $fields["addBCC"][] = 'pedidos-ecommerce-rolamentos@vallery.com.br';
                break;
            case 4:
                $name = 'Metais';
                $fields["addBCC"][] = 'ronaldrr@rolemak.com.br';
                break;
            case 5:
                $name = 'Autopeças';
                $fields["addBCC"][] = 'pedidos-ecommerce-autopecas@vallery.com.br';
                break;
            case 6:
                $name = 'Motopeças';
                $fields["addBCC"][] = 'pedidos-ecommerce-moto@vallery.com.br';
                break;
        }

        $fields["Subject"]      = utf8_decode(sprintf('Confirmação do Pedido de %s nº %s - %s', $name, $order['id'], $order['Emitente']['emitenteFantasia'] ));
        $fields["addAddress"][] = $email;

        if (isset($order['Cliente']['Vendedor']['UsuarioEmailInterno']))
        {
            $fields["addBCC"][]   = $order['Cliente']['Vendedor']['UsuarioEmailInterno'];
            $fields["addReplyTo"] = $order['Cliente']['Vendedor']['UsuarioEmailInterno'];
        }

        $fields["addAddress"][] = 'rogeriobbvn@rolemak.com.br';

        $fields["SMTPDebug"] = 0;
        $fields["isHTML"]    = true; // Set email format to HTML
        $fields["Body"]      = utf8_decode($html);
        $fields["AltBody"]   = 'Rolemak';

        $email = $this->sendMailService( $fields );
    }

}