<?php
class Controller_Ticket_Generate extends Controller_Ticket_Base
{

    //http://office.vallery.com.br/leads5/ticket/generate/index?ticketClienteId=1&ticketOrigemId=1&ticketConsultaId=1&ticketPedidoId=1&
    //=teste&ticketDataRetorno=2021/01/30&ticketStatusId=1&ticketEventoId=1&ticketProcessoId=1&ticketCanalId=1
    public function action_index()
    {
        $data = array(
            'ticketClienteId'   => $this->request->query('ticketClienteId'),
            'ticketOrigemId'    => $this->request->query('ticketOrigemId'),
            //'ticketAssunto'     => $this->request->query('ticketAssunto'),
            'ticketConsultaId'  => $this->request->query('ticketConsultaId') ,
            'ticketPedidoId'    => $this->request->query('ticketPedidoId') ,
            'ticketDetalhes'    => $this->request->query('ticketDetalhes'),
            'ticketDataRetorno' => $this->request->query('ticketDataRetorno'),
            'ticketStatusId'    => $this->request->query('ticketStatusId'),
            'ticketEventoId'    => $this->request->query('ticketEventoId'),
            'ticketProcessoId'  => $this->request->query('ticketProcessoId'),
            'ticketCanalId'     => $this->request->query('ticketCanalId'),
        );
        
        $time = $this->get_date_new();

        self::addTicket($data,$time);
    }

    ///-----------------------------------------------------------------
    ///   Description:    <Função para Trazer os tickets por vendedor>
    ///   Author:         <Rogério>                 Date: <26-09-2018>
    ///   Notes:          <>
    ///   Revision History:
    ///   Name: ##           Date: ##        Description: ##
    ///-----------------------------------------------------------------
    // Controller_Ticket_Generate::addTicket();
    public static function addTicket($data, $time)
    {
        s($data);
        //die();

        $array = array(
            'ticketData'        => $time,
            'ticketClienteId'   => $data['ticketClienteId'],
            'ticketUserId'      => $_SESSION['MM_Userid'],
            'ticketContatoId'   => 0,
            'ticketOrigemId'    => $data['ticketOrigemId'],
            //'ticketAssunto'     => $data['ticketAssunto'],
            'ticketConsultaId'  => $data['ticketConsultaId'] ,
            'ticketPedidoId'    => $data['ticketPedidoId'] ,
            'ticketDetalhes'    => $data['ticketDetalhes'],
            'ticketDataRetorno' => $data['ticketDataRetorno'],
            'ticketStatusId'    => $data['ticketStatusId'],
            'ticketEventoId'    => $data['ticketEventoId'],
            'ticketProcessoId'  => $data['ticketProcessoId'],
            'ticketCanalId'     => $data['ticketCanalId'],
        );

        $url = $_ENV['api_vallery_v1'].'/crmTickets/';
        $ticket = self::put( $url , $array );

        s($ticket);
        //die();

        if ( $_SESSION['MM_Userid'] == 132 )
        {
            //s($response);
            //die();
        }

        if ( $ticket )
        {
            $info = array(
                'TicketId'             => $ticket['id'],
                'historicoData'        => $time,
                'historicoUserId'      => $_SESSION['MM_Userid'],
                'historicoDataRetorno' => $data['ticketDataRetorno'],
                'historicoOrigemId'    => $data['ticketOrigemId'],
                'historicoProcessoId'  => $data['ticketProcessoId'],
                'historicoDetalhes'    => $data['ticketDetalhes'],
                'historicoCanalId'     => $data['ticketCanalId'],
                'historicoStatusId'    => $data['ticketStatusId'],
                'historicoEventoId'    => $data['ticketEventoId'],
            );

            $url = $_ENV['api_vallery_v1'].'/crmHistorico/';
            $history =self::put( $url, $info );

            s($history);
        }

        return true;
    }

    private static function put( $url, $data)
    {
        $json = Request::factory($url)
            ->method('PUT')
            ->post($data)
            ->execute()
            ->body();

        return $response = json_decode( $json, true );
    }

}