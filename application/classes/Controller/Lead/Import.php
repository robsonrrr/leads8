<?php

class Controller_Lead_Import extends Controller_Lead_Base {

    public function action_index()
    {
        $get  = $this->request->query();
        //s($get);
        //die();

        if ( empty($get))
        {
            die(s('sem $get'));
        }

        /*
        ┌──────────────────────────────────────────────────────────────────────────────┐
        │ $get                                                                         │
        └──────────────────────────────────────────────────────────────────────────────┘
        array (4) [
            'type' => string (9) "ecommerce"
            'order' => string (5) "teste"
            'lead' => string (6) "147731"
            'customer' => string (6) "716957"
        ]
        */
        
        $msg = 'Vendedor *'.$_SESSION['MM_Nick'].'*, Lead <'. $this->baseUrl .'/'.$get['lead'].'/'.$get['segmentoPOID'].'|'.$get['lead'].'> importou *'.$get['type'].'*, número *'.$get['order'].'*, lead *'.$get['lead'].'*, customer *'.$get['customer'].'*';
        $log = $this->logSlack( $msg );

        $redirect  = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].''.$_SERVER['HTTP_X_FORWARDED_PREFIX'];
        //return $this->redirect( $redirect.''.$get['lead'].'?alert=01&message="realizada com sucesso"', 302);

        if ( $get['type'] == 'ecommerce' )
        {
            $order = self::get_ecommerce( trim($get['order']), $get['customer'] );
            //s($order);
            //die();

            $this->message = 'Não existe essa cotação nome '.$get['order'].' para esse cliente';

            if ( isset($order[0]) )
            {
                $lead  = self::add_ecommerce( $order, $get['lead'] );
                self::redirecturl( $get['lead'], $get['segmentoPOID'] );
            }
        }

        if ( $get['type'] == 'quote' )
        {
            $order = self::get_quote( trim($get['order']), $get['customer'] );
            //s($order);
            //die();

            $this->message = 'Não existe essa cotação nome '.$get['order'].' para esse cliente';

            if ( isset($order[0]) )
            {
                $lead  = self::add_quote( $order, $get['lead'] );

                self::redirecturl( $get['lead'], $get['segmentoPOID']);
            }
        }

        if ( $get['type'] == 'order' )
        {
            $order = self::get_order( trim($get['order']), $get['customer'] );
            //s($order);
            //die();

            $this->message = 'Não existe esse pedido nº '.$get['order'].' ';

            if ( isset($order[0]) )
            {
                $lead  = self::add_order( $order, $get['lead'] );
                self::redirecturl( $get['lead'], $get['segmentoPOID'] );
            }
        }
        
        if ( $get['type'] == 'shipment' )
        {
            $order = self::get_shipment( trim($get['order']), $get['customer'] );
            //s($order);
            //die();

            $this->message = 'Não existe esse pedido nº '.$get['order'].' ';

            if ( isset($order[0]) )
            {
                $lead  = self::add_shipment( $order, $get['lead'] );
                self::redirecturl( $get['lead'], $get['segmentoPOID'] );
            }
        }

        if ( $get['type'] == 'lead' )
        {
            $order = self::get_lead( trim($get['order']), $get['customer'] );
            //s($order);
            //die();

            $this->message = 'Não existe esse Lead nº '.$get['order'].' ';

            if ( isset($order[0]) )
            {
                $lead  = self::add_lead( $order, $get['lead'], $get['segmentoPOID'] );

                self::redirecturl( $get['lead'],  $get['segmentoPOID'] );
            }
        }
    }

    //http://api-v1.vallery.com.br/v1/Leads/153681/Produtos/
    private function get_lead( $id )
    {
        $sql= sprintf("SELECT inv.id as produtoPOID, inv.unidade, inv.embalagem as produtoEmbalagem, inv.modelo as produtoModelo,inv.peso as produtoPeso,
                                inv.marca as produtoMarca, inv.origem as produtoOrigem, inv.icms_antecipado as produtoICMSantecipado,
                                inv.nome as produtoNome, inv.isento_st as produtoIsentoST, inv.marca as produtoMarca, inv.st as produtoST,
                                inv.revenda as produtoRevenda, packing.packing,
                                produtos.segmento_id as segmentoPOID, produtos.ncm as segmentoNCM, produtos.segmento as segmentoNome,
                                icart.vIPI as leadIPI, icart.vCST as leadST, icart.qProduct as leadQuantidade, icart.vProduct as leadValor,
                                icart.vProductOriginal as leadValorOriginal, icart.tProduct as leadVezes
                          FROM sCart
                          LEFT JOIN icart on ( icart.cSCart = sCart.cSCart )
                          LEFT JOIN inv on ( inv.id = icart.cProduct )
                          LEFT JOIN produtos on (produtos.id=inv.idcf)
                          LEFT JOIN Catalogo.packing on (packing.id=inv.embalagem)
                        WHERE 1=1
                        AND sCart.cSCart = %s
                        AND inv.vip <> 9
                        AND inv.idcf > 0
                        AND inv.revenda > 0", $id);

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        //s($response);
        //die();

        return $response;
    }
    
    private function get_shipment( $id )
    {
        $url = $_ENV["api_vallery_v1"].'/Embarques/'.$id.'/Produtos/';

        $json = Request::factory( $url )
            ->method('GET')
            ->query(array('filter'=>array('include'=> 'Produto')))
            ->execute()
            ->body();

        return json_decode($json,true);
    }

    private function get_order( $id )
    {
        $url = $_ENV["api_vallery_v1"].'/Pedidos/'.$id.'/DetalheProdutos/';

        $json = Request::factory( $url )
            ->method('GET')
            ->query(array('filter'=>array('include'=> 'Produto')))
            ->execute()
            ->body();

        return json_decode($json,true);
    }

    private function get_quote( $id )
    {
        $url = $_ENV["api_vallery_v1"].'/Cotacao/'.$id.'/Produtos/';

        $json = Request::factory( $url )
            ->method('GET')
            //->query(array('filter'=>array('include'=> 'Produto')))
            ->execute()
            ->body();

        return json_decode($json,true);
    }

    //https://api-ecommerce.vallery.com.br/v1/Cotacao?filter[where][cotacaoNome]=dama-maio&filter[where][clientePOID]=718343
    private function get_ecommerce( $name, $customer )
    {
        $url = $_ENV['api_ecommerce'].'/Cotacao';

        $json = Request::factory( $url )
            ->method('GET')
            ->query(array('filter' =>
                          array('where' =>
                                array( 'cotacaoNome'=> $name )
                               )))
            ->execute()
            ->body();

        return json_decode($json,true);
    }

    private function add_ecommerce( $array, $leadID )
    {
        $data  = array();
        $count = 0; 

        foreach ( $array as $k => $v )
        {
            $qty = $v['carrinhoQuantidade'];

            if ( $v['carrinhoQuantidade'] < 1 )
            {
                $qty= 1;
            }

            $data[$count]['leadPOID']             = $leadID;
            //$data[$count]['dataEmissao']          = '';
            $data[$count]['produtoPOID']          = $v['produtoPOID'];
            $data[$count]['produtoValor']         = $v['carrinhoPreco'];
            $data[$count]['produtoValorOriginal'] = $v['carrinhoPreco'];
            $data[$count]['produtoST']            = $v['carrinhoST'];
            $data[$count]['produtoIPI']           = $v['carrinhoIPI'];
            $data[$count]['produtoQuantidade']    = $qty;
            $data[$count]['produtoVezes']         = $v['carrinhoVezes'];

            $count++;
        }

        $data = json_encode($data);

        s($data);
        //die();

        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/';

        echo $json = Request::factory( $url )
            ->headers(array('Authorization' => 'Basic '.AUTH, 'Content-Type' => 'application/json'))
            ->method('POST')
            ->body($data)
            ->execute();

        return json_decode($json,true);
    }

    private function add_lead( $array, $leadID, $segmentoPOID )
    {
        //s($array, $leadID, $segmentoPOID);
        //die();

        $data  = array();
        $count = 0;

        $lead = $this->get_lead_base( $leadID );

        s($lead);
        //die();

        $count=0;

        foreach( $array as $k => $v)  
        {
            $products_ids[] = $v['produtoPOID'] ; // group product id
        }

        if ( !empty ($products_ids) )
        {
            $ids="'".implode("','",$products_ids)."'";
            $price_group = $this->get_price_group( $ids, $lead['cEmitUnity'], $lead['cCustomer'], $lead['vPaymentTerms'], $segmentoPOID );
        }

        //s($array,$price_group);
       // die();

        foreach ( $array as $k => $v )
        {
            $v['Preco']['precoVista']      = $price_group[$v["produtoPOID"]]['precoVista'];
            $v['Segmento']['segmentoNome'] = $v['segmentoNome'];
            $v['Segmento']['segmentoNCM']  = $v['segmentoNCM'];

            $impostos = $this->get_tax( $lead, $v );

             //s( $impostos );
            //die();

            $qty = $v['leadQuantidade'];

            if ( $v['leadQuantidade'] < 1 )
            {
                $qty= 1;
            }

            $data[$count]['leadPOID']             = $leadID;
            //$data[$count]['dataEmissao']        = '';
            $data[$count]['produtoPOID']          = $v['produtoPOID'];
            $data[$count]['produtoValorOriginal'] = $v['Preco']['precoVista'];

            $data[$count]['produtoValor']         = $impostos['valor_produto'];
            $data[$count]['produtoST']            = $impostos['valor_subtotal_icms_st'];
            $data[$count]['produtoIPI']           = $impostos['valor_subtotal_ipi'];

            $data[$count]['produtoQuantidade']    = $qty;
            $data[$count]['produtoVezes']         = $v['leadVezes'];

            $count++;
        }

        $data = json_encode($data);

        //s($data);
        //die();

        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/';

        echo $json = Request::factory( $url )
            ->headers(array('Authorization' => 'Basic '.AUTH, 'Content-Type' => 'application/json'))
            ->method('POST')
            ->body($data)
            ->execute();

        return json_decode($json,true);
    }
    
    private function add_shipment( $array, $leadID )
    {
        $data  = array();
        $count = 0; 

        //s($array);
        //die();

        foreach ( $array as $k => $v )
        {
            $data[$count]['leadPOID']             = $leadID;
            //$data[$count]['dataEmissao']          = '';
            $data[$count]['produtoPOID']          = $v['produtoPOID'];
            $data[$count]['produtoValor']         = $v['Produto']['produtoRevenda'];
            $data[$count]['produtoValorOriginal'] = $v['Produto']['produtoRevenda'];
            $data[$count]['produtoST']            = 0;
            $data[$count]['produtoIPI']           = 0;
            $data[$count]['produtoQuantidade']    = 1;
            $data[$count]['produtoVezes']         = 0;

            $count++;
        }

        $data = json_encode($data);

        s($data);
        //die();

        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/';

        echo $json = Request::factory( $url )
            ->headers(array('Authorization' => 'Basic '.AUTH, 'Content-Type' => 'application/json'))
            ->method('POST')
            ->body($data)
            ->execute();

        return json_decode($json,true);
    }

    private function add_order( $array, $leadID )
    {
        $data  = array();
        $count = 0; 

        //s($array);
        //die();

        foreach ( $array as $k => $v )
        {
            $data[$count]['leadPOID']             = $leadID;
            //$data[$count]['dataEmissao']          = '';
            $data[$count]['produtoPOID']          = $v['produtoPOID'];
            $data[$count]['produtoValor']         = $v['pedidoValor'];
            $data[$count]['produtoValorOriginal'] = $v['Produto']['produtoRevenda'];
            $data[$count]['produtoST']            = $v['produtoST'];
            $data[$count]['produtoIPI']           = $v['produtoIPI'];
            $data[$count]['produtoQuantidade']    = $v['pedidoProdutoQuantidade'];
            $data[$count]['produtoVezes']         = $v['pedidoProdutoVezes'];

            $count++;
        }

        $data = json_encode($data);

        s($data);
        //die();

        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/';

        echo $json = Request::factory( $url )
            ->headers(array('Authorization' => 'Basic '.AUTH, 'Content-Type' => 'application/json'))
            ->method('POST')
            ->body($data)
            ->execute();

        return json_decode($json,true);
    }

    private function add_quote( $array, $leadID )
    {
        //s($array, $leadID);
        //die();

        $data  = array();
        $count = 0; 

        foreach ( $array as $k => $v )
        {
            $data[$count]['leadPOID']             = $leadID;
            //$data[$count]['dataEmissao']          = '';
            $data[$count]['produtoPOID']          = $v['produtoPOID'];
            $data[$count]['produtoValor']         = $v['cotacaoProdutoValor'];
            $data[$count]['produtoValorOriginal'] = $v['cotacaoProdutoValor'];
            $data[$count]['produtoST']            = $v['cotacaoProdutoST'];
            $data[$count]['produtoIPI']           = $v['cotacaoProdutoIPI'];
            $data[$count]['produtoQuantidade']    = $v['cotacaoProdutoQuantidade'];
            $data[$count]['produtoVezes']         = $v['cotacaoProdutoVezes'];

            $count++;
        }

        $data = json_encode($data);

        s($data);
        //die();

        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/';

        echo $json = Request::factory( $url )
            ->headers(array('Authorization' => 'Basic '.AUTH, 'Content-Type' => 'application/json'))
            ->method('POST')
            ->body($data)
            ->execute();

        return json_decode($json,true);
    }

    private function redirecturl( $id, $segmentID)
    {
        $redirect  = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].''.$_SERVER['HTTP_X_FORWARDED_PREFIX'];
        $url = $redirect.''.$id.'/'.$segmentID.'?alert=01&title=Recalculando Lead&message=valores do produtos foram corrigidos e removidos os descontos';

        return $this->redirect($url, 302);
    }
}