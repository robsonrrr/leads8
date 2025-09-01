<?php
class Controller_Order_Build extends Controller_Website{

    private function get()
    {
        date_default_timezone_set('UTC');

        $this->orderID = $this->request->param('id');

        if ( ! $this->orderID )
            die(s('Favor Informar orderID'));

        $json = Request::factory( '/order/get/'.$this->orderID )
            ->method('GET')					
            ->execute()
            ->body();

        $data = json_decode( $json, true);

        return $data;
    }

    public function action_index()
    {
        $response = self::get();

        if ( ! isset( $response['data']['allPedidos']['edges'][0] ) )
            die( sprintf('<h3 align="center" style="margin-top:100px">Pedido No.%s não existe no sistema!</h3>', $this->orderID));

        $array = null;
        //s($lead);
        //die();

        $order = $response['data']['allPedidos']['edges'][0]['node'];
        $array                         = $order;
        $array['dataEmissaoFormatado'] = date("d/m/Y H:i:s", strtotime($order['pedidoDataEmisao']));
        $array['pedidoDataEntregaProgramadaFormatado'] = date("d/m/Y", strtotime($order['pedidoDataEntregaProgramada']));
        $array['Cliente']              = $order['Cliente']['edges'][0]['node']; 
        
        if ( isset( $order['clienteDeCliente']['edges'][0]['node'] ))
        {
            $array['clienteDeCliente'] = $order['clienteDeCliente']['edges'][0]['node'];           
        }
        
        $array['Natureza']             = $order['Natureza']['edges'][0]['node']; 

        //NOP
        $array['Natureza'][$array['Natureza']['id']] = true;

        $array['Transportadora']       = $order['Transportadora']['edges'][0]['node'];
        $array['Transportadora'][$array['Transportadora']['transportadoraNome']] = true;

        //$array['TipoTransporte']       = $order['TipoTransporte']['edges'][0]['node'];
        $array['TipoPagamento']        = $order['TipoPagamento']['edges'][0]['node'];

        $array['segmentoPedido'][$order['segmentoPOID']] = true;

        //Pagamento
        $array['TipoPagamento'][$array['TipoPagamento']['pagamentoDescricao']] = true;

        //$array['Pagamentos']           = $order['Pagamentos']['edges'][0]['node'];
        $array['Prazo']                = $order['Prazo']['edges'][0]['node'];
        $array['Emitente']             = $order['Emitente']['edges'][0]['node'];
        //$array['UnidadeLogistica']   = $order['UnidadeLogistica']['edges'][0]['node'];
        $array['PedidoStatus']         = $order['PedidoStatus']['edges'][0]['node'];

        if ( isset($order['Vendedor']['edges'][0]['node']))
        {
            $array['Vendedor']             = $order['Vendedor']['edges'][0]['node'];
        }

        $array['Produtos']             = $order['DetalheProdutos']['edges'];

        foreach(  $array['Produtos'] as $k => $v )
        {
            $array['Produtos'][$k] = $v['node'];
            $array['Produtos'][$k]['Detalhe']  = $array['Produtos'][$k]['Produto']['edges'][0]['node'];
            $array['Produtos'][$k]['Segmento'] = $array['Produtos'][$k]['Detalhe']['Segmento']['edges'][0]['node'];
        }

        //s($array);
        //die();

        $quantidade = 0;
        $variedade  = 0;
        $peso       = 0;
        $pedido     = 0;
        $produto    = 0;
        $ipi        = 0;
        $st         = 0;
        $impostos   = 0;
        $frete      = 0;

        foreach( $array['Produtos'] as $k => $v )  
        { 
            //s($v);
            //die();
            $variedade++;

            // $solr = self::get_solr_query( $v['produtoPOID'] );
            // $array['Produtos'][$k]['solr'] = $solr;

            $array['Produtos'][$k]['produtoMarcaFormatado'] = $this->clean_string( $v['Detalhe']['produtoMarca'] );
            //$array['Produtos'][$k]['produtoDescontoPorcentagem'] =  round( (1 - ( $v['pedidoValor'] / $v['pedidoValorOriginal'] ) ) * 100, 0);

            //Impostos
            $array['Produtos'][$k]['produtoSTSubtotal']   = $this->formatPrice($v["produtoST"]);
            $array['Produtos'][$k]['produtoIPISubtotal']  = $this->formatPrice($v["produtoIPI"]);
            $array['Produtos'][$k]['produtoST']           = $this->formatPrice($v["produtoST"]   / $v["pedidoProdutoQuantidade"]);
            $array['Produtos'][$k]['produtoIPI']          = $this->formatPrice($v["produtoIPI"]  / $v["pedidoProdutoQuantidade"]);
            $array['Produtos'][$k]['pedidoValorUnitario'] = $this->formatPrice($v["pedidoValor"]);

            if ( $v["pedidoValor"] > 0 )
            {
                $produto    += $v["pedidoValor"] * $v["pedidoProdutoQuantidade"];
                $ipi        += $v["produtoIPI"];
                $st         += $v["produtoST"];
                $impostos   += $v["produtoIPI"] + $v["produtoST"];
            }

            $array['Produtos'][$k]['produtoContagem']                = $variedade;
            $array['Produtos'][$k]['pedidoValorSubtotal']            = $this->formatPrice( $v["pedidoValor"] * $v["pedidoProdutoQuantidade"]);
            $array['Produtos'][$k]['pedidoValorUnitarioComImpostos'] = $this->formatPrice( $v["pedidoValor"] + ( $v["produtoIPI"] / $v["pedidoProdutoQuantidade"] ) + ( $v["produtoST"] / $v["pedidoProdutoQuantidade"]) );
            $array['Produtos'][$k]['pedidoValorSubtotalComImpostos'] = $this->formatPrice( ( $v["pedidoValor"] * $v["pedidoProdutoQuantidade"] ) + $v["produtoST"] + $v["produtoIPI"]  );

            if ( $v['Detalhe']["produtoPeso"] > 0 )
            {
                $peso += $v["pedidoProdutoQuantidade"] * $v['Detalhe']['produtoPeso'];
            }

            if ( $v["pedidoProdutoQuantidade"] > 0 )
            {
                $quantidade += $v["pedidoProdutoQuantidade"];
            }

            if ( 0 == $v['pedidoProdutoVezes'] )
            {
                $array['Produtos'][$k]['pedidoProdutoVezesFormatado'] = 'à vista';
            }else{
                $array['Produtos'][$k]['pedidoProdutoVezesFormatado'] = $v['pedidoProdutoVezes'].'x';
            }
        }


        $pedido = $produto + $ipi + $st;
        $frete  = $array['pedidoValorFrete'];

        //Total
        $array['Total'] = array(
            'quantidade' => $quantidade,
            'variedade'  => $variedade,
            'peso'       => round( $peso, 2),
            'pedido'     => $this->formatPrice($pedido + $frete),
            'frete'      => $this->formatPrice($frete),
            'ipi'        => $this->formatPrice($ipi),
            'st'         => $this->formatPrice($st),
            'produto'    => $this->formatPrice($produto),
            'impostos'   => $this->formatPrice($impostos)
        );

        //d($array);

        //echo $profile = View::factory('profiler/stats');

        $this->response->body( json_encode($array) );
    }

    public function get_solr_query( $id )
    {
        //return array();

        $fl= "id,catalogoPOID,categoriaPOID,segmentoPOID,segmentoSEO,produtoPOID,produtoModelo,produtoNome,produtoMarca,produtoSEO,produtoRanking,produtoEmbalagem,produtoDescricaoCurta";

        $url = "http://www.rolemak.com.br/solr/catalogo/select?q=produtoPOID:".$id."&fl=".$fl."&indent=on&wt=json&rows=1";

        $json = Request::factory( $url )
            ->headers('Authorization', 'Basic '.AUTH)
            ->method('GET')
            ->execute()
            ->body();

        $response = json_decode($json,true);

        if ( $response['response']['numFound'] > 0)
        {
            return $response['response']['docs'][0];
        }

        return $response;
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

}