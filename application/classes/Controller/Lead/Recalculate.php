<?php
class Controller_Lead_Recalculate extends Controller_Lead_Base {

    public function action_index()
    {
        $this->auto_render = FALSE;

        $leadID    = $this->request->param('id');
        $renew     = $this->request->query('price');
        $segmentID = $this->request->param('segment');

        if ( ! $leadID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $leadID</h3>'));

        $data = array( 
            'segment' => $segmentID
        );

        $json = Request::factory( '/lead/build/'.$leadID )
            ->method('GET')
            ->query($data)
            ->execute()
            ->body();

        $response = json_decode($json,true);

        //s($response);
        //die();

        $response['query']  = $this->request->query('q');
        $response['leadID'] = $leadID;

        $count=0;

        foreach( $response['Lead']['Produtos'] as $k => $v)  
        { 
            $products_ids[] = $v['produtoPOID'] ; // group product id
        }

        if ( $renew )
        {

            self::updateLead( $leadID );

            if ( !empty ($products_ids) )
            {
                //// sub routines by group
                $ids="'".implode("','",$products_ids)."'";
                // stock
                $price_group = $this->get_price_group( $ids, $response['Lead']['unidadeEmitentePOID'], $response['Lead']['clientePOID'], false, $response['Lead']['segmentoPOID'] );
                //s($price_group);
                //die();
            }
        }

        foreach ( $response['Lead']['Produtos'] as $k => $v )
        {
            $count++;

            //s($v);
            //die();

            $array[$k]             = $v; 
            $array[$k]['item']     = $count;

            if ( $renew )
            {
                //$array[$k]['Preco']    = $this->get_price( $v['produtoPOID'], $response['Lead']['unidadeEmitentePOID'], $response['Lead']['clientePOID'], $response['Lead']['prazoPagamentoVezes'], $response['Lead'] );
                //$array[$k]['Estoque']  = $this->get_stock( $v['produtoPOID'], $response['Lead']['unidadeEmitentePOID'], $response['Lead']['clientePOID'] );
                //$product['Preco']['precoVista']      = $array[$k]['Preco']['precoVista'];
                $product['Preco']['precoVista'] = $price_group[$v["produtoPOID"]]['precoVista'];
            }else{
                $product['Preco']['precoVista'] = $v["produtoValor"];
            }

            $produtoValor = $product['Preco']['precoVista'];

            if ( $produtoValor < 1000 )
            {
                $search  = array(',');
                $replace = array('.');
                $product['Preco']['precoVista'] = str_replace($search, $replace, $produtoValor);
            }else{
                $search  = array('.', ',');
                $replace = array('', '.');
                $product['Preco']['precoVista'] = number_format( $produtoValor , 2, '.', '');
            }

            //s($product['Preco']['precoVista'] );
            //die();

            //$array[$k]['Estoque']  = $this->get_stock( $v['produtoPOID'], $response['Lead']['unidadeEmitentePOID'], $response['Lead']['clientePOID'] );

            $product["produtoPOID"]              = $v["produtoPOID"];
            $product["produtoIsentoST"]          = $v['Detalhe']["produtoIsentoST"];
            $product['Segmento']['segmentoNCM']  = $v['Detalhe']['Segmento']['segmentoNCM'];
            $product['Segmento']['segmentoNome'] = $v['Detalhe']['Segmento']['segmentoNome'];
            $product["produtoST"]                = $v['Detalhe']['produtoST'];
            $product["produtoOrigem"]            = $v['Detalhe']["produtoOrigem"];
            $product["produtoICMSantecipado"]    = $v['Detalhe']["produtoICMSantecipado"]; 
            $product["produtoQuantidade"]        = $v["produtoQuantidade"]; 
            
            if ( isset($v["produtoFrete"] ))
            {
                $product["produtoFrete"] = $v["produtoFrete"]; 
            }

            //s($v, $product);
            //die();

            $array[$k]['Impostos'] = $this->get_tax( $response['Lead'], $product );

            self::update( $leadID, $v['produtoPOID'], $array[$k], $v, $renew);
        }

        //s($array);
        //die();

        $redirect  = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].''.$_SERVER['HTTP_X_FORWARDED_PREFIX'];
        $url = $redirect.''.$leadID.'/'.$segmentID.'?alert=01&title=Recalculando Lead&message=valores do produtos foram corrigidos e removidos os descontos e a data de emissão foi renovada';

        if ( $this->request->query('redirect') )
        {
            $url = $redirect.''.$leadID.'/'.$segmentID.'?alert=01&title='.$this->request->query('title').'&message='.$this->request->query('message').'';

            return HTTP::redirect($url, '302');
        }

        return $this->response->body($url);
    }

    private function update( $lead, $product, $data, $cart, $renew)
    {
        if ( $renew )
        {
            $post['produtoValorOriginal'] = $data['Impostos']['valor_produto'];
        }

        $post['produtoValor']         = $data['Impostos']['valor_produto'];
        $post['produtoST']            = $data['Impostos']['valor_subtotal_icms_st'];
        $post['produtoIPI']           = $data['Impostos']['valor_subtotal_ipi'];
        $post['produtoDifal']         = $data['Impostos']['valor_subtotal_difal'];
        $post['produtoVezes']         = 0;

        // s($post,$data['Impostos']);
        // die();

        $where = array(
            'where' => array(
                'leadPOID'    => $lead,
                'produtoPOID' => $product 
            ));

        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/update';

        //s($post);
        //s($data,$where);
        //die();

        $json = Request::factory($url)
            ->method('POST')
            ->post($post)
            ->query($where)
            ->execute()
            ->body(); 

        //s($json);
        //die();

        return $this->response->body($json);
    }

    private function updateLead( $lead)
    {
        //s($data);
        //die();

        $where = array(
            'where' => array(
                'leadPOID'    => $lead,
            ));

        $post = array(
            'dataEmissao' => date('Y-m-d G:i:s'),
        );

        $url = $_ENV["api_vallery_v1"].'/Leads/update';

        //s($data,$where);
        //die();

        $json = Request::factory($url)
            ->method('POST')
            ->post($post)
            ->query($where)
            ->execute()
            ->body(); 

        //s($json);
        //die();

        return $this->response->body($json);
    }
}