<?php
class Controller_Lead_Transfer extends Controller_Lead_Base {

    public function action_index()
    {
        $leadID = $this->request->param('id');
        $segmentID = $this->request->param('segment');

        //s($leadID, $segmentID);

        $where = null;

        if ( $leadID )
            $where.=sprintf(" AND cSCart = %s ", $leadID);

        $sql= sprintf("SELECT cSCart,cCustomer,cEmitUnity
                        FROM sCart
                        WHERE 1=1
                        %s
                        LIMIT 1", $where);

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();
        // s($response);

        $cCustomer  = $response[0]['cCustomer'];
        $cEmitUnity = $response[0]['cEmitUnity'];

        $field = null;
        $where = null;
        $join  = null;

        $join.= sprintf(" LEFT JOIN mak_0109.Estoque as stock09 on ( stock09.ProdutoPOID = inv.id ) ");
        $join.= sprintf(" LEFT JOIN mak_0885.Estoque as stock85 on ( stock85.ProdutoPOID = inv.id ) ");
        $join.= sprintf(" LEFT JOIN mak_0613.Estoque as stock13 on ( stock13.ProdutoPOID = inv.id ) ");
        $join.= sprintf(" LEFT JOIN mak_0370.Estoque as stock70 on ( stock70.ProdutoPOID = inv.id ) ");
        $join.= sprintf(" LEFT JOIN mak_0613.Estoque_TTD_1 as stock13ttd on ( stock13ttd.ProdutoPOID = inv.id ) ");

        if ( 721240 == $cCustomer )
        {
            $field.=sprintf(" ( select sum(hi.quant) from hoje h, hist hi where hi.pedido=h.id AND h.UnidadeLogistica = 8 AND h.nop=27 AND hi.isbn=inv.id AND YEAR(h.data)=YEAR(NOW()) AND h.data BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() limit 1 ) AS Vendas30 ");

            if ( 5 == $segmentID )
            {
                $where.=sprintf(" AND ( stock13.EstoqueDisponivel + stock13ttd.EstoqueDisponivel > 1 ) AND stock85.EstoqueDisponivel < 100 ");
            }

            if ( 2 == $segmentID )
            {
                $where.=sprintf(" AND ( stock13.EstoqueDisponivel + stock13ttd.EstoqueDisponivel > 1 ) AND stock09.EstoqueDisponivel < 100 ");
            }
        }

        if ( 30000 == $cCustomer )
        {
            $field.=sprintf(" ( select sum(hi.quant) from hoje h, hist hi where hi.pedido=h.id AND h.UnidadeLogistica = 1 AND h.nop = 27 AND hi.isbn=inv.id AND YEAR(h.data)=YEAR(NOW()) AND h.data BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() limit 1 ) AS Vendas30 ");
        }

        if ( $segmentID )
        {
            $where.=sprintf(" AND p.segmento_id = %s ", $segmentID);
        }

        $sql= sprintf("SELECT  inv.id as produtoID, inv.nome as produtoNome, inv.revenda as produtoRevenda, inv.modelo as produtoModelo,
                               stock09.EstoqueDisponivel as estoqueSP, stock85.EstoqueDisponivel as estoqueSPBF, 
                               stock13.EstoqueDisponivel + stock13ttd.EstoqueDisponivel as estoqueSC,
                               ROUND(((d.FOB+d.FRETE+d.SEGURO+d.II+d.DESPESA+d.VLR_PIS+d.VLR_COFINS+d.TAXA_SISCOMEX+d.VALOR_DESPESA+d.AFRMM)/d.QTDE),3) AS produtoCustoTransferencia,
                               %s
                        FROM inv
                        LEFT JOIN produtos as p  on ( p.id=inv.idcf)
                        LEFT JOIN dis as d on (d.CODIGO=inv.id)
                        %s
                        WHERE 1=1
                        AND inv.vip <> 9
                        %s
                        GROUP BY inv.id
                        LIMIT 500", $field, $join, $where);

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        if ( 0 == count( $response ) )
        {
            die('sem produto');
        }

        //s($sql, $response);
        //die();

        $values = null;
        $first  = 0;

        foreach ( $response as $k => $v )
        {
            $qtd   = 1;
            $custo = $v['produtoCustoTransferencia'];

            if ( 0.00 == $custo )
            {
                $custo = $v['produtoRevenda'];
            }

            if ( 721240 == $cCustomer )
            {
                $stock1 = $v['estoqueSC'];
                $stock2 = $v['estoqueSPBF'];
                $sales  = $v['Vendas30'];

                if ( $v['Vendas30'] > 1 )
                {
                    $qtd = $v['Vendas30'];
                }

                if ( $stock2 >= $stock1 )
                {
                    $qtd = 0;
                }

                if ( $stock2 < $stock1 )
                {
                    $qtd = round( $stock1 * 0.7, 0);
                }

                if ( $qtd > $sales )
                {
                    $qtd = $sales;
                }

                if ( $qtd < $stock2 )
                {
                    $qtd = 0;
                }
            }

            if ( 30000 == $cCustomer )
            {
                // $stock = round( $v['estoqueSC'] / 2, 0);
                $qtd   = 1;
            }

            if ( $v['Vendas30'] > 0 and $qtd > 0 )
            { 
                if ( $first > 0)
                    $values.=",";

                $values.=" ('".$leadID."','".$this->get_date_new()."','".$v['produtoID']."','".$qtd."','".$custo."','".$v['produtoRevenda']."'  ) ";

                $first++;
            }else{
                $qtd = 0;
            }

            $response[$k]['sugestao'] = $qtd;
        }

        //s($response);

        $sql      = sprintf("INSERT INTO `icart` ( `cSCart`,  `dInquiry`, `cProduct`, `qProduct`, `vProduct`, `vProductOriginal`)  VALUES %s ", $values);
        $query    = DB::query(Database::INSERT, $sql);
        $insert = $query->execute();

        //s($sql);

        $redirect  = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].''.$_SERVER['HTTP_X_FORWARDED_PREFIX'];
        $url = $redirect.'lead/recalculate/'.$leadID.'/'.$segmentID.'?redirect=true&alert=01&title=Raio Z&message=Criado com sucesso, por favor revisar os produtos de emitir o pedido';

        return $this->response->body($url);

    }
}