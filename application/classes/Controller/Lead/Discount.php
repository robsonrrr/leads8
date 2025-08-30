<?php
class Controller_Lead_Discount extends Controller_Lead_Base {

    public function action_index()
    {
        $this->auto_render = FALSE;

        $get  = $this->request->query();

        //s($get);
        //die();

        $leadID    = $get['lead'];
        $segmentID = $get['segment'];

        $data = array( 
            'segment' => $segmentID
        );

        if ( ! $leadID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $leadID</h3>'));

        $json = Request::factory( '/lead/build/'.$leadID )
            ->method('GET')
            ->query($data)
            ->execute()
            ->body();

        $lead = json_decode($json,true);

        //s($lead);
        //die();

        $POID = $get['leadProduct'];

        if ( $get['type'] == 'price' )
        {
            $update = self::update_product_price( $get['leadProduct'], $get['discount'] );
            $this->log( 'icart', 'vProductCC', 'cCart', $get['leadProduct'], 0, $get['discount']);
        }

        if ( $get['type'] == 'pricecc' )
        {
            $update = self::update_product_pricecc( $get['leadProduct'], $get['discount'] );
            $this->log( 'icart', 'vProductCC', 'cCart', $get['leadProduct'], 0, $get['discount']);
        }

        $product = self::get_product_lead( $POID );
        //s($product);

        if ( $get['type'] == 'discount' )
        {
            $discount = $this->get_discount( $lead, $get['discount'], $get['classe'] );

            //s($discount);
            //die();

            $update   = self::update_lead_discount($product,$discount);
            //$this->log( 'icart', 'vProductDiscount', 'cCart', $get['leadProduct'], 0, $get['discount']);
        }

        //s($update);

        $response = json_decode($json,true);
        //s($response);

        $product = self::get_product_api( $update['produtoPOID'] );
        //s($update);
        //die();

        $product['produtoQuantidade']   = $update['produtoQuantidade'];
        $product['Preco']['precoVista'] = $update['produtoValor'];
        //s($product);
        //die();

        $product['Impostos'] = $this->get_tax( $lead['Lead'], $product );
        //s($product);

        $update = self::update_product_tax( $update, $product );
        //s($update);

        return $this->response->body(json_encode(true));
    }

    private function get_product_lead( $POID )
    {
        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/'.$POID;

        $json = Request::factory( $url )
            ->method('GET')
            ->execute()
            ->body();

        return json_decode($json,true);
    }

    private function get_product_api( $id )
    {
        $url = $_ENV["api_vallery_v1"].'/Produtos/'.$id;

        $json = Request::factory( $url )
            ->method('GET')
            ->query(array('filter' => array( 'include' => 'Segmento' )))
            ->execute()
            ->body();

        return json_decode($json,true);
    }

    private function update_product_price( $leadProduct, $price)
    {
        if ( $price == 0 )
        {
            return true;
        }

        if ( 6 == $_SESSION['MM_CompanyId'] and $_SESSION['MM_Nivel'] < 3  )
        {
            return true;
        }

        if  ( $_SESSION['MM_Depto'] == 'VENDAS' and 1 == $_SESSION['MM_Nivel'] )
        {
            $url = $_ENV["api_vallery_v1"].'/LeadProdutos/'.$leadProduct;

            $json = Request::factory( $url )
                ->method('GET')
                ->execute()
                ->body();

            $product = json_decode($json,true);

            if ( $price < $product['produtoValor'] )
            {
                exit;
            }
        } 

        $search  = array('.', ',');
        $replace = array('.', '.');
        $price   = str_replace($search, $replace, $price);

        $data = array(
            'produtoValor' => $price,
        );

        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/'.$leadProduct;

        $json = Request::factory( $url )
            ->method('PUT')
            ->post($data)
            ->execute()
            ->body();

        return json_decode($json,true);
    }

    private function update_product_pricecc( $leadProduct, $price)
    {
        if ( $price == 0 )
        {
            return true;
        }

        $search  = array('.', ',');
        $replace = array('.', '.');
        $price   = str_replace($search, $replace, $price);

        $data = array(
            'produtoValorClientedeCliente' => $price,
        );

        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/'.$leadProduct;

        $json = Request::factory( $url )
            ->method('PUT')
            ->post($data)
            ->execute()
            ->body();

        return json_decode($json,true);
    }

    private function update_lead_discount( $product, $discount)
    {
        $produtoValorOriginal = str_replace(',', '', $product['produtoValorOriginal']);

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

    private function update_product_tax( $update, $product)
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