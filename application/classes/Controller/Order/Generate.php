<?php
class Controller_Order_Generate extends Controller_Order_Base {

    public function before()
    {
        $this->shouldRedirect = (isset($_GET['redirect']) and $_GET['redirect'] === 'false') ? false : true;
        $this->clientUser = 0;
        $this->isDev = false;
        $this->domain = "https://office.vallery.com.br";

        if(isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) and isset($_SERVER["HTTP_X_FORWARDED_HOST"]))
        {
            $this->domain = $_SERVER["HTTP_X_FORWARDED_PROTO"].'://'.$_SERVER["HTTP_X_FORWARDED_HOST"];
        }

        if(isset($_GET['customerUser']))
        {
            $this->clientUser = $_GET['customerUser'];
        }

        if($this->clientUser == '114781')
        {
            $this->isDev = true;
            $this->shouldRedirect = false;
        }

        if(isset($_GET['dev']) and $_GET['dev'] == 'true')
        {
            $this->isDev = true;
            $this->shouldRedirect = false;
        }
    }

    public function action_index()
    {
        $leadID    = $this->request->param('id');
        $segmentID = $this->request->param('segment');

        $this->testAPP = 'webteam';

        if ( ! $leadID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $leadID</h3>'));

        if ( ! $segmentID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $segmentID</h3>'));

        $data = array(
            'check'   => true,
            'segment' => $segmentID
        );

        $json = Request::factory( '/lead/build/'.$leadID )
            ->method('GET')
            ->query($data)
            ->execute()
            ->body();

        $lead = json_decode($json,true);

        //Check Lead
        self::check( $lead, $segmentID );

        $order      = self::prepareOrder( $lead );
        $products   = self::prepareOrderProducts( $lead, $order );

        if($this->isDev)
        {
            if(!$this->shouldRedirect)
            {
                $order['products'] = $products;
                $order['dev'] = true;

                header('Content-Type: application/json');
                echo json_encode($order);
                die();
            }

            return $this->redirect( $this->domain.'/crm/v4/orders/order/'.base64_encode($order['id']), 302);
        }

        if ( 1 == $segmentID)
        {
            //$tsm = self::updateTSM( $products, $order);

            //Se for Faturamento Direto Consumidor diferente de Retira
            if ( 1 == $segmentID and $lead['Lead']['clienteDoClientePOID']  > 0 and $lead['Lead']['transportadoraPOID'] <> 9 )
            {
                $ordercc    = self::prepareOrderCC( $lead );
                $productscc = self::prepareOrderProducts( $lead, $ordercc );
                //$tsmcc      = self::updateTSM( $productscc, $ordercc );

                // s($ordercc,$productscc);
            }
        }

        $stock      = self::updateStock( $lead, $products, $order['id'] );
        $updateLead = self::updateLead( $leadID, $order );

        if ( 27 == $lead['Lead']['Natureza']['id'] )
        {
            $mail = self::sendMail( $order, $lead );
        }


        //Slack
        $this->logSlack( '<'.$this->domain.'/nfe/admin/?search_txt='.$order['id'].'|ver pedido> '.$_SESSION['MM_Nick'].' Gerou um Pedido Número '.$order['id'] );

        //Twilio
        // echo $twilio = $this->logTwilio( '5511995206263', '14155238886', $_SESSION['MM_Nick'].' Gerou um Pedido Número '.$order['id'] ); //$to, $from, $message

        //$profile = View::factory('profiler/stats');
        //die();

        //echo $order['id'];

        $text = $_SESSION['MM_Nick'].' Gerou um Pedido Número '.$order['id'];
        $link = $this->domain.'/crm/v4/orders/order/'.base64_encode($order['id']);
        $this->notify( $text, $link );

        //Add Ticket
        $ticket = array(
            'ticketClienteId'   => $lead['Lead']['clientePOID'],
            'ticketOrigemId'    => 3,
            'ticketConsultaId'  => $leadID ,
            'ticketPedidoId'    => $order['id'] ,
            'ticketStatusId'    => 2,
            'ticketEventoId'    => 34,
            'ticketProcessoId'  => 4,
            'ticketCanalId'     => 6,
            'ticketDetalhes'    => '',
            'ticketDataRetorno' => $lead['today'],
        );

        $ticket = Controller_Ticket_Generate::addTicket($ticket, $this->get_date_new() );

        if ( $_SESSION['MM_Userid'] == 84 )
        {
            s($ticket);
            s($_POST, $lead);
            //die();
        }

        $send_to_sqs = Request::factory( $_ENV['sqs'].'/orders/publish/'.$order['id'] )->method('GET')->execute()->body();

        if(!$this->shouldRedirect)
        {
            $order['products'] = $products;

            header('Content-Type: application/json');
            echo json_encode($order);
            die();
        }

        return $this->redirect( $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].'/crm/v4/orders/order/'.base64_encode($order['id']), 302);
    }

    private function updateLead( $leadID, $order )
    {
        $data = array(
            'pedidoPOID' => $order['id'],
        );

        $url = $_ENV['api_vallery_v1'].'/Leads/'.$leadID;

        $json = Request::factory($url)
            ->headers('Authorization', 'Basic '.AUTH2)
            ->method('PUT')
            ->post($data)
            ->execute()
            ->body();
    }

    private function check(array $data, $segmentID)
    {
        //s($data['Lead']['Produtos']);
        //die();

        $error = [];


        if ($data['Lead']['Natureza']['id'] == 76)
        {
            // Se unidade NÃO for 6 e também NÃO for 8 → gera erro
            if ($data['Lead']['unidadeEmitentePOID'] != 6 && $data['Lead']['unidadeEmitentePOID'] != 8) {
                $error = [
                    'id' => 17,
                    'message' => 'Somente Barra Funda ou Blumenau (unidades 6 ou 8) Pré-venda'
                ];
            }
        }

        if ( 27 == $data['Lead']['Natureza']['id'] )
        {
            //Verifica Limite Disponível
            $search  = array('.', ',');
            $replace = array('', '.');
            $pedido  = str_replace($search, $replace, $data['Lead']['Total']['pedido']);
            $limite  = $data['Lead']['Financeiro']['disponivel'];

            if ( $pedido > $limite and $data['Lead']['TipoPagamento']['pagamentoNome'] == 'DU' )
            {
                $error = [
                    'id' => 1,
                    'message' => 'Favor Verificar o limite disponível. Valor do pedido é maior que o limite.'
                ];
            }

            if ( 0 == $data['Lead']['Cliente']['clientePrazo'] and $data['Lead']['TipoPagamento']['pagamentoNome'] == 'DU' )
            {
               $error = [
                    'id' => 7,
                    'message' => 'Cliente só pode comprar à vista'
                ];
            }

            if ( $segmentID == 5 and $pedido > $limite and ( $data['Lead']['TipoPagamento']['pagamentoNome'] == 'DU' or $data['Lead']['TipoPagamento']['id'] == 'BO' )  )
            {
               $error = [
                    'id' => 1,
                    'message' => 'Favor Verificar o limite disponível. Valor do pedido é maior que o limite.'
                ];
            }

            if ( !isset($data['Lead']['Prazo']['id']) or $data['Lead']['Prazo']['id'] == '' )
            {
                $error = [
                    'id' => 7,
                    'message' => 'Cliente só pode comprar à vista'
                ];
            }

            //s($data['Lead']['TipoPagamento']['pagamentoPOID']);
            //s($data['Lead']['Prazo']['id'] );
            //die();

            if ( $segmentID == 1  )
            {
                //Regra 1 Mesmo do Pedido
                $list = array(4,1);

                if (
                    isset($data['Lead']['Prazo']['id']) and (
                        ( in_array( $data['Lead']['TipoPagamento']['pagamentoPOID'], $list ) and $data['Lead']['Prazo']['id'] != '204'  ) or
                        ( !in_array( $data['Lead']['TipoPagamento']['pagamentoPOID'], $list ) and $data['Lead']['Prazo']['id'] == '204')
                    )
                  )
                {
                    $error = [
                        'id' => 9,
                        'message' => 'Operação de Pagamento não permitida para Prazo Mesmo do Pedido'
                    ];
                }

                //Regra 2 BOLETO
                $list2 = array(320,109,107);

                if (
                    isset($data['Lead']['Prazo']['id']) and (
                        ( in_array( $data['Lead']['Prazo']['id'], $list2 ) and $data['Lead']['TipoPagamento']['pagamentoPOID'] != '3' ) or
                        ( ! in_array( $data['Lead']['Prazo']['id'], $list2 ) and $data['Lead']['TipoPagamento']['pagamentoPOID'] == '3')
                    )
                )
                {
                    $error = [
                        'id' => 10,
                        'message' => 'Operação de Pagamento não permitida para Boleto'
                    ];
                }

                //Regra 3 Dinheiro, PIX e Cartão
                $list3 = array(5,14,12,7);
                if (
                    ( in_array( $data['Lead']['TipoPagamento']['pagamentoPOID'], $list3 ) and $data['Lead']['Prazo']['id'] == '204'  )
                  )
                {
                    $error = [
                        'id' => 12,
                        'message' => 'Operação de Pagamento (Prazo) não permitida para Dinheiro, PIX e Cartão'
                    ];
                }

                //Última Regra Pagamento não autorizado
                $list_last = array(4,1,14,3,5,6,7,8,9,10,11,12);

                if ( ! in_array( $data['Lead']['TipoPagamento']['pagamentoPOID'] , $list_last ) )
                {
                    $error = [
                        'id' => 11,
                        'message' => 'Operação de Pagamento não permitida para máquinas'
                    ];
                }
            }

            //Autopeças entregar só pedido maior que 1000
            if ( $data['Lead']['segmentoPOID'] == 5  and $data['Lead']['transportadoraPOID'] == 1 and $data['Lead']['Total']['liquido'] < 100 )
            {
                $error = [
                    'id' => 5,
                    'message' => 'Favor Verificar a forma de coleta <strong>valor abaixo do estipulado. Valor para entrega tem que acima de R$ 800,00 em produtos'
                ];
            }
        }

        if ( $data['Lead']['Natureza']['naturezaTipoEstoque'] == '' )
        {
            $error = [
                'id' => 6,
                'message' => 'Favor Natureza de Operação'
            ];
        }

        //Verifica estoque Produtos
        foreach( $data['Lead']['Produtos'] as $k => $v )
        {
            if ( isset( $v['produtoIndisponivel'] )  )
            {
                $error = [
                    'id' => 2,
                    'message' => 'Favor Verificar os Produtos sem estoque. Para prosseguir com o pedido é necessário remover-los'
                ];
            }

            if ( isset( $v['produtoQuantidade'] ) and $v['produtoQuantidade'] == 0 )
            {
                $error = [
                    'id' => 3,
                    'message' => 'Favor Verificar a <strong>Data de Entrega. O Valor não pode ser menor que hoje.'
                ];
            }

            if ( isset( $v['produtoValorCC'] ) and $data['Lead']['clienteDoClientePOID'] > 0  and ( $v['produtoValorCC'] == 0 or $v['produtoValorCC'] == null or  $v['produtoValorCC'] == '' )  )
            {
                $error = [
                    'id' => 8,
                    'message' => 'Produto de venda para o Cliente do Cliente está zerado'
                ];
            }

            if ( $segmentID == 1  )
            {
                //Se For diferente de Duplicata não permite comprar parcelado
                $list50 = array(1);

                if ( isset( $v['produtoVezes'] ) and $v['produtoVezes'] > 0 and $v['produtoValor'] > 0 and ! in_array( $data['Lead']['TipoPagamento']['pagamentoPOID'], $list50 ) )
                {
                    // $error = [
                    //     'id' => 14,
                    //     'message' => 'Operação de Pagamento não permitida, com produto parcelado'
                    // ];
                    //return $this->redirect( $redirect.'order/checkout/'.$data['Lead']['id'].'/'.$segmentID.'?error=14', 302);
                }

                //Se For diferente de Dinheiro, PIX, Deposito, Boleto não permite comprar a vista
                $list51 = array(5, 14, 4, 3);

                if ( isset( $v['produtoVezes'] ) and $v['produtoVezes'] == 0 and $v['produtoValor'] > 0 and ! in_array( $data['Lead']['TipoPagamento']['pagamentoPOID'], $list51 ) )
                {
                    // $error = [
                    //     'id' => 15,
                    //     'message' => 'Operação de Pagamento não permitida, com produto a vista'
                    // ];
                    //return $this->redirect( $redirect.'order/checkout/'.$data['Lead']['id'].'/'.$segmentID.'?error=15', 302);
                }

                //Regra 3 Dinheiro, PIX e Cartão
                $list3 = array(5,14,12,7);
                if (
                    ( in_array( $data['Lead']['TipoPagamento']['pagamentoPOID'], $list3 ) and $v['produtoVezes'] > 0 )
                  )
                {
                    $error = [
                        'id' => 16,
                        'message' => 'Produtos com prazo, favor alterar para forma permitido para Dinheiro, PIX e Cartão'
                    ];
                }


            }
        }

        //Lead já fez Pedido
        if ( isset( $data['Lead']['pedidoPOID'] )  )
        {
            $error = [
                'id' => 4,
                'message' => 'Favor Verificar a <strong>esse Lead já foi usado. Se deseja gerar outro pedido, favor criar outro LEAD.'
            ];
        }

        if ( $_SESSION['MM_Userid'] == 84 )
        {
            //s($_POST, $lead);
            //die();
        }

        if(!isset($error['id']))
        {
            return;
        }

        if($this->shouldRedirect)
        {
            return self::redirect_error($error['id'], $data, $segmentID);
        }

        header('Content-Type: application/json');
        echo json_encode([ 'error' => $error ]);
        die();
    }

    private function prepareOrder(array $data): array
    {
        // s($data);
        // die();

        $array = null;

        // extra
        $array['segmentoPOID'] = $data['Lead']['segmentoPOID'];

        //Autorizar Pedido
        $AutorizaLogistica = 0;

        if  ( 100 == $data['Lead']['Cliente']['clientePrazo'] and 27 == $data['Lead']['Natureza']['id'])
            $AutorizaLogistica = 1 ;

        if ( isset( $data['Lead']['leadFonte'] )
              and ( '3' == $data['Lead']['leadFonte']
                 or '4' == $data['Lead']['leadFonte']
                 or '5' == $data['Lead']['leadFonte']
                 or '6' == $data['Lead']['leadFonte']
                 or '7' == $data['Lead']['leadFonte']
                 or '8' == $data['Lead']['leadFonte']
                )
           )
        {
            $AutorizaLogistica = 1;

            $array['pedidoVolumes'] = 1;
        }

        $array['AutorizaLogistica'] = $AutorizaLogistica;

        //pedido variables
        $array['clientePOID']           = $data['Lead']['clientePOID'];
        $array['clienteDeClientePOID']  = $data['Lead']['clienteDoClientePOID'];
        $array['clienteUsuarioPOID']    = $this->clientUser;
        $array['pedidoTipoFrete']       = $data['Lead']['tipoFrete'];
        $array['naturezaOperacaoPOID']  = $data['Lead']['naturezaOperacaoPOID'];
        $array['unidadeEmitentePOID']   = $data['Lead']['unidadeEmitentePOID'];
        $array['unidadeLogisticaPOID']  = $data['Lead']['unidadeEmitentePOID'];
        $array['tipoPagamentoPOID']     = $data['Lead']['tipoPagamentoPOID'];
        $array['emissorPOID']           = $_SESSION['MM_Userid'];
        $array['vendedorPOID']          = $data['Lead']['Cliente']['clienteGerentePOID'];

        //Vendedor por Segmento
        if ( 3 == $array['segmentoPOID'] or 1 == $array['segmentoPOID'] )
        {
            $array['vendedorPOID'] = self::getSale( $data['Lead']['clientePOID'], $array['segmentoPOID'], $data['Lead']['Cliente']['clienteGerentePOID'] );
        }

        $array['tipoTransportePOID']    = $data['Lead']['transportadoraPOID'];
        $array['transportadoraPOID']    = $data['Lead']['transportadoraPOID'];

        //Se for Faturamento Direto Consumidor diferente de Retira
        if ( 1 == $array['segmentoPOID'] and $data['Lead']['clienteDoClientePOID']  > 0 and $data['Lead']['transportadoraPOID'] <> 9 )
        {
            $array['tipoTransportePOID']    = 16273;
            $array['transportadoraPOID']    = 16273;
        }

        $array['pedidoPrazo']           = $data['Lead']['Prazo']['id'];
        $array['pedidoNovoPrazo']       = '';

        //datas
        $array['pedidoDataEmisao']            = $this->get_date_new();
        $array['pedidoDataEntregaProgramada'] = $data['Lead']['dataEntrega'];
        //$array['pedidoDataSaida']             = null;
        $array['pedidoStatus']                = 2; //Em análise
        $array['pedidoFontePOID']             = $data['Lead']['leadFonte'];

        $pedidoInfoAdicional = '';

        if ( isset( $data['Lead']['ordemDeCompra']))
        {
            $pedidoInfoAdicional.= ' Ordem de compra:'.$data['Lead']['ordemDeCompra'];
        }

        if ( isset( $data['Lead']['nomeComprador']))
        {
            $pedidoInfoAdicional.= ' Nome Comprador:'.$data['Lead']['nomeComprador'];
        }

        $obs = 'emitido via sistema interno';

        if ( isset( $data['Lead']['observacaoInterna']) )
        {
            $obs.= $data['Lead']['observacaoInterna'];
        }

        //$array['pedidoColeta']               = null;
        $array['pedidoObservacao']           = $obs;
        $array['pedidoObservacaoFinanceiro'] = $data['Lead']['observacaoFinaceiro'];
        $array['pedidoObservacaoLogistica']  = $data['Lead']['observacaoLogistica'];
        $array['pedidoObservacaoNfe']        = $data['Lead']['observacaoNotaFiscal'];
        $array['pedidoInfoAdicional']        = $pedidoInfoAdicional;

        //$array['pedidoPlaca']                = '';
        //$array['pedidoPlacaUF']              = '';

        $search  = array('.', ',');
        $replace = array('', '.');
        $total   = str_replace($search, $replace, $data['Lead']['Total']);

        //Valores
        $array['pedidoValor']         = $total['produto'];
        $array['pedidoValorFrete']    = $total['frete'];
        $array['pedidoValorImpostos'] = $total['impostos'];
        $array['pedidoValorTotal']    = $total['pedido'] - $total['frete'];
        $array['pedidoCredito']       = $total['pedido'];
        $array['pedidoValorIPI']      = $total['ipi'];
        $array['pedidoValorST']       = $total['st'];
        $array['pedidoDesconto']      = $total['mdpedido'];
        $array['pedidoValorComissao'] = 0;

        if ( $array['segmentoPOID'] == 6 and $data['Lead']['Cliente']['Gerente']['UsuarioCargo'] == 'Representante' )
        {
            $array['pedidoValorComissao'] = $data['Lead']['Vendedor']['Comissao']  ;
        }

        if ( $array['segmentoPOID'] == 1 and $data['Lead']['clienteDoClientePOID'] > 0 )
        {
            $array['pedidoValorComissao'] =  str_replace($search, $replace, $data['Lead']['Total']['comissao']);;
            $array['pedidoValor']         = $total['produtocc'];
            $array['pedidoValorTotal']    = $total['produtocc'] - $total['frete'];
            $array['pedidoCredito']       = $total['produtocc'];
        }

        if($data['Lead']['Natureza']['naturezaOperacaoFormaPagamento'] < '1')
        {
            $array['pedidoCredito'] = 0;
        }

        //s($array);
        //die();

        if($this->isDev) {
            $url = 'https://dev.office.internut.com.br/api/vallery/teste/Pedidos/';
        } else {
            $url = $_ENV["api_vallery_v1"].'/Pedidos';
        }

        // $url = $_ENV["api_vallery_v1"].'/Pedidos';

        $json = Request::factory( $url )
            ->headers('Authorization', 'Basic '.AUTH2)
            ->method('POST')
            ->post($array)
            ->execute()
            ->body();

        $response = json_decode($json,true);
        //s($json,$response);
        //die();

        return $response;
    }

    private function prepareOrderCC(array $data): array
    {
        $array = null;

        $AutorizaLogistica = 0;

        $array['AutorizaLogistica'] = $AutorizaLogistica;

        //pedido variables
        $array['clientePOID']           = $data['Lead']['clientePOID'];
        $array['clienteDeClientePOID']  = 0;
        $array['clienteUsuarioPOID']    = 0;
        $array['pedidoTipoFrete']       = $data['Lead']['tipoFrete'];
        $array['naturezaOperacaoPOID']  = 5;
        $array['unidadeEmitentePOID']   = $data['Lead']['unidadeEmitentePOID'];
        $array['unidadeLogisticaPOID']  = $data['Lead']['unidadeEmitentePOID'];
        $array['tipoPagamentoPOID']     = $data['Lead']['tipoPagamentoPOID'];
        $array['emissorPOID']           = $_SESSION['MM_Userid'];
        $array['vendedorPOID']          = $data['Lead']['Cliente']['clienteGerentePOID'];

        $array['tipoTransportePOID']    = $data['Lead']['transportadoraPOID'];
        $array['transportadoraPOID']    = $data['Lead']['transportadoraPOID'];

        $array['pedidoPrazo']           = $data['Lead']['Prazo']['id'];
        $array['pedidoNovoPrazo']       = '';

        //datas
        $array['pedidoDataEmisao']            = $this->get_date_new();
        $array['pedidoDataEntregaProgramada'] = $data['Lead']['dataEntrega'];
        //$array['pedidoDataSaida']             = null;
        $array['pedidoStatus']                = 2; //Em análise

        // extra
        $array['segmentoPOID'] = $data['Lead']['segmentoPOID'];

        $pedidoInfoAdicional = '';

        if ( isset( $data['Lead']['ordemDeCompra']))
        {
            $pedidoInfoAdicional.= ' Ordem de compra:'.$data['Lead']['ordemDeCompra'];
        }

        if ( isset( $data['Lead']['nomeComprador']))
        {
            $pedidoInfoAdicional.= ' Nome Comprador:'.$data['Lead']['nomeComprador'];
        }

        $obs = sprintf('emitido via sistema interno, REMESSA POR CONTA E ORDEM DE: %s %s %s %s %s %s',
                       $data['Lead']['ClienteDoCliente']['clienteNome'],
                       $data['Lead']['ClienteDoCliente']['clienteEndereco'],
                       $data['Lead']['ClienteDoCliente']['clienteNumero'],
                       $data['Lead']['ClienteDoCliente']['clienteComplemento'],
                       $data['Lead']['ClienteDoCliente']['clienteBairro'],
                       $data['Lead']['ClienteDoCliente']['clienteCidade'],
                       $data['Lead']['ClienteDoCliente']['clienteEstado']
                      );

        if ( isset( $data['Lead']['observacaoInterna']) )
        {
            $obs.= $data['Lead']['observacaoInterna'];
        }

        //$array['pedidoColeta']               = null;
        $array['pedidoObservacao']           = $obs;
        $array['pedidoObservacaoFinanceiro'] = $data['Lead']['observacaoFinaceiro'];
        $array['pedidoObservacaoLogistica']  = $data['Lead']['observacaoLogistica'];
        $array['pedidoObservacaoNfe']        = $data['Lead']['observacaoNotaFiscal'];
        $array['pedidoInfoAdicional']        = $pedidoInfoAdicional;
        //$array['pedidoPlaca']                = '';
        //$array['pedidoPlacaUF']              = '';
        //$array['pedidoVolumes']              = null;

        //desconto
        //$desconto =  $data['Lead']['Cliente']['clienteDesconto'] + $data['Lead']['Cliente']['clienteDescontoMakAutomotive'];

        $search  = array('.', ',');
        $replace = array('', '.');
        $total   = str_replace($search, $replace, $data['Lead']['Total']);

        //Valores
        $array['pedidoValor']         = $total['produto'];
        $array['pedidoValorFrete']    = $total['frete'];
        $array['pedidoValorImpostos'] = $total['impostos'];
        $array['pedidoValorTotal']    = $total['pedido'] - $total['frete'];
        $array['pedidoCredito']       = $total['pedido'];
        $array['pedidoValorIPI']      = $total['ipi'];
        $array['pedidoValorST']       = $total['st'];
        $array['pedidoDesconto']      = $total['mdpedido'];
        $array['pedidoValorComissao'] = 0;


        if ( $array['segmentoPOID'] == 1 and $data['Lead']['clienteDoClientePOID'] > 0 )
        {
            $array['pedidoValorComissao'] =  str_replace($search, $replace, $data['Lead']['Total']['comissao'])  ;
            $array['pedidoValor']         = $total['produtocc'];
            $array['pedidoValorTotal']    = $total['produtocc'] - $total['frete'];
            $array['pedidoCredito']       = $total['produtocc'];
        }

        if($data['Lead']['Natureza']['naturezaOperacaoFormaPagamento'] < '1')
        {
            $array['pedidoCredito'] = 0;
        }

        //s($array);
        //die();

        if($this->isDev)
        {
            $url = 'http://api-vallery-test.vallery.com.br/v1/Pedidos/';
        }
        else
        {
            $url = $_ENV["api_vallery_v1"].'/Pedidos';
        }


        $json = Request::factory( $url )
             ->headers('Authorization', 'Basic '.AUTH2)
            ->method('POST')
            ->post($array)
            ->execute()
            ->body();

        $response = json_decode($json,true);
        //s($response);

        return $response;
    }

    private function prepareOrderProducts(array $data, array $order): array
    {
        //s($data,$order);

        $array = null;
        $count = 0;

        foreach ( $data['Lead']['Produtos'] as $k => $v )
        {

            //s($v);
            //die();

            $source  = array('.', ',');
            $replace = array('', '.');
            $produtoValorSubtotal   = str_replace($source, $replace, $v['produtoValorSubtotal'] );
            $produtoCCValorSubtotal = str_replace($source, $replace, $v['produtoCCValorSubtotal'] );
            $produtoSTSubtotal      = str_replace($source, $replace, $v['produtoSTSubtotal'] );
            $produtoIPISubtotal     = str_replace($source, $replace, $v['produtoIPISubtotal'] );
            $produtoDifalSubtotal   = str_replace($source, $replace, $v['produtoDifalSubtotal'] );
            $produtoValorOriginal   = str_replace($source, $replace, $v['produtoValorOriginal'] );
            $produtoValor           = str_replace($source, $replace, $v['produtoValor'] );

            if ( isset( $v['produtoValorCC'] ))
            {
                $produtoValorCC = str_replace($source, $replace, $v['produtoValorCC'] );
            }else{
                $produtoValorCC = 0;
            }

            $margemNominal = 0;

            if ( isset($v['margemNominal']) )
            {
                $margemNominal = $v['margemNominal'];
            }

            $array[$count]['pedidoProdutoQuantidade']  = $v['produtoQuantidade'];
            $array[$count]['pedidoProdutoVezes']       = $v['produtoVezes'];
            $array[$count]['pedidoValor']              = $produtoValor;
            $array[$count]['produtoValorRevenda']      = $produtoValorOriginal;
            $array[$count]['pedidoValorSubtotal']      = $produtoValorSubtotal;
            //$array[$count]['pedidoValorSubtotalIPI'] = $v['produtoIPISubtotal'];
            //$array[$count]['pedidoValorSubtotalST']  = $v['produtoSTSubtotal'];
            $array[$count]['produtoEstoqueSistema']    = $v['stock']['estoqueComercial'];
            $array[$count]['produtoPOID']              = $v['produtoPOID'];
            $array[$count]['produtoST']                = $produtoSTSubtotal;
            $array[$count]['produtoIPI']               = $produtoIPISubtotal;
            $array[$count]['produtoDifal']             = $produtoDifalSubtotal;
            $array[$count]['clientePOID']              = $data['Lead']['clientePOID'];
            $array[$count]['produtoMargemNominal']     = $margemNominal;
            $array[$count]['pedidoPOID']               = $order['id'];

            if ( 'SC' == $data['Lead']['Emitente']['emitenteUF'] )
            {
                 $array[$count]['produtoTTD'] = 1;
            }

            if ( $data['Lead']['segmentoPOID'] == 1 and $data['Lead']['clienteDoClientePOID'] > 0 )
            {
                $array[$count]['pedidoValor']         = $produtoValorCC;
                $array[$count]['produtoValorCliente'] = $produtoValorCC;
                $array[$count]['pedidoValorSubtotal'] = $produtoCCValorSubtotal;
            }

            $count++;
        }

        $array = json_encode($array);
        //s($array);
        //die();

        if($this->isDev)
        {
            $url = 'https://dev.office.internut.com.br/api/vallery/teste/PedidoProdutos/';
        }
        else
        {
            $url = $_ENV["api_vallery_v1"].'/PedidoProdutos';

        }

        $json = Request::factory( $url )
            ->headers(array('Authorization' => 'Basic '.AUTH2, 'Content-Type' => 'application/json'))
            ->method('POST')
            ->body($array)
            ->execute();

        $response = json_decode($json,true);
        //s($json);
        //die();

        return $response;
    }

    private function updateStock(array $data, array $order, $orderID)
    {
        //s($data,$order,$orderID);
        // die();

        $update   = array();
        $emitente = $data['Lead']['Emitente'];
        $nop      = $data['Lead']['Natureza'];

        if ( !  $nop['naturezaOperacaoMovimentaEstoque'] and ! $nop['naturezaTipoOperacao'] )
            die( s('<h3 align="center" style="margin-top:100px">Sem nop correto</h3>'));

        $database = $emitente['DB'];
        $table    = 'Estoque';
        //$field    = 'EstoqueDisponivel';
        $field    = $nop['naturezaTipoEstoque'];

        if (  1 == $data['Lead']['segmentoPOID'] and $data['Lead']['clienteDoClientePOID']  > 0  )
        {
            /* $url = $_ENV["api"].'v1/Pedidos/'.$orderID.'/DetalheProdutos';
            //$url = 'http://api-vallery-test.vallery.com.br/v1/Pedidos/'.$orderID.'/Produtos';

            $json = Request::factory( $url )
                ->headers(array('Authorization' => 'Basic '.AUTH, 'Content-Type' => 'application/json'))
                ->method('GET')
                //->post($data)
                ->execute()
                ->body();

            $order = json_decode($json,true);
            s('new order', $order); */
        }

        if ( $emitente['emitenteUF'] == 'SC' and $nop['naturezaTipoOperacao'] == 'saida' and  $nop['naturezaOperacaoMovimentaEstoque'] == '1' )
        {
            foreach ( $order as $k => $v )
            {
                $sql = sprintf(" SELECT  i.id, modelo,
						d2.EstoqueDisponivel as stock_6_ttd,
						d1.EstoqueDisponivel as stock_6_d1,
						d1.EstoqueDisponivel+d2.EstoqueDisponivel as stock_6
						FROM mak.inv i
						LEFT JOIN mak_0613.Estoque d1 on (i.id = d1.ProdutoPOID)
						LEFT JOIN mak_0613.Estoque_TTD_1 d2 on (i.id = d2.ProdutoPOID)

						WHERE i.id = %s ", $v['produtoPOID']);

                $query = DB::query(Database::SELECT, $sql);
                //$result = $query->execute($this->testAPP)->as_array();
                $result = $query->execute()->as_array();

                $stock_d1  = $result[0]['stock_6_d1'];
                $stock_ttd = $result[0]['stock_6_ttd'];

                if ( $v['pedidoProdutoQuantidade'] < $stock_d1 )
                {
                    $sql = sprintf("UPDATE %s.%s SET %s = %s - %s  WHERE `%s`.`ProdutoPOID` = %s ", $database, $table, $field, $field, $v['pedidoProdutoQuantidade'], $table, $v['produtoPOID']);
                    $query = DB::query(Database::UPDATE, $sql);
                    //$update[] = $query->execute($this->testAPP);
                    $update[] = $query->execute();

                }else{
                    $sql = sprintf(" UPDATE %s.`Estoque` SET `EstoqueDisponivel` = '%s' WHERE `Estoque`.`ProdutoPOID` = %s  ", $database, 0, $v['produtoPOID']);
                    $query = DB::query(Database::UPDATE, $sql);
                    //$update[] = $query->execute($this->testAPP);
                    $update[] = $query->execute();

                    $estoqueRestante = $v['pedidoProdutoQuantidade'] - $stock_d1;

                    $sql = sprintf(" UPDATE %s.`Estoque_TTD_1` SET `EstoqueDisponivel` = EstoqueDisponivel - '%s' WHERE `Estoque_TTD_1`.`ProdutoPOID` = %s  ", $database, $estoqueRestante, $v['produtoPOID']);
                    $query = DB::query(Database::UPDATE, $sql);
                    //$update[] = $query->execute($this->testAPP);
                    $update[] = $query->execute();
                }
            }

        }else{
            // remove estoque
            if ( $nop['naturezaTipoOperacao'] == 'saida' and  $nop['naturezaOperacaoMovimentaEstoque'] == '1')
            {
                foreach ( $order as $k => $v )
                {
                    $sql = sprintf("UPDATE %s.%s SET %s = %s - %s  WHERE `%s`.`ProdutoPOID` = %s ", $database, $table, $field, $field, $v['pedidoProdutoQuantidade'], $table, $v['produtoPOID']);
                    $query = DB::query(Database::UPDATE, $sql);
                    //$update[] = $query->execute($this->testAPP);
                    $update[] = $query->execute();
                }
            }

            // entrada estoque
            if ( $nop['naturezaTipoOperacao'] == 'entrada' and  $nop['naturezaOperacaoMovimentaEstoque'] == '1')
            {
                foreach ( $order as $k => $v )
                {
                    $sql = sprintf("UPDATE %s.%s SET %s = %s + %s  WHERE `%s`.`ProdutoPOID` = %s ", $database, $table, $field, $field, $v['pedidoProdutoQuantidade'], $table, $v['produtoPOID']);
                    $query = DB::query(Database::UPDATE, $sql);
                    //$update[] = $query->execute($this->testAPP);
                    $update[] = $query->execute();
                }
            }
        }

        // s($update);
        return $update;
    }

    //////////////////////////////////////////////
    //    Email Function
    //////////////////////////////////////////////
    private function sendMail( $order, $lead )
    {
        $url= sprintf('/order/mail/%s', $order['id']);

        $html = Request::factory($url)
            ->execute()
            ->body();

        switch ( $order['segmentoPOID'] )
        {
            case 1:
                $name = 'Máquinas';
                $fields["addBCC"][] = 'pedidos-ecommerce-maquinas@vallery.com.br';
                break;
            case 2:
                $name = 'Rolamentos';
                $fields["addBCC"][] = 'pedidos-ecommerce-rolamentos@vallery.com.br';
                break;
            case 3:
                $name = 'Peças';
                $fields["addBCC"][] = 'pedidos-ecommerce-rolamentos@vallery.com.br';
                break;
            case 4:
                $name = 'Metais';
                $fields["addBCC"][] = 'ronaldrr@rolemak.com.br';
                break;
            case 5:
                $name = 'Autopeças';
                $fields["addBCC"][] = 'pedidos-ecommerce-autopecas@vallery.com.br';

                if ( isset($lead['Lead']['Vendedor']['UsuarioSegmento']) and $lead['Lead']['Vendedor']['UsuarioSegmento'] == 'bearings')
                {
                    $fields["addBCC"][] = 'camilacb@vallery.com.br';
                    $fields["addBCC"][] = 'tarsog@vallery.com.br';
                }

                break;
            case 6:
                $name = 'Motopeças';
                $fields["addBCC"][] = 'pedidos-ecommerce-moto@vallery.com.br';

                if ( isset($lead['Lead']['Vendedor']['UsuarioSegmento']) and $lead['Lead']['Vendedor']['UsuarioSegmento'] == 'bearings')
                {
                    $fields["addBCC"][] = 'camilacb@vallery.com.br';
                }
                break;
        }

        $fields["Subject"]      = utf8_decode(sprintf('Confirmação do Pedido de %s nº %s - %s', $name, $order['id'], $lead['Lead']['Emitente']['emitenteFantasia'] ));
        $fields["addAddress"][] = $lead['Lead']['Cliente']['clienteEmail'];

        if (isset($lead['Lead']['Cliente']['Gerente']['UsuarioEmailInterno']))
        {
            $fields["addBCC"][]     = $lead['Lead']['Cliente']['Gerente']['UsuarioEmailInterno'];
            $fields["addReplyTo"][] = $lead['Lead']['Cliente']['Gerente']['UsuarioEmailInterno'];
        }

        if ( isset($lead['Lead']['Vendedor']['UsuarioEmailInterno']) )
        {
            $fields["addBCC"][] = $lead['Lead']['Vendedor']['UsuarioEmailInterno'];
        }

        $fields["addBCC"][] = 'rogeriobbvn@rolemak.com.br';

        $fields["SMTPDebug"] = 0;
        $fields["isHTML"]    = true; // Set email format to HTML
        $fields["Body"]      = utf8_decode($html);
        $fields["AltBody"]   = 'Rolemak';

        $email = $this->sendMailService( $fields );

        //Clientes Específicos autoriza direto, autorizado pelo Financeiro
        if ( 1 == $order['unidadeEmitentePOID'] and 1 == $order['AutorizaLogistica']  )
        {
            $mailauth = self::sendMailAuth( $order, $lead );
        }
    }

    private function sendMailAuth( $order, $lead )
    {
        if ( isset($lead['Lead']['Vendedor']['UsuarioEmailInterno']) )
        {
            $fields["addBCC"][] = $lead['Lead']['Vendedor']['UsuarioEmailInterno'];
        }

        if ( isset($lead['Lead']['Vendedor']['UsuarioEmailInterno']) )
        {
            $fields["addBCC"][] = $lead['Lead']['Vendedor']['UsuarioEmailInterno'];
        }

        if (isset($lead['Lead']['Cliente']['Gerente']['UsuarioEmailInterno']))
        {
            $fields["addBCC"][]     = $lead['Lead']['Cliente']['Gerente']['UsuarioEmailInterno'];
            $fields["addReplyTo"][] = $lead['Lead']['Cliente']['Gerente']['UsuarioEmailInterno'];
        }

        $fields["addAddress"][] = 'rafaells@vallery.com.br';

        $fields["Subject"] = utf8_decode(sprintf('Pedido Autorizado nº %s', $order['id'] ));

        $html = "Pedido ".$order['id']." foi autorizado direto, autorizado pelo departamento financeiro";
        $html.="<br> Cliente ".$lead['Lead']['Cliente']['clienteNome']."'";
        $html.="<br> <a href='http://office.vallery.com.br/crm/v4/expedition/order/".$order['id']."' target='_blank'>imprimir pedido</a>";

        $fields["SMTPDebug"] = 0;
        $fields["isHTML"]    = true; // Set email format to HTML
        $fields["Body"]      = utf8_decode($html);
        $fields["AltBody"]   = 'Rolemak';

        //SEND EMAIL VIA WEBSERVICE
        $email = Request::factory( $_ENV['sendmail'] )
            ->method(Request::POST)
            ->post($fields)
            ->execute()
            ->body();

        s($email);

        if ( 1 == $email)
        {
            $sql = sprintf("INSERT IGNORE INTO `webteam`.`hoje_tempo` ( `order_id`, `date_authorized` ) VALUES ('%s','%s'); ", $order['id'], $order['pedidoDataEmisao'] );
            $query = DB::query(Database::UPDATE, $sql);
            //$update = $query->execute($this->testAPP);
            $update = $query->execute();
        }

        return $email;
    }

    //////////////////////////////////////////////
    //    Parts  Function
    //////////////////////////////////////////////
    public function getSale($idcli, $segid, $userid)
    {
        $vendedor = $userid;

        $sql = sprintf(" SELECT * FROM mak.`clientes_vendedores` WHERE `idcli` = %s and `idseg` = %s and ativo = 1 LIMIT 1 ",  $idcli, $segid);

        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute()->as_array();

        if ( isset($result[0]))
        {
            $vendedor =  $result[0]['idven'];
        }

        return $vendedor;
    }

    //////////////////////////////////////////////
    //    Machines Function
    //////////////////////////////////////////////
    private function updateTSM(array $products, $order)
    {
        foreach ( $products as $k => $v )
        {
            self::getTSM( $v['produtoPOID'], $v['pedidoProdutoQuantidade'], $order['id'] );
        }
    }

    private function getTSM($id,$qty,$order_id)
    {
        $sql = sprintf("SELECT tampo, motor	FROM mak.inv WHERE inv.id ='%s'", $id);
        $query = DB::query(Database::SELECT, $sql);
        //$result = $query->execute($this->testAPP)->as_array();
        $result = $query->execute()->as_array();

        s($result);

        if( isset($result[0]['tampo'])  and $result[0]['tampo']>0 )
        {
            if($this->check_tsm( $result[0]['tampo'], $order_id))
            {
                $this->update_tsm( $order_id, $result[0]['tampo'], $qty );
            }else{
                $this->insert_tsm( $order_id, $result[0]['tampo'], $qty );
            }
        }

        if( isset($result[0]['motor'])  and $result[0]['motor']>0 )
        {
            if($this->check_tsm( $result[0]['motor'], $order_id))
            {
                $this->update_tsm( $order_id, $result[0]['motor'], $qty);
            }else{
                $this->insert_tsm( $order_id, $result[0]['motor'], $qty);
            }
        }
    }

    private function check_tsm($product_id='1',$order_id='100000')
    {
        $sql = sprintf("SELECT isbn FROM  mak.hist WHERE isbn='%s'  AND pedido ='%s'", $product_id,$order_id);
        $query = DB::query(Database::SELECT, $sql);
        //$result = $query->execute($this->testAPP)->as_array();
        $result = $query->execute()->as_array();
        if(count($result)>0)  return true;
        return false;
    }

    private function insert_tsm($order_id=1,$product_id=1647509,$quantity=1)
    {
        echo $sql ="INSERT INTO mak.hist
								  (
									 pedido ,
									 isbn   ,
									 quant  ,
									 valor  ,
									 entrada,
									 vezes
									 )
								 VALUES(
									 '".$order_id."',
									 '".$product_id."',
									 '".$quantity."',
									 '0',
									 '0',
									 '0'
								 )";
        $query = DB::query(Database::INSERT, $sql);
        //$insert = $query->execute($this->testAPP);
        $insert = $query->execute();

        s($insert);
    }

    private function update_tsm($order_id=100000,$product_id=1647509,$quantity=1)
    {
        $sql = sprintf("UPDATE mak.hist SET  quant= quant+%s WHERE pedido='%s' AND isbn = '%s' ",$quantity,$order_id,$product_id);
        $query = DB::query(Database::UPDATE, $sql);
        //$insert = $query->execute($this->testAPP);
        $update = $query->execute();
    }

    private function redirect_error($error_id, $data, $segment)
    {
        $redirect  = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].''.$_SERVER['HTTP_X_FORWARDED_PREFIX'];

        return $this->redirect( $redirect.'order/checkout/'.$data['Lead']['id'].'/'.$segment.'?error='.$error_id, 302);
    }

}