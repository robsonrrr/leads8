<?php
class Controller_Lead_Raioy extends Controller_Lead_Base {

    public function action_index()
    {
        // if ( $_SESSION['MM_Userid'] <> 84 )
        // die();

        $leadID = $this->request->param('id');
        $segmentID = $this->request->param('segment');

        $where = null;

        if ( $leadID )
            $where.=sprintf(" AND cSCart = %s ", $leadID);

        $sql= sprintf("SELECT cSCart,cCustomer,cEmitUnity,cType
                        FROM sCart
                        WHERE 1=1
                        %s
                        LIMIT 1", $where);

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();
        // s($response);

        $cCustomer  = $response[0]['cCustomer'];
        $cEmitUnity = $response[0]['cEmitUnity'];
        $cType      = $response[0]['cType'];

        $where = null;
        $left  = null;

        if ( 1 == $cEmitUnity )
        {
            $left = sprintf(" LEFT JOIN mak_0109.Estoque as stock on ( stock.ProdutoPOID = inv.id ) ");
        }

        if ( 8 == $cEmitUnity )
        {
            $left = sprintf(" LEFT JOIN mak_0885.Estoque as stock on ( stock.ProdutoPOID = inv.id ) ");
        }

        if ( 6 == $cEmitUnity )
        {
            $left = sprintf(" LEFT JOIN mak_0613.Estoque as stock on ( stock.ProdutoPOID = inv.id ) ");
        }

        if ( 3 == $cEmitUnity )
        {
            $left = sprintf(" LEFT JOIN mak_0370.Estoque as stock on ( stock.ProdutoPOID = inv.id ) ");
        }

        if ( $cCustomer )
        {
            $where.=sprintf(" AND hoje.idcli = %s", $cCustomer);
        }

        if ( $cEmitUnity )
        {
            $where.=sprintf(" AND stock.EstoqueDisponivel > 0 ");
        }

        if ( $segmentID )
        {
            if ( 6 == $segmentID )
            {
                $where.= sprintf(" AND produtos.segmento_id = 2 " );
            }
            else{
                $where.= sprintf(" AND produtos.segmento_id = %s ", $segmentID );
            }

        }

        $sql= sprintf("SELECT  hoje.id as pedidoID, hoje.UnidadeLogistica as pedidoUnidadeLogistica, 
                               hist.isbn as produtoID, inv.nome as produtoNome, inv.revenda as produtoRevenda, inv.modelo as produtoModelo,
                               stock.EstoqueDisponivel as produtoEstoque
                        FROM hoje
                        LEFT JOIN hist on ( hist.pedido=hoje.id)
                        LEFT JOIN inv  on ( inv.id=hist.isbn)
                        LEFT JOIN produtos on (produtos.id=inv.idcf)
                        %s
                        WHERE 1=1
                        AND inv.vip < 8
                        AND ( DATE(hoje.data) BETWEEN DATE_SUB(CURDATE(),INTERVAL 5 YEAR) AND CURDATE() )
                        %s
                        GROUP BY inv.id
                        LIMIT 100", $left, $where);

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        //s($sql, $response);
        //die();

        //INSERT INTO `icart` ( `cSCart`,  `dInquiry`, `cProduct`, `qProduct`, `vProduct`) 
        //VALUES ('1', CURRENT_TIMESTAMP, '1', '1', CURRENT_TIMESTAMP, '1', '1', '1', '0.00', '0.00', '0.00', '0.0000')

        if ( 0 == count( $response ) )
        {
            $redirect  = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].''.$_SERVER['HTTP_X_FORWARDED_PREFIX'];
            $url = $redirect.''.$leadID.'/'.$segmentID.'?alert=01&title=Sem historíco&message=sem pedido nos últimos 5 anos';
            return $this->response->body($url);
        }

        $values = null;
        $last   = 0;

        foreach ( $response as $k => $v )
        {
            if ( $last > 0)
                $values.=",";

            $values.=" ('".$leadID."','".$this->get_date_new()."','".$v['produtoID']."','1','".$v['produtoRevenda']."','".$v['produtoRevenda']."'  ) ";

            $last++;
        }

        $sql      = sprintf("INSERT INTO `icart` ( `cSCart`,  `dInquiry`, `cProduct`, `qProduct`, `vProduct`, `vProductOriginal`)  VALUES %s ", $values);
        $query    = DB::query(Database::INSERT, $sql);
        $insert = $query->execute();

        //s($response,$insert);

        //$url = sprintf('http://leads5/lead/recalculate/%s/%s', $leadID, $segmentID) ;

        //s($data,$where);
        //die();

        //$update = Controller_Lead_Recalculate::lead( $leadID, $segmentID, true);
        //s($update);
        //die();
        
        $leadOrigem = 1;
        
        if ($cType == 2)
        {
           $leadOrigem = $cType;
        }
        
        //Add Ticket
        $ticket = array(
            'ticketClienteId'   => $cCustomer,
            'ticketOrigemId'    => $leadOrigem,
            'ticketConsultaId'  => $leadID,
            'ticketPedidoId'    => 0,
            'ticketStatusId'    => 1,
            'ticketEventoId'    => 145,
            'ticketProcessoId'  => 3,
            'ticketCanalId'     => 6,
            'ticketDetalhes'    => 'Criado automáticamente pelo sistema',
            'ticketDataRetorno' => $this->get_date_new(),
        );

        $ticket = Controller_Ticket_Generate::addTicket($ticket, $this->get_date_new() );

        $msg = '<'.$this->baseUrl.'lead/mail/'.$leadID.'/'.$segmentID.'/|ver email> '.$_SESSION['MM_Nick'].' criou Raio Y referente ao Lead nº'.$leadID;
        $log = $this->logSlack( $msg );

        $redirect  = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].''.$_SERVER['HTTP_X_FORWARDED_PREFIX'];
        $url = $redirect.'lead/recalculate/'.$leadID.'/'.$segmentID.'?redirect=true&price=true&alert=01&title=Raio Y&message=Criado com sucesso, por favor revisar os produtos antes de enviar para o cliente, itens vendidos dos últimos 5 anos e limite máximo 100 itens para não travar o lead.';

        return $this->response->body($url);


    }

}