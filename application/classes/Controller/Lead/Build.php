<?php
class Controller_Lead_Build extends Controller_Lead_Base{

    private $leadID;
    private $check;
    private $segmentID;
    private $page;

    private function get()
    {
        $this->leadID    = $this->request->param('id');

        if ( ! $this->leadID )
            die(s('Favor Informar $leadID'));

        $json = Request::factory( '/lead/get/'.$this->leadID )
            ->method('GET')
            ->execute()
            ->body();

        $data = json_decode( $json, true);

        return $data;
    }

    public function action_index()
    {
        // Start a new benchmark
        $benchmark = Profiler::start('Build', __FUNCTION__);

        $lead = self::get();

        if ( ! isset( $lead['data']['Lead'] ) )
            die( sprintf('<h3 align="center" style="margin-top:100px">Lead No.%s já foi deletado do sistema!</h3>', $this->leadID));

        $this->check     = $this->request->query('check');
        $this->segmentID = $this->request->param('segment');
        $this->page      = $this->request->query('page');

        if ( ! isset( $this->segmentID ) )
        {
            die('Sem segmento');
        }

        $array = null;

        foreach ( $lead['data'] as $k => $v )
        {
            $array[$k] = $v;
            //s($v);
            //die();

            if ( '2' == $v['tipoFrete'] and isset($v['tipoFrete']) )
                $array[$k]['CIF'] = true;

            if ( '1' == $v['tipoFrete'] and isset($v['tipoFrete']) )
                $array[$k]['FOB'] = true;

            $d0 = date('Y-m-d');
            $d1 = $v['dataEntrega'];

            if( ($d1) < ($d0))
            {
                $array[$k]['dataEntrega'] = $d0;
                $array[$k]['dataEntregaFormatado'] = date_format( date_create( $d0 ) , 'Y-m-d');
            }else{
                $array[$k]['dataEntrega'] = $d1;
                $array[$k]['dataEntregaFormatado'] = date_format( date_create( $d1 ) , 'Y-m-d');
            }

            $now = time(); // or your date as well
            $your_date = strtotime($v['dataEmissao']);
            $datediff = $now - $your_date;
            $array[$k]['dataEmissaoDias'] = round($datediff / (60 * 60 * 24));

            if ( $array[$k]['dataEmissaoDias'] > 7 and 0 == $array[$k]['leadAutorizado']  )
            {
                $array['Lead']['existPendencies'] = 'Lead criado a '.$array[$k]['dataEmissaoDias'].' dias, Lead vencido favor criar outro';
            }

            $array[$k]['dataEmissaoFormatado'] = date_format( date_create( $v['dataEmissao']) , 'd/m/Y H:i:s');

            $array[$k]['Cliente'] = $v['Cliente']['edges'][0]['node'];

            if ( $array[$k]['Cliente']['clienteTipoPessoa'] <> 'F' and $array[$k]['Cliente']['clienteTipoPessoa'] <> 'J' )
            {
                $array['Lead']['existPendencies'] = 'Favor corrigir cadastro campo clienteTipoPessoa -> '.$array[$k]['Cliente']['clienteTipoPessoa'].', valores permitidos';
            }

            if ( $array[$k]['Cliente']['clienteRegimeFiscal'] == 0 )
            {
                $array['Lead']['existPendencies'] = 'Favor corrigir cadastro campo clienteRegimeFiscal está indefinido';
            }

            if (isset($array[$k]['Cliente']['Gerente']['edges'][0]['node']))
                $array[$k]['Cliente']['Gerente'] = $array[$k]['Cliente']['Gerente']['edges'][0]['node'];

            if (isset($array[$k]['Cliente']['Segmento']['edges'][0]['node']))
                $array[$k]['Cliente']['Segmento'] = $array[$k]['Cliente']['Segmento']['edges'][0]['node'];

            if (isset($array[$k]['Cliente']['Hierarquia']['edges'][0]['node']))
                $array[$k]['Cliente']['Hierarquia'] = $array[$k]['Cliente']['Hierarquia']['edges'][0]['node'];

            $array[$k]['Natureza'] = $v['Natureza']['edges'][0]['node'];

            //Transportadora
            if ( isset($v['Transportadora']['edges'][0]['node']))
            {
                $array[$k]['Transportadora'] =  $v['Transportadora']['edges'][0]['node'];
            }

            //TipoPagamento
            if ( isset($v['TipoPagamento']['edges'][0]['node']))
            {
                $array[$k]['TipoPagamento'] = $v['TipoPagamento']['edges'][0]['node'];

                //Pagamento
                $array[$k]['TipoPagamento'][$array['Lead']['TipoPagamento']['pagamentoDescricao']] = true;
            }

            if ( isset($v['Pagamentos']['edges'][0]['node']))
                $array[$k]['Pagamentos'] = $v['Pagamentos']['edges'][0]['node'];

            if ( isset($v['Prazo']['edges'][0]['node']))
            {
                $array[$k]['Prazo'] = $v['Prazo']['edges'][0]['node'];

                //Pagamento
                $array[$k]['Prazo'][$array['Lead']['Prazo']['PrazoTermos']] = true;
            }

            $array[$k]['Emitente']           = $v['Emitente']['edges'][0]['node'];
            //$array[$k]['UnidadeLogistica']   = $v['UnidadeLogistica']['edges'][0]['node'];

            if ( isset($v['Vendedor']['edges'][0]['node']))
                $array[$k]['Vendedor'] = $v['Vendedor']['edges'][0]['node'];

            $array[$k]['Produtos'] = $v['Produtos']['LeadProdutos'];
        }


        $array['Lead']['Emitente']['emitenteCnpjFormatado'] = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $array['Lead']['Emitente']['emitenteCnpj'] );


        $array['Lead']['Emitente']['API'] = 'Estoque'.substr($array['Lead']['Emitente']['emitenteCnpj'] ,10,4);
        $array['Lead']['Emitente']['DB']  = 'mak_'.substr($array['Lead']['Emitente']['emitenteCnpj'] ,10,4);
        $array['Lead']['Financeiro']      = self::pending( $array[$k]['Cliente']['id'] );
        $array['Lead']['Ticket']          = self::ticket( $array[$k]['Cliente']['id'] );

        $array['Lead']['check'] =  $this->check;
        $array['Lead']['page'] =  $this->page;

        if ( $this->segmentID )
        {
            $array['Lead']['segmentoPOID'] =  $this->segmentID;

            switch ( $this->segmentID )
            {
                case "1":
                    $array['Lead']['machines'] = true;
                    $array['Lead']['segmento'] = 'machines';
                    $array['Lead']['segmentoNome'] = 'Máquinas';
                    break;
                case "2":
                    $array['Lead']['bearings'] = true;
                    $array['Lead']['segmento'] = 'bearings';
                    $array['Lead']['segmentoNome'] = 'Rolamentos';
                    break;
                case "3":
                    $array['Lead']['parts']    = true;
                    $array['Lead']['segmento'] = 'parts';
                    $array['Lead']['segmentoNome'] = 'Peças e Acessórios';
                    break;
                case "4":
                    $array['Lead']['faucets']  = true;
                    $array['Lead']['segmento'] = 'faucets';
                    $array['Lead']['segmentoNome'] = 'Metais';
                    break;
                case "5":
                    $array['Lead']['auto']     = true;
                    $array['Lead']['segmento'] = 'auto';
                    $array['Lead']['segmentoNome'] = 'Autopeças';
                    break;
                case "6":
                    $array['Lead']['moto']     = true;
                    $array['Lead']['segmento'] = 'moto';
                    $array['Lead']['segmentoNome'] = 'Motopeças';
                    break;
            }
        }else{
            $array['Lead'][$_SESSION['MM_Segment']] = true;
            $array['Lead']['segmento']   = $_SESSION['MM_Segment'];
            $array['Lead']['segmentoPOID'] = 'all';
        }


        //s($array);
        //die();

        $quantidade  = 0;
        $variedade   = 0;
        $peso        = 0;
        $pedido      = 0;
        $produto     = 0;
        $produtocc   = 0;
        $base        = 0;
        $ipi         = 0;
        $st          = 0;
        $difal       = 0;
        $impostos    = 0;
        $frete       = 0;
        $desconto    = 0;
        $maxdesconto = 0;
        $margem      = 0;
        $percentual  = 0;
        $pnominal    = 0;
        $totalIndex = 0;
        $indexCount = 0;

        foreach( $array['Lead']['Produtos'] as $k => $v )
        {
            $products_ids[] = $v['produtoPOID'] ; // group product id
        }

        if ( !empty ($products_ids) )
        {
            //// sub routines by group
            $ids="'".implode("','",$products_ids)."'";

            // stock
            $stk_group = $this->get_stock_group( $ids, $array['Lead']['unidadeEmitentePOID'] );
            // s($stk_group);
            // cost
           // $cost_group = self::cost_group( $ids);
            // s($cost_group);
            //  die();
        }

        foreach( $array['Lead']['Produtos'] as $k => $v )
        {
            $array['Lead']['existProducts'] = true;
            //s($v);
            //die();
            $array['Lead']['Produtos'][$k]['produtoDisponivel'] = TRUE;

            $detalhe = $v['Detalhe']['edges'][0]['node'];

            $array['Lead']['Produtos'][$k]['Detalhe'] = $detalhe;
            $array['Lead']['Produtos'][$k]['Detalhe']['produtoMarcaFormatado'] = $this->clean_string( $detalhe['produtoMarca'] );
            $array['Lead']['Produtos'][$k]['Detalhe']['produtoRevenda']        = $this->formatPrice(  $detalhe['produtoRevenda'] );
            // $array['Lead']['Produtos'][$k]['Detalhe']['produtoFob']        = $this->formatPrice(  $detalhe['produtoFob'] );

            if ( $v['produtoValorOriginal'] > 0)
            {
                $produtoValor   = $this->formatPrice( $v["produtoValor"] );
                $produtoValorCC = $this->formatPrice( $v["produtoValorClientedeCliente"] );

                if ( $v['produtoValor'] < $v['produtoValorOriginal'] )
                {
                    $array['Lead']['Produtos'][$k]['produtoDescontoPorcentagem'] = round( (1 - ( $v['produtoValor'] / $v['produtoValorOriginal'] ) ) * 100, 0);
                }else{
                    $array['Lead']['Produtos'][$k]['produtoDescontoPorcentagem'] = 0;
                }

                $array['Lead']['Produtos'][$k]['produtoValor']               = $produtoValor;
                $array['Lead']['Produtos'][$k]['produtoValorCC']             = $produtoValorCC;
                $array['Lead']['Produtos'][$k]['produtoValorOriginal']       = $this->formatPrice( $v["produtoValorOriginal"] );
                $array['Lead']['Produtos'][$k]['produtoValorArredondado']    = $this->formatPrice( $v["produtoValor"] );
                $array['Lead']['Produtos'][$k]['produtoValorFormatado']      = $this->formatPrice( $v["produtoValor"]);
                $array['Lead']['Produtos'][$k]['produtoCCValorFormatado']    = $this->formatPrice( $v["produtoValorClientedeCliente"]);

                if ($detalhe['produtoFob'] > 0) {
                    $productIndex = $v['produtoValor'] / $detalhe['produtoFob'];
                    $array['Lead']['Produtos'][$k]['produtoIndex'] = number_format($productIndex, 2, ',', '.');
                    $totalIndex += $productIndex;
                    $indexCount++;
                } else {
                    $array['Lead']['Produtos'][$k]['produtoIndex'] = 'N/A';
                }

                if ( $v['produtoValorOriginal'] <> $v["produtoValor"] )
                {
                    $array['Lead']['Produtos'][$k]['produtoPrecoAjustado'] = true;
                }
            }

            //Impostos
            $array['Lead']['Produtos'][$k]['produtoSTSubtotal']    = $this->formatPrice($v["produtoST"]);
            $array['Lead']['Produtos'][$k]['produtoIPISubtotal']   = $this->formatPrice($v["produtoIPI"]);
            $array['Lead']['Produtos'][$k]['produtoDifalSubtotal']   = $this->formatPrice($v["produtoDifal"]);

            if ( $v["produtoQuantidade"] > 0)
            {
                $array['Lead']['Produtos'][$k]['produtoST']            = $this->formatPrice($v["produtoST"] / $v["produtoQuantidade"]);
                $array['Lead']['Produtos'][$k]['produtoIPI']           = $this->formatPrice($v["produtoIPI"] / $v["produtoQuantidade"]);
                $array['Lead']['Produtos'][$k]['produtoDifal']         = $this->formatPrice($v["produtoDifal"] / $v["produtoQuantidade"]);
                $array['Lead']['Produtos'][$k]['produtoValorUnitario'] = $this->formatPrice($v["produtoValor"] / $v["produtoQuantidade"]);
            }

            //Segmento
            $segmento =  $detalhe['Segmento']['edges'][0]['node'];
            $array['Lead']['Produtos'][$k]['Detalhe']['Segmento']  = $segmento;

            //Classificacao
            if (isset($detalhe['Classificacao']['edges'][0]['node']))
            {
                $classificacao =  $detalhe['Classificacao']['edges'][0]['node'];
                $array['Lead']['Produtos'][$k]['Classificacao']  = $classificacao;
                $array['Lead']['Produtos'][$k]['Detalhe']['Classificacao']  = $classificacao;
            }

            $solr = $this->get_solr_query( $v['produtoPOID'] );
            $array['Lead']['Produtos'][$k]['solr'] = $solr;

            if ( $this->check )
            {
                $stock = $stk_group[ $v['produtoPOID'] ]['estoques'][ $array['Lead']['unidadeEmitentePOID'] ];
                // $stock = self::get_stock_lead(  $v['produtoPOID'], $array['Lead']['unidadeEmitentePOID'] );

                $array['Lead']['Produtos'][$k]['stock'] = $stock;

                $estoqueComercial = $stock['estoqueComercial'];

                //Reduz Estoque Autopeças barra funda
                if ( $_SESSION['MM_Nivel'] < 3 and 5 == $this->segmentID and ( '3' != $array['Lead']['leadFonte'] and '4' != $array['Lead']['leadFonte'] and '5' != $array['Lead']['leadFonte'] and '6' != $array['Lead']['leadFonte'] and '7' != $array['Lead']['leadFonte']  and '8' != $array['Lead']['leadFonte']  ) )
                {
                    //s($stock);
                    //die();

                    if ( 8 == $stock['estoqueID'] and $estoqueComercial < 50 )
                    {
                        // $estoqueComercial = $estoqueComercial - 5;
                        if ( $estoqueComercial < 0 ) $estoqueComercial = 0;

                        $array['Lead']['Produtos'][$k]['stock']['estoqueComercial'] = $estoqueComercial;
                    }
                }

                if ( $estoqueComercial < $v['produtoQuantidade'] and 1 == $array['Lead']['Natureza']['naturezaOperacaoMovimentaEstoque'])
                {
                    $array['Lead']['Produtos'][$k]['produtoIndisponivel']    = TRUE;
                    $array['Lead']['Produtos'][$k]['produtoDisponivel']      = FALSE;
                    $array['Lead']['Produtos'][$k]['produtoDisponibilidade'] = $estoqueComercial;
                    $array['Lead']['existPendencies'] = 'Produtos sem estoque Comercial Disponível';
                }
            }else{
                $array['Lead']['existPendencies'] = 'para emitir pedido opção verificar estoque precisa estar ativado';
            }

            if (  $v['produtoQuantidade'] == 0 )
            {
                $array['Lead']['Produtos'][$k]['produtoIndisponivel']    = TRUE;
                $array['Lead']['Produtos'][$k]['produtoDisponivel']      = FALSE;
                $array['Lead']['Produtos'][$k]['produtoDisponibilidade'] = FALSE;//$stock['estoqueComercial'];
                $array['Lead']['existPendencies'] = 'Produto zerado no carrinho';
            }

            $variedade++;
            $array['Lead']['Produtos'][$k]['produtoContagem'] = $variedade;

            if (  ! isset($array['Lead']['Produtos'][$k]['produtoIndisponivel'])  )
            {
                if ( $v["produtoValor"] > 0  )
                {
                    $desconto   += $array['Lead']['Produtos'][$k]['produtoDescontoPorcentagem'];
                    $produto    += $v["produtoValor"] * $v["produtoQuantidade"];
                    $produtocc  += $v["produtoValorClientedeCliente"] * $v["produtoQuantidade"];
                    $base       += $v["produtoValorOriginal"] * $v["produtoQuantidade"];
                    $ipi        += $v["produtoIPI"];
                    $st         += $v["produtoST"];
                    $difal      += $v["produtoDifal"];
                    $impostos   += $v["produtoIPI"] + $v["produtoST"] + $v["produtoDifal"];
                }

                if ( $v["produtoValorClientedeCliente"] == 0  and $this->segmentID == 1 and $array['Lead']['clienteDoClientePOID'] > 0 )
                {
                    //$array['Lead']['existPendencies'] = 'Favor arrumar o preço de venda do produto para o Cliente não pode ser zerado.';
                }

                if ( $v["produtoQuantidade"] > 0)
                {
                    $array['Lead']['Produtos'][$k]['produtoValorSubtotal']            = $this->formatPrice( $v["produtoValor"] * $v["produtoQuantidade"]);
                    $array['Lead']['Produtos'][$k]['produtoCCValorSubtotal']          = $this->formatPrice( $v["produtoValorClientedeCliente"] * $v["produtoQuantidade"]);
                    $array['Lead']['Produtos'][$k]['produtoValorUnitarioComImpostos'] = $this->formatPrice( $v["produtoValor"] + ( round($v["produtoIPI"] / $v["produtoQuantidade"],2) ) + ( round($v["produtoST"] / $v["produtoQuantidade"],2) ) + ( round($v["produtoDifal"] / $v["produtoQuantidade"],2) ) );
                    $array['Lead']['Produtos'][$k]['produtoValorOriginalUnitarioComImpostos'] = $this->formatPrice( $v["produtoValorOriginal"] + ( round($v["produtoIPI"] / $v["produtoQuantidade"],2) ) + ( round($v["produtoST"] / $v["produtoQuantidade"],2) ) );
                    $array['Lead']['Produtos'][$k]['produtoValorSubtotalComImpostos'] = $this->formatPrice( ( $v["produtoValor"] * $v["produtoQuantidade"] ) + $v["produtoST"] + $v["produtoIPI"] + $v["produtoDifal"]  );
                }

                if ( $detalhe["produtoPeso"] > 0 )
                {
                    $peso += $v["produtoQuantidade"] * $detalhe['produtoPeso'];
                }

                if ( $v["produtoQuantidade"] > 0 )
                {
                    $quantidade += $v["produtoQuantidade"];
                }

            }

        }

        if ($pnominal > 0)
            $percentual = ( $pnominal / $produto ) * 100;

        $pedido = $produto + $ipi + $st + $difal;
        $frete  = $array['Lead']['valorFrete'];


        //Total
        $array['Lead']['Total'] = array(
            'maxdesconto' => $maxdesconto,
            'desconto'    => 0,
            'mdpedido'    => 0,
            'nominal'     => round($pnominal,2),
            'percentual'  => round($percentual,2),
            'quantidade'  => $quantidade,
            'variedade'   => $variedade,
            'peso'        => round( $peso, 2),
            'pedido'      => $this->formatPrice($pedido + $frete),
            //'icms'        => $this->formatPrice($produto + $frete ),
            'bruto'       => round( $pedido, 2),
            'frete'       => $this->formatPrice($frete),
            'ipi'         => $this->formatPrice($ipi),
            'st'          => $this->formatPrice($st),
            'difal'       => $this->formatPrice($difal),
            'base'        => $this->formatPrice($base),
            'produto'     => $this->formatPrice($produto),
            'produtocc'   => $this->formatPrice($produtocc),
            'liquido'     => round($produto, 2),
            'impostos'    => $this->formatPrice($impostos)
        );

        if ($indexCount > 0) {
            $averageIndex = $totalIndex / $indexCount;
            $array['Lead']['Total']['indexAvg'] = number_format($averageIndex, 2, ',', '.');
        } else {
            $array['Lead']['Total']['indexAvg'] = 'N/A';
        }

        if ( isset($this->segmentID)  )
        {
            $maxdesconto = $this->get_discount( $array, 'order' );
            $array['Lead']['Total']['maxdesconto'] = $maxdesconto;
        }

        if ( $pedido <  $array['Lead']['Financeiro']['disponivel'] )
        {
            $array['Lead']['Financeiro']['autorizado'] = true;
        }

        $countF=0;

        foreach( $array['Lead']['Produtos'] as $k => $v )
        {
            if ( $peso > 0)
            {
                $pFreight = round( str_replace(",", ".", $v['Detalhe']['produtoPeso']) * str_replace(",", ".", $frete)  / str_replace(",", ".", $peso ) * $v['produtoQuantidade'], 4);
                $array['Lead']['Produtos'][$k]['produtoFrete'] = $pFreight;

                $countF += $pFreight;
                $array['Lead']['Produtos'][$k]['produtoFreteTotal'] = $countF;
            }
        }

        //Cliente do Cliente
        if ( isset($this->segmentID) and $this->segmentID == 1 and $array['Lead']['clienteDoClientePOID'] > 0 )
        {
            $array['Lead']['ClienteDoCliente'] = self::getCustomerCustomer( $array['Lead']['clienteDoClientePOID'] );

            $comissao = 0;

            if ( $produtocc > 0 )
            {
                $margem           = $produtocc - $produto;
                $descontoFP	      = $produto * $array['Lead']['TipoPagamento']['pagamentoSobretaxa'] / 100;
                $descontoFederal  = $margem * 0.1183; // Desconto Pis/Cofins/CSIR (11,83%)
                $descontoICMS     = $margem * 0.088; // Desconto de ICMS (8,8%) sobre a diferença
                $comissao         = $margem - $descontoFederal -  $descontoFP - $descontoICMS;
                $percentual       = $comissao / $produtocc * 100;

                $array['Lead']['ClienteDoCliente']['comissao']        = round($comissao,2);
                $array['Lead']['ClienteDoCliente']['descontoFP']      = $this->formatPrice($descontoFP,2);
                $array['Lead']['ClienteDoCliente']['descontoFederal'] = $this->formatPrice($descontoFederal,2);
                $array['Lead']['ClienteDoCliente']['descontoICMS']    = $this->formatPrice($descontoICMS,2);
                $array['Lead']['ClienteDoCliente']['margem']          = round($margem,2);
                $array['Lead']['ClienteDoCliente']['percentual']      = round($percentual,2);

                $array['Lead']['Total']['comissao'] = $this->formatPrice( $comissao );
            }else{
                //$array['Lead']['existPendencies'] = 'Favor arrumar a Comissão do Cliente não pode ser zerado.';
            }

            if ( ($comissao < 0 ) and $variedade > 0)
            {
                //$array['Lead']['existPendencies'] = 'Favor arrumar a Comissão do Cliente não pode ser negativa ou zerado.';
            }
        }


        $array['today'] = date('Y-m-d');

        $this->response->body( json_encode($array) );
        return $this->response;
    }

    public function get_stock_lead(  $product, $unity )
    {

        $url = sprintf( 'http://vallery-stock/get/%s/%s' , $product, $unity);

        $json = Request::factory( $url )
            ->method('GET')
            ->execute()
            ->body();

        $response = json_decode($json,true);

        //s($response);
        //die();

        if ( ! isset($response['estoque'][$unity][0]))
        {
            return 0;
        }

        return $response['estoque'][$unity][0];
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


    private function cost_group ( $ids )
    {
        $sql= sprintf("SELECT *, DATE_FORMAT(STR_TO_DATE(chegada,'%s'), '%s') As Date, DATE_FORMAT(date,'%s') As Date2
                    FROM webteam.`custo_rolamentos` WHERE `produto_id` in (%s) ORDER BY Date asc, Date2 asc, id desc ", '%d/%m/%Y','%Y-%m-%d','%Y-%m-%d', $ids );

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        $resp = [];
        foreach( $response as $k => $v )
        {
            $resp[$v['produto_id']] = $v;
        }

        //s($resp);
        //die();

        return $resp;
    }

    public function cost ( $produtoID )
    {
        $sql= "SELECT *, DATE_FORMAT(STR_TO_DATE(chegada, '%d/%m/%Y'), '%Y-%m-%d') As Date, DATE_FORMAT(date,'%Y-%m-%d') As Date2
                    FROM webteam.`custo_rolamentos` WHERE `produto_id` = ".$produtoID." ORDER BY Date DESC, Date2 desc, id asc ";

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        if (count($response) > 0 )
        {
            return $response[0];
        }

        return array();
    }

    public function pending ( $clienteID )
    {
        $sql= sprintf("SELECT clientes.id, clientes.limite,
                            (SELECT  SUM(c.valor) FROM cheques c WHERE c.idcli=clientes.id AND c.data < CURDATE()-5 AND ( ISNULL(c.datadep) OR MONTH(c.datadep)=0  ) GROUP BY c.idcli) AS atrasados,
                            (SELECT  SUM(c.valor) FROM cheques c WHERE c.idcli=clientes.id AND ( YEAR(datadep)=0 OR datadep> current_date ) GROUP BY c.idcli) AS pendentes,
                            (SELECT  SUM(h.usvale) FROM hoje h WHERE h.idcli=clientes.id  and h.prazo<>15  AND h.nop in (27,28,51,76) GROUP BY h.idcli) AS vale
                            FROM clientes
                            WHERE vip < 9 AND clientes.id=%s
                            LIMIT 1
                        ", $clienteID );

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        if ( count($response) > 0)
        {
            $response[0]['disponivel'] = $response[0]['limite'] - ( max(0, $response[0]['vale'] ) + $response[0]['pendentes'] ) ;
            
            // Add flag for atrasados > 100
            if ($response[0]['atrasados'] > 100) {
                $response[0]['atrasados_gt_100'] = true;
            }

            return $response[0];
        }

        return true;
    }

    public function permissions ( $clienteID )
    {
        $sql= sprintf("SELECT clientes.id, clientes.limite,
                            (SELECT SUM(cheques.valor) FROM mak.cheques WHERE cheques.idcli=clientes.id AND cheques.data < CURDATE() AND ( ISNULL(cheques.datadep) OR MONTH(cheques.datadep)=0 ) GROUP BY cheques.idcli  ) GROUP BY c.idcli) AS atrasados,
                            (SELECT SUM(cheques.valor) FROM mak.cheques WHERE cheques.idcli=clientes.id AND ( cheques.datadep > NOW() OR MONTH(cheques.datadep)=0 ) GROUP BY cheques.idcl) AS pendentes
                            (SELECT SUM(hoje.usvale) FROM mak.hoje WHERE hoje.idcli=clientes.id AND hoje.prazo<>15 AND hoje.nop = 27 AND hoje.usvale >= 0  GROUP BY hoje.idcli) AS pedidos_em_aberto
                            FROM clientes
                            WHERE vip < 9 AND clientes.id=%s
                            LIMIT 1
                        ", $clienteID );

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        if ( count($response) > 0)
        {
            $response[0]['disponivel'] = $response[0]['limite'] - ( $response[0]['pendentes'] + $response[0]['pedidos_em_aberto'] );

            return $response[0];
        }

        return true;
    }
    public function ticket ( $clienteID )
    {
        $sql= sprintf("SELECT *
                            FROM crm.chamadas
                            WHERE cliente_id=%s
                            AND status_id = 1
                            ORDER BY id desc
                        ", $clienteID );

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        if ( count($response) > 0)
        {
            return $response;
        }

        return false;
    }

    private function getCustomerCustomer( $id )
    {
        $url = $_ENV['api_vallery_v1'].'//ClientedeClientes/'.$id;

        $json = Request::factory($url)
            ->method('GET')
            //->post($data)
            ->execute()
            ->body();

        return $response = json_decode( $json, true );
    }

}