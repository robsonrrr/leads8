<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Order_Base extends Controller_Website  {

    public function config_mail_order( $response )
    {
        switch ($response['segmentoPOID']) {
            case 1:
                $response['Mail']['title'] = "Zoje Máquina de Costura Industrial";
                $response['Mail']['color'] = "#00a353";
                $response['Mail']['logo']  = $_ENV['cdn']."media/rolemak-branco.png";
                break;
            case 2:
                $response['Mail']['title'] = "Rolemak Rolamentos em Geral";
                $response['Mail']['color'] = "#ef8200";
                $response['Mail']['logo']  = $_ENV['cdn']."media/rolemak-branco.png";
                break;
            case 3:
                $response['Mail']['title'] = "Rolemak Peças e Acessórios";
                $response['Mail']['color'] = "#04a1cd";
                $response['Mail']['logo']  = $_ENV['cdn']."media/rolemak-branco.png";
                break;
            case 4:
                $response['Mail']['title'] = "Mak Metais";
                $response['Mail']['color'] = "#00aec7";
                $response['Mail']['logo']  = $_ENV['cdn']."media/rolemak-branco.png";
                break;
            case 5:
                $response['Mail']['title'] = "Mak Automotive";
                $response['Mail']['color'] = "#fdb812";
                $response['Mail']['logo']  = $_ENV['cdn']."media/r6/logotipo-mak-automotive-completo.png";
                break;
            case 6:
                $response['Mail']['title'] = "PPK Motoparts";
                $response['Mail']['color'] = "#db1414";
                $response['Mail']['logo']  = $_ENV['cdn']."media/r6/logotipo-ppk-motoparts.png";
                break;
        }

        return $response;
    }

    public function notify( $text, $link = null )
    {
        $data = array(
            'noteMessage'       => $text,
            'noteUserId'        => 999,
            'noteDateTime'      => $this->get_date_new(),
            'noteDestinationId' => 1,
            'noteMessageType'   => 'template',
            'noteLink'          => $link,
        );

        $url = $_ENV['api_vallery_v1'].'/Notifications/';

        $json = Request::factory($url)
            ->method('PUT')
            ->post($data)
            ->execute()
            ->body();
    }

}