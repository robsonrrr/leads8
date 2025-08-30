<?php
class Controller_Lead_Discountall extends Controller_Lead_Base {

    public function action_index()
    {
        $this->auto_render = FALSE;

        $leadID    = $this->request->param('id');
        $get       = $this->request->query();
        $segmentID = $this->request->param('segment');

        if ( ! $leadID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $leadID</h3>'));

        $data = array( 
            'check'   => false,
            'segment' => $segmentID
        );

        $json = Request::factory( '/lead/build/'.$leadID )
            ->method('GET')
            ->query($data)
            ->execute()
            ->body();

        $response = json_decode($json,true);

        // $products = self::get_products_lead( $leadID );
        //s($response);

        $segmento = $response['Lead']['segmentoPOID'];

        if ( $segmento == 2)
        {
            foreach ( $response['Lead']['Produtos'] as $k => $v)
            {
                $discount = $this->get_discount( $response, $get['discount'], $v['Classificacao']['id'] );

                $update  = self::update_lead_discount( $v, $discount);

                $product['Preco']['precoVista']      = $update['produtoValor'];
                $product["produtoPOID"]              = $v["produtoPOID"];
                $product["produtoIsentoST"]          = $v['Detalhe']["produtoIsentoST"];
                $product['Segmento']['segmentoNCM']  = $v['Detalhe']['Segmento']['segmentoNCM'];
                $product['Segmento']['segmentoNome'] = $v['Detalhe']['Segmento']['segmentoNome'];
                $product["produtoST"]                = $v['Detalhe']['produtoST'];
                $product["produtoOrigem"]            = $v['Detalhe']["produtoOrigem"];
                $product["produtoICMSantecipado"]    = $v['Detalhe']["produtoICMSantecipado"]; 
                $product["produtoQuantidade"]        = $v["produtoQuantidade"]; 
                $product["produtoFrete"]             = $v["produtoFrete"]; 

                $product['Impostos'] = $this->get_tax( $response['Lead'], $product );
                //s($product);
                // die();

                $updateLead[] = self::update_lead_price( $update, $product );
            }

            //s($updateLead);

            $redirect  = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].''.$_SERVER['HTTP_X_FORWARDED_PREFIX'];
            $url = $redirect.''.$leadID.'/'.$segmentID.'?alert=01&title=Desconto Automático&message=desconto aplicado de '.$discount.'%';
        }

        if ( $segmento == 5)
        {
            $discount = $this->get_discount( $response, $get['discount'] );

            // s($discount);
            //die();

            foreach ( $response['Lead']['Produtos'] as $k => $v)
            {
                // $discount = $this->get_discount( $response, $get['discount'], $v['Classificacao']['id'] );

                $update  = self::update_lead_discount( $v, $discount);

                $product['Preco']['precoVista']      = $update['produtoValor'];
                $product["produtoPOID"]              = $v["produtoPOID"];
                $product["produtoIsentoST"]          = $v['Detalhe']["produtoIsentoST"];
                $product['Segmento']['segmentoNCM']  = $v['Detalhe']['Segmento']['segmentoNCM'];
                $product['Segmento']['segmentoNome'] = $v['Detalhe']['Segmento']['segmentoNome'];
                $product["produtoST"]                = $v['Detalhe']['produtoST'];
                $product["produtoOrigem"]            = $v['Detalhe']["produtoOrigem"];
                $product["produtoICMSantecipado"]    = $v['Detalhe']["produtoICMSantecipado"]; 
                $product["produtoQuantidade"]        = $v["produtoQuantidade"]; 
                $product["produtoFrete"]             = $v["produtoFrete"]; 

                $product['Impostos'] = $this->get_tax( $response['Lead'], $product );
                //s($product);
                // die();

                $updateLead[] = self::update_lead_price( $update, $product );
            }

            //s($updateLead);

            $redirect  = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].''.$_SERVER['HTTP_X_FORWARDED_PREFIX'];
            $url = $redirect.''.$leadID.'/'.$segmentID.'?alert=01&title=Desconto Automático&message=desconto aplicado de '.$discount.'%';
        }

        if ( $segmento == 6)
        {
            $discount = $this->get_discount( $response, $get['discount'] );
            // s($discount);
            //die();

            foreach ( $response['Lead']['Produtos'] as $k => $v)
            {
                // $discount = $this->get_discount( $response, $get['discount'], $v['Classificacao']['id'] );

                $update  = self::update_lead_discount( $v, $discount);

                $product['Preco']['precoVista']      = $update['produtoValor'];
                $product["produtoPOID"]              = $v["produtoPOID"];
                $product["produtoIsentoST"]          = $v['Detalhe']["produtoIsentoST"];
                $product['Segmento']['segmentoNCM']  = $v['Detalhe']['Segmento']['segmentoNCM'];
                $product['Segmento']['segmentoNome'] = $v['Detalhe']['Segmento']['segmentoNome'];
                $product["produtoST"]                = $v['Detalhe']['produtoST'];
                $product["produtoOrigem"]            = $v['Detalhe']["produtoOrigem"];
                $product["produtoICMSantecipado"]    = $v['Detalhe']["produtoICMSantecipado"]; 
                $product["produtoQuantidade"]        = $v["produtoQuantidade"]; 
                $product["produtoFrete"]             = $v["produtoFrete"]; 

                $product['Impostos'] = $this->get_tax( $response['Lead'], $product );
                //s($product);
                // die();

                $updateLead[] = self::update_lead_price( $update, $product );
            }

            //s($updateLead);

            $redirect  = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].''.$_SERVER['HTTP_X_FORWARDED_PREFIX'];
            $url = $redirect.''.$leadID.'/'.$segmentID.'?alert=01&title=Desconto Automático&message=desconto aplicado de '.$discount.'%';
        }

        //echo $url ;
        //die();

        return $this->response->body($url);
    }

    /* public function get_discount( $lead )
    {
        $discount  = $this->request->param('id2');
        $fdiscount = 20;
        $ediscount = 0 ;

        $purchases  = self::get_orders( $lead['Lead']['clientePOID'] );

        if ( $purchases == 0 )
        {
            //Preço Gerencia
            if ( $discount == '0' and $_SESSION['MM_Nivel'] > 4)
                $fdiscount = 0;

            if ( $discount > 0 and $_SESSION['MM_Nivel'] > 4)
                $fdiscount = $discount;

            return $fdiscount;
        }

        $search  = array('.', ',');
        $replace = array('', '.');
        $total   = str_replace($search, $replace, $lead['Lead']['Total']);

        //https://api-ecommerce.vallery.com.br/v1/DescontoPedido?filter[where][descontoTipo]=1&filter[where][descontoSegmentoPOID]=5
        $json = Request::factory( 'https://api-ecommerce.vallery.com.br/v1/DescontoPedido' )
            ->method('GET')  
            ->query(array(
                'filter'=> array(
                    //'limit' => '1', 
                    'where' => array ( 
                        'descontoSegmentoPOID' => $lead['Lead']['segmentoPOID'],
                        //Valor
                        'descontoValorInicial' => array( 'lte' => $total['base'] ),
                        'descontoValorFinal'   => array( 'gte' => $total['base'] ),
                        //Tipo
                        'descontoTipo' => $lead['Lead']['Cliente']['clienteDescontoPedido'],
                    ))
            ))
            ->execute() 
            ->body();
        $response =  json_decode($json, true);
        //s($response);
        //die();

        //Preço Vendedor
        if ( isset($response[0]['descontoValor']) )
        {
            if ( $ediscount > $response[0]['descontoValor'] )
            {
                $ediscount = $response[0]['descontoValor'];
            }

            if (empty($discount))
            {
                $ediscount = $response[0]['descontoValor'];
            }

            if ( $discount == 'order' )
            {
                $ediscount = $response[0]['descontoValor'];
            }
        }

        //Preço Gerencia
        if ( $discount == '0' and $_SESSION['MM_Nivel'] > 4)
            $ediscount = 0;

        if ( $discount > 0 and $_SESSION['MM_Nivel'] > 4)
            $ediscount = $discount;

        //s($ediscount, $discount);
        //die();

        return $ediscount;
    } */

    private function get_product_api( $id )
    {
        $url = $_ENV["api_vallery_v1"].'/Produtos/'.$id;

        $json = Request::factory( $url )
            ->method('GET')
            ->query(array('filter' => array( 'include' => 'Segmento' , 'Classificacao' )))
            ->execute()
            ->body();

        return json_decode($json,true);
    }

    private function get_products_lead( $id )
    {
        //http://api-v1.vallery.com.br/v1/Leads/151612/Produtos/
        $url = $_ENV["api_vallery_v1"].'/Leads/'.$id.'/Produtos/';

        $json = Request::factory( $url )
            ->method('GET')
            ->execute()
            ->body();

        return json_decode($json,true);
    }

    private function update_lead_discount( $product, $discount)
    {
        $source  = array('.', ',');
        $replace = array('', '.');
        $produtoValorOriginal   = str_replace($source, $replace, $product['produtoValorOriginal'] );

        $price = round ( $produtoValorOriginal * ( 1 - ( $discount/100 ) ) , 2 );

        $data = array(
            'produtoValor' => $price,
        );

        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/'.$product['POID'];

        $json = Request::factory( $url )
            ->method('PUT')
            ->post($data)
            ->execute()
            ->body();

        return json_decode($json,true);
    }

    private function update_lead_price( $update, $product)
    {
        $data = array(
            'produtoST'  => $product['Impostos']['valor_subtotal_icms_st'],
            'produtoIPI' => $product['Impostos']['valor_subtotal_ipi'],
        );

        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/'.$update['POID'];

        $json = Request::factory( $url )
            ->method('PUT')
            ->post($data)
            ->execute()
            ->body();

        return json_decode($json,true);
    }

}