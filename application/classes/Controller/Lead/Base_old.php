<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Lead_Base extends Controller_Website  {

    public function get_orders( $id )
    {
        $sql = sprintf(" SELECT count(id) as x, segmentoPOID
						FROM  mak.`hoje`  
						WHERE idcli='%s'
                        AND nop=27 
                        AND datae BETWEEN NOW() - INTERVAL 1000 DAY AND NOW()
						LIMIT 1", $id);

        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute()->as_array(); 

        //s($result);

        if(count($result)>0)
        {
            return $result[0]['x'];	
        }else{
            return 0;
        } 
    }

    public function get_lead_base( $leadID )
    {
        $lead = null;

        if ( empty( $leadID ))
        {
            return $lead;
        }

        $sql= sprintf("SELECT sCart.*, nop.id_nop, nop.cfop, Emitentes.EmitentePOID, Emitentes.UF, Emitentes.Fantasia as local,
                              clientes.tipo_pessoa, clientes.isento_ipi, clientes.isento_st, clientes.estado, clientes.cnae, clientes.inscr,
                              clientes.icms, clientes.regime_especial, clientes.suframa, clientes.regime_fiscal
                         FROM sCart 
                         LEFT JOIN nop on (nop.id_nop=sCart.cNatOp)
                         LEFT JOIN Emitentes on (Emitentes.EmitentePOID=sCart.cEmitUnity)
                         LEFT JOIN clientes on (clientes.id=sCart.cCustomer)
                         WHERE 1=1 AND cSCart = %s 
                         LIMIT 1 ", $leadID );

        $query = DB::query(Database::SELECT, $sql);
        $lead = $query->execute()->as_array();

        if ( isset( $lead[0]) )
        {
            $lead = $lead[0];
            $lead['Natureza']['id']                      = $lead['id_nop'];
            $lead['Natureza']['naturezaOperacaoCFOP']    = $lead['cfop'];
            $lead['Emitente']['emitenteUF']              = $lead['UF'];
            $lead['Cliente']['clienteTipoPessoa']        = $lead['tipo_pessoa'];
            $lead['Cliente']['clienteIsentoIPI']         = $lead['isento_ipi'];
            $lead['Cliente']['clienteIsentoSt']          = $lead['isento_st'];
            $lead['Cliente']['clienteEstado']            = $lead['estado'];
            $lead['Cliente']['clienteCnae']              = $lead['cnae'];
            $lead['Cliente']['clienteInscricaoEstadual'] = $lead['inscr'];
            $lead['Cliente']['clienteICMSSTEspecial']    = $lead['icms'];
            $lead['Cliente']['clienteRegimeFiscal']      = $lead['regime_fiscal'];
            $lead['Cliente']['clienteSuframa']           = $lead['suframa'];
        }

        return $lead;
    }

    public function get_solr_query( $id, $segmento = false)
    {
        //return array();

        $fl= "id,produto*,segmento*,categoria*,catalogoSimilarPreferido,catalogoSimilarPDF,catalogoOriginalPDFmarcaSeo";

        $fq = null;

        if ( $segmento )
        {
            $fq="&fq=segmentoPOID:".$segmento;
        }

        $url = "https://www.rolemak.com.br/solr/catalogo/select?q=produtoPOID:".$id."&fl=".$fl."&indent=on&wt=json&rows=1".$fq;

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
 
    public function get_discount( $lead, $discount, $produtoClasssificacao = false)
    {   
        $segmento = $lead['Lead']['segmentoPOID'];

        //s( $lead, $discount, $produtoClasssificacao);
        //die();

        if ( 2 == $segmento )
        {
            //Desconto Progressivo por Pedido
            if  ( $discount == "order" )
            {
                return 0;
            }

            if  ( $discount >= 0 )
            {
                //s($lead, $discount, $produtoClasssificacao);

                //Diretoria
                if  ( $discount > 0 and ( $_SESSION['MM_Userid'] == '77' or $_SESSION['MM_Userid'] == '46' or $_SESSION['MM_Userid'] == '111' or $_SESSION['MM_Userid'] == '2') )
                {
                    return $discount;
                }

                if ( $produtoClasssificacao > 0 )
                {
                    $url = $_ENV["api_vallery_v1"].'/ProdutoClassificacao/'.$produtoClasssificacao;

                    $json = Request::factory( $url )
                        ->method('GET')
                        ->query(array(
                            'filter'=> array(
                                'where' => array ( 
                                    'classificacaoAtivo' => 1,
                                ))
                        ))
                        ->execute()
                        ->body();

                    $response =  json_decode($json, true);

                    //s($response);
                    //die();

                    if  ( $_SESSION['MM_Depto'] == 'VENDAS' and $_SESSION['MM_Nivel'] == 1 or ( $_SESSION['MM_Userid'] == 84 ) )
                    {
                        $leadVezes = $lead['Lead']['prazoPagamentoVezes'];

                        $classificacaoDescontoVendedor = $response['classificacaoDescontoVendedor'];

                        if ( isset($leadVezes) and  13 == $leadVezes)
                        {
                            $classificacaoDescontoVendedor += 5;
                        }

                        if ( $discount > $classificacaoDescontoVendedor )
                        {
                            $discount = $classificacaoDescontoVendedor;
                        }
                    }

                    if  ( $_SESSION['MM_Depto'] == 'VENDAS' and $_SESSION['MM_Nivel'] > 2 )
                    {
                        //s($response);

                        if ( $discount > $response['classificacaoDescontoGerente'] )
                        {
                            $discount = $response['classificacaoDescontoGerente'];
                        }
                    }

                }

                return $discount;
            }
        }

        if ( 5 == $segmento )
        {
            //Desconto Progressivo por Pedido
            if  ( $discount == "order" )
            {
                $first = self::get_orders( $lead['Lead']['Cliente']['clienteID'] );

                if ( $first == 0)
                {
                    return 0;
                }

                $cdiscount = $lead['Lead']['Cliente']['clienteDescontoMakAutomotive'] + $lead['Lead']['Cliente']['clienteDesconto'];

                if  ( $cdiscount > 0)
                {
                    return 0;
                }

                $discount = 0;
                $maxdiscount = 0;

                $search  = array('.', ',');
                $replace = array('', '.');
                $total   = str_replace($search, $replace, $lead['Lead']['Total']);

                $json = Request::factory( $_ENV['api_ecommerce'].'/DescontoPedido')
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

                if ( isset( $response[0]['descontoValor'] ) )
                {
                    return $response[0]['descontoValor'];
                }

                if  ( $_SESSION['MM_Depto'] == 'VENDAS' and $_SESSION['MM_Nivel'] == 1 or ( $_SESSION['MM_Userid'] == 84 ) )
                {
                    $leadVezes = $lead['Lead']['prazoPagamentoVezes'];

                    $classificacaoDescontoVendedor = 0;

                    if ( isset($leadVezes) and  13 == $leadVezes)
                    {
                        $classificacaoDescontoVendedor += 3;
                    }

                    if ( $discount > $classificacaoDescontoVendedor )
                    {
                        $discount = $classificacaoDescontoVendedor;
                    }
                }

                if ( empty($discount) )
                {
                    $discount = $maxdiscount;
                }

                return $discount;
            }

            //Desconto pro Produto
            if  ( $discount >= 0 )
            {
                //Diretoria
                if  ( $discount > 0 and ( $_SESSION['MM_Userid'] == '34' or $_SESSION['MM_Nivel'] > 4 ) )
                {
                    return $discount;
                }

                //Primeira compra
                $first = self::get_orders( $lead['Lead']['Cliente']['clienteID'] );

                if ( $first == 0)
                {
                    return 0;
                }

                //Se já tem desconto direto aplicado
                $cdiscount = $lead['Lead']['Cliente']['clienteDescontoMakAutomotive'] + $lead['Lead']['Cliente']['clienteDesconto'];

                if  ( $cdiscount > 0)
                {
                    return 0;
                }

                $maxdiscount = 0;

                $search  = array('.', ',');
                $replace = array('', '.');
                $total   = str_replace($search, $replace, $lead['Lead']['Total']);

                $json = Request::factory( $_ENV['api_ecommerce'].'/DescontoPedido')
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

                if ( isset( $response[0]['descontoValor'] ) )
                {
                    $maxdiscount = $response[0]['descontoValor'];

                    if  ( $discount > $maxdiscount )
                    {
                        $discount = $maxdiscount;
                    }

                    //s($discount);
                    //die();

                    return $discount;
                }else{
                    $discount = 0;
                }

                if ( empty($discount) )
                {
                    $discount = $maxdiscount;
                }
            }
        }

        if ( 6 == $segmento )
        {
            //Desconto Progressivo por Pedido
            if  ( $discount == "order" )
            {
                $nordeste = array("MA", "PI", "CE", "RN", "PB", "PE", "AL", "SE", "BA", "TO", "PA", "AP", "RR", "AM", "AC", "RO", "DF", "GO", "MS", "MT"); 

                if (in_array( $lead['Lead']['Cliente']['clienteEstado'], $nordeste))
                { 
                    $tipo = 1;
                }

                $saopaulo = array("SP"); 

                if (in_array( $lead['Lead']['Cliente']['clienteEstado'], $saopaulo))
                { 
                    $tipo = 2;
                }

                $sudeste = array("RJ", "MG", "ES", "PR", "SC", "RS"); 

                if (in_array( $lead['Lead']['Cliente']['clienteEstado'], $sudeste)) 
                { 
                    $tipo = 3;
                }

                $cdiscount =  $lead['Lead']['Cliente']['clienteDesconto'];

                if  ( $cdiscount > 0)
                {
                    return 0;
                }

                $discount = 0;
                $maxdiscount = 0;

                $search  = array('.', ',');
                $replace = array('', '.');
                $total   = str_replace($search, $replace, $lead['Lead']['Total']);

                $json = Request::factory( $_ENV['api_ecommerce'].'/DescontoPedido')
                    ->method('GET')  
                    ->query(array(
                        'filter'=> array(
                            //'limit' => '1', 
                            'where' => array ( 
                                'descontoSegmentoPOID' => $segmento,
                                //Valor
                                'descontoValorInicial' => array( 'lte' => $total['base'] ),
                                'descontoValorFinal'   => array( 'gte' => $total['base'] ),
                                //Tipo
                                'descontoTipo' => $tipo,
                            ))
                    ))
                    ->execute() 
                    ->body();
                $response =  json_decode($json, true);

                //s($response);


                if ( isset( $response[0]['descontoValor'] ) )
                {
                    $descontoValor = $response[0]['descontoValor'];

                    if ( $lead['Lead']['Cliente']['Gerente']['UsuarioCargo'] <> "Representante" )
                    {
                        $descontoValor+=5;
                    }

                    return $descontoValor;
                }

                if ( empty($discount) )
                {
                    $discount = $maxdiscount;
                }

                return $discount;
            }

            //Desconto pro Produto
            if  ( $discount >= 0 )
            {
                //Diretoria
                if  ( $discount > 0 and ( $_SESSION['MM_Userid'] == '73' or $_SESSION['MM_Nivel'] > 4 ) )
                {
                    return $discount;
                }

                $nordeste = array("MA", "PI", "CE", "RN", "PB", "PE", "AL", "SE", "BA", "TO", "PA", "AP", "RR", "AM", "AC", "RO", "DF", "GO", "MS", "MT"); 

                if (in_array( $lead['Lead']['Cliente']['clienteEstado'], $nordeste))
                { 
                    $tipo = 1;
                }

                $saopaulo = array("SP"); 

                if (in_array( $lead['Lead']['Cliente']['clienteEstado'], $saopaulo))
                { 
                    $tipo = 2;
                }

                $sudeste = array("RJ", "MG", "ES", "PR", "SC", "RS"); 

                if (in_array( $lead['Lead']['Cliente']['clienteEstado'], $sudeste)) 
                { 
                    $tipo = 3;
                }

                //Primeira compra
                //$first = self::get_orders( $lead['Lead']['Cliente']['clienteID'] );

                //if ( $first == 0)
                //{
                //return 0;
                //}

                //Se já tem desconto direto aplicado
                $cdiscount = $lead['Lead']['Cliente']['clienteDesconto'];

                if  ( $cdiscount > 0)
                {
                    return 0;
                }

                $maxdiscount = 0;

                $search  = array('.', ',');
                $replace = array('', '.');
                $total   = str_replace($search, $replace, $lead['Lead']['Total']);

                $json = Request::factory( $_ENV['api_ecommerce'].'/DescontoPedido')
                    ->method('GET')  
                    ->query(array(
                        'filter'=> array(
                            //'limit' => '1', 
                            'where' => array ( 
                                'descontoSegmentoPOID' => $segmento,
                                //Valor
                                'descontoValorInicial' => array( 'lte' => $total['base'] ),
                                'descontoValorFinal'   => array( 'gte' => $total['base'] ),
                                //Tipo
                                'descontoTipo' => $tipo,
                            ))
                    ))
                    ->execute() 
                    ->body();
                $response =  json_decode($json, true);
                //s($response);
                //die();

                if ( isset( $response[0]['descontoValor'] ) )
                {
                    $maxdiscount = $response[0]['descontoValor'];

                    if ( $lead['Lead']['Cliente']['Gerente']['UsuarioCargo'] <> "Representante" )
                    {
                        $maxdiscount+=5;
                    }

                    if  ( $discount > $maxdiscount )
                    {
                        $discount = $maxdiscount;
                    }

                    //s($discount);
                    //die();

                    return $discount;
                }

                if ( empty($discount) )
                {
                    $discount = $maxdiscount;
                }
            }
        }

        return $discount;
    }

    public function get_realtime( $product, $lead )
    {
        //s( $product, $lead );
        //die();

        $array = array();
        // if ( empty($solr))
        // return $array;

        $count=0;

        foreach ( $product as $k => $v )
        {
            $count++;
            $array[$k] = $v;
            $array[$k]['marcaSeo'] = $this->clean_string( $v['produtoMarca'] );
            $array[$k]['item'] = $count;
        }

        foreach ( $array as $k => $v )
        {
            $array[$k]['Preco']      = $this->get_price( $v['produtoPOID'], $lead['unidadeEmitentePOID'], $lead['clientePOID'], $lead['prazoPagamentoVezes'], $lead );

            //s($array[$k]['Preco']);
            //die();

            $array[$k]['precoVista'] = $array[$k]['Preco']['precoVista'];
            $array[$k]['Estoque']    = $this->get_stock( $v['produtoPOID'], $lead['unidadeEmitentePOID'], $lead['clientePOID'] );
            $array[$k]['Impostos']   = $this->get_tax( $lead, $array[$k] );

            // s($array[$k]['Impostos']);

            if ( ! empty($array[$k]['Impostos'] ))
            {
                $array[$k]['Impostos']['valor_subtotal_produto_com_impostos'] = $this->formatPrice( $array[$k]['Impostos']['valor_subtotal_produto_com_impostos'] );
                $array[$k]['Impostos']['valor_subtotal_produto']              = $this->formatPrice( $array[$k]['Impostos']['valor_subtotal_produto'] );
                $array[$k]['Impostos']['valor_subtotal_icms_st']              = $this->formatPrice( $array[$k]['Impostos']['valor_subtotal_icms_st'] );
                $array[$k]['Impostos']['valor_subtotal_ipi']                  = $this->formatPrice( $array[$k]['Impostos']['valor_subtotal_ipi'] );
            }
        }

        //s($array);
        //die();

        return $array;
    }

    public function get_tax( $lead, $product )
    {
        //s($product);
        //die();

        $item    = 0;
        $qty     = 1;
        $freight = 0;

        if ( isset($product['item']) )
        {
            $item = $product['item'];
        }

        if ( isset($product['produtoQuantidade']) )
        {
            $qty = $product['produtoQuantidade'];
        }

        if ( isset($product['leadQuantidade']) )
        {
            $qty = $product['leadQuantidade'];
        }

        if ( isset($product['produtoFrete']) )
        {
            $freight = $product['produtoFrete'];
        }

        $arr["nopID"]                   = $lead['Natureza']["id"];
        $arr["naturezaOperacaoCFOP"]    = $lead['Natureza']["naturezaOperacaoCFOP"];
        $arr["emitenteUF"]              = $lead['Emitente']["emitenteUF"];

        $arr["tipoPessoa"]              = $lead['Cliente']["clienteTipoPessoa"];
        $arr["destinatarioIsentoIPI"]   = $lead['Cliente']["clienteIsentoIPI"];
        $arr["destinatarioIsentoST"]    = $lead['Cliente']["clienteIsentoSt"];
        $arr["destinatarioUF"]          = $lead['Cliente']["clienteEstado"];   
        $arr["destinatarioCNAE"]        = $lead['Cliente']["clienteCnae"];   
        $arr["destinatarioIE"]          = $lead['Cliente']["clienteInscricaoEstadual"];
        $arr["clienteICMSSTEspecial"]   = $lead['Cliente']["clienteICMSSTEspecial"];
        $arr["regimeFiscal"]            = $lead['Cliente']["clienteRegimeFiscal"];
        $arr["suframa"]                 = $lead['Cliente']["clienteSuframa"];

        $arr['item']                    = $item;
        $arr["produtoValor"]            = $product['Preco']['precoVista'];
        $arr["produtoIsentoST"]         = $product["produtoIsentoST"];
        $arr["produtoTTD"]              = $product["produtoPOID"];
        $arr["produtoNCM"]              = $product['Segmento']['segmentoNCM'];
        $arr["segmentoNome"]            = $product['Segmento']['segmentoNome'];
        $arr["produtoST"]               = $product["produtoST"];
        $arr["produtoOrigem"]           = $product["produtoOrigem"];
        $arr["produtoICMSantecipado"]   = $product["produtoICMSantecipado"];  
        $arr["produtoQtd"]              = $qty;
        $arr["frete"]                   = $freight; 

        $json = Request::factory( 'http://vallery-tax/index/' )
            ->method('POST')
            ->post($arr)
            ->execute()
            ->body();

        $tax = json_decode($json,true);
        //s($tax, $arr);

        if(is_array($tax))
            return $tax;

        //return array();

        if ( $_SESSION['MM_Userid'] == 84 )
        {
            //s($json);
            //throw new  exception('Imposto não calculado produto ID'.$product["produtoPOID"]);
        }

        //s($json);
        //throw new  exception('Imposto não calculado produto ID'.$product["produtoPOID"]);

        return true;
    }

    public function get_price( $product, $unity = false, $customer, $terms= false , $lead = false, $segmentID = false)
    {
        // s( $product, $unity, $customer, $terms , $lead);
        //die();

        $data = array();

        $desconto = 0;

        //and $lead['Cliente']['clientePrazo'] > 0
        if ( isset($lead['segmentoPOID']) and 13 == $terms and $lead['segmentoPOID'] == 5  )
        {
            // $desconto = 3;
        }

        //if ( isset($lead['segmentoPOID']) and 13 == $terms and $lead['segmentoPOID'] == 2 and $lead['Cliente']['clientePrazo'] > 0 )
        //{
        // $desconto = 5;
        //}

        if ( $unity )
        {
            $data = array(
                'product'           => $product,
                'unity'             => $unity,
                'discount_website'  => $desconto,
                'segment'           => $segmentID,
            );
        }

        $request = Request::factory('http://vallery-price/'.$product.'/'.$customer )
            ->method('POST')
            ->post($data)
            ->execute()
            ->body();

        $response = json_decode( $request, true);

        if ( $_SESSION['MM_Userid'] == 84 )
        {
            //s($product,$customer);
            //echo $request;
        }

        //s($response);
        //die();

        if( isset($response) )
        {
            //if ( $response['precoVistaFormatado'] == 0)
            //$this->error.=' Erro ao pegar os preços produto ID:'.$product;

            return $response;
        }else{
            return array();
        }
    }

    public function get_price_group( $ids, $unity, $customer, $terms = false, $segmentID = false )
    {
        //s( $ids, $unity, $customer, $segmentID);
        //die();

        $data = array(
            'segment' => $segmentID,
        );

        $url = sprintf( 'http://vallery-price/group/%s/%s?ids=%s', $unity, $customer, $ids);

        $request = Request::factory( $url )
            ->method('POST')
            ->post($data)
            ->execute()
            ->body();

        $response = json_decode( $request, true);

        if ( $_SESSION['MM_Userid'] == 84 )
        {
            //s($product,$customer);
            //echo $request;
            //die();
        }

        //echo $request;
        //die();

        if( isset($response) )
        {
            //if ( $response['precoVistaFormatado'] == 0)
            //$this->error.=' Erro ao pegar os preços produto ID:'.$product;

            return $response;
        }else{
            return array();
        }
    }


    public function get_stock_group(  $ids, $unity=1 )
    {
        $url = sprintf( 'http://vallery-stock/group/%s/%s?ids=%s' ,0,$unity, $ids);

        //s($unity);
        //die();

        $json = Request::factory( $url )
            ->method('GET')
            ->execute()
            ->body();

        $response = json_decode($json,true);

        //s($response);
        //die();

        return $response;
    }

    public function get_stock( $id, $unity = false )
    {
        //s($unity);
        //die();

        if ( $unity )
        {
            $url = 'http://vallery-stock/get/'.$id.'/'.$unity;
        }else{
            $url = 'http://vallery-stock/get/'.$id;
        }

        $request = Request::factory( $url )
            ->method('POST')
            ->execute()
            ->body();  

        $response = json_decode( $request, true);

        //s($response);
        //die();

        if( isset($response)  )
        {
            return $response;
        }else{
            return array();
        }
    }


}