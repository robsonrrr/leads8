<?php
class Controller_Order_Get extends Controller {

    public function action_index()
    {
        $uri = sprintf('{
          allPedidos(where: {id: %s}) {
          edges {
              node {
                id
                segmentoPOID
                clientePOID
                clienteUsuarioPOID
                naturezaOperacaoPOID
                unidadeEmitentePOID
                unidadeLogisticaPOID
                AutorizaLogistica
                tipoPagamentoPOID
                vendedorPOID
                tipoTransportePOID
                transportadoraPOID
                pedidoDataEmisao
                pedidoDataEntregaProgramada
                pedidoPrazo
                pedidoNovoPrazo
                pedidoValor
                pedidoProdutoValorBase
                pedidoValorST
                pedidoValorIPI
                pedidoValorImpostos
                pedidoValorFrete
                pedidoValorTotal
                pedidoCredito
                pedidoColeta
                pedidoValorComissao
                pedidoObservacao
                pedidoObservacaoFinanceiro
                pedidoObservacaoLogistica
                pedidoObservacaoNfe
                pedidoInfoAdicional
                pedidoStatus
                pedidoPlaca
                pedidoPlacaUF
                pedidoVolumes
                pedidoPeso
                clienteDeClientePOID
                pedidoFontePOID
                clienteDeCliente {
                 edges {
                    node {
                      id
                      clienteID
                      clienteNome
                      clienteEndereco
                      clienteNumero
                      clienteBairro
                      clienteCidade
                      clienteEstado
                      clienteComplemento
                      clienteCep
                      clienteCnpj
                    }
                  }
                }
                Prazo {
                  edges {
                    node {
                      id
                      PrazoTermos             
                    }
                  }
                }        
                Natureza {
                  edges {
                    node {
                        id
                        naturezaOperacao
                        naturezaOperacaoNotaFiscaL
                        naturezaOperacaoCFOP
                        naturezaOperacaoCstICMS
                        naturezaOperacaoCstIPI
                        naturezaOperacaocCstPIS
                        naturezaOperacaocCstPIS
                        naturezaOperacaoCstCOFINS
                        naturezaOperacaoObservacaoNFE
                        naturezaOperacaoFormaPagamento
                        naturezaTipoOperacao   
                        naturezaTipoEstoque
                        naturezaFinalidadeNFE
                        }
                  }
                }
                Emitente {
                  edges {
                    node {
                      id
                      emitenteFantasia
                      emitenteCNAE
                      emitentePais
                      emitenteCodigoPais
                      emitentecUF
                      emitenteUF
                      emitenteMunicipio
                      emitenteCodigoMunicipal
                      emitenteDDD
                      emitenteTelefone
                      emitenteIM
                      emitenteIE
                      emitenteCEP
                      emitenteBairro
                      emitentexCpl
                      emitenteNumero
                      emitenteLogradouro
                      emitenteRazao
                      emitentetpAmb
                      emitenteCnpj
                    }
                  }
                }
                Cliente {
                  edges {
                    node {
                      id
                      clienteCnae
                      clienteNome
                      clienteFantasia
                      clienteEndereco
                      clienteNumero
                      clienteComplemento
                      clienteBairro
                      clienteCidade
                      clienteEstado
                      clienteCep
                      clienteInscricaoMunicipal
                      clienteInscricaoEstadual
                      clienteIsentoInscricaoEstadual
                      clienteIsentoIPI
                      clienteIsentoICMS
                      clienteRegimeFiscal
                      clienteRegimeEspecial
                      clienteConsumidorFinal
                      clienteDifal
                      clienteSuframa
                      clienteSuframaNumero
                      clienteSuframaData
                      clienteRg
                      clienteCnpj
                      clienteCpf
                      clienteTipoPessoa
                      clienteIsentoSt
                      clienteEmail
                      clienteEmailFinanceiro
                      clienteEmailNFE
                      clienteDdd
                      clienteTelefone
                      clienteBNDES
                    }
                  }
                }
                Transportadora {
                  edges {
                    node {
                      id
                      transportadoraPOID
                      transportadoraNome
                      transportadoraEndereco
                      transportadoraBairro
                      transportadoraCidade
                      transportadoraEstado
                      transportadoraCnpj
                    }
                  }
                }
                Pagamentos {
                  edges {
                    node {
                      id
                      pagamentoDataVencimento
                      pagamentoValor

                    }
                  }
                }        
                TipoPagamento {
                  edges {
                    node {
                      pagamentoPOID
                      pagamentoNome

                      pagamentoDescricao
                    }
                  }
                }
                TipoTransporte {
                  edges {
                    node {
                      transporteID
                      transporteNome
                      segmentoPOID
                      transporteDescricao
                    }
                  }
                }
                PedidoStatus {
                  edges {
                    node {
                      statusPOID
                      statusNome
                    }
                  }
                }
                Vendedor {
                  edges {
                    node {
                      id
                      UsuarioNomeCurto
                      UsuarioNome
                      UsuarioEmail
                      UsuarioSegmento
                      UsuarioEmailInterno
                    }
                  }
                }
                DetalheProdutos {
                  edges {
                    node {
                      id
                      pedidoProdutoQuantidade
                      pedidoProdutoVezes
                      pedidoPOID
                      pedidoValor
                      pedidoProdutoValorBase
                      pedidoValorSubtotalIPI
                      pedidoValorSubtotalST
                      produtoPOID
                      produtoST
                      produtoIPI
                      produtoDifal
                      clientePOID
                      Produto {
                        edges {
                          node {
                            produtoPOID
                            produtoModelo
                            produtoSEO
                            produtoNome
                            produtoMarca
                            produtoRevenda
                            produtoEmbalagem
                            produtoVIP
                            produtoPeso
                            produtoFob
                            produtoCodigoBarras
                            produtoOrigem
                            produtoST
                            produtoIsentoST
                            produtoICMSantecipado
                            produtoCST                    
                            segmentoPOID
                            Segmento {
                              edges {
                                node {
                                  segmentoPOID
                                  segmentoNome
                                  segmentoCategoria
                                  segmentoNCM                         
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }        
              }
            }
          }
        }
        ',$this->request->param('id'));

        $url = $_ENV['api_vallery_v1'].'/gql?query='.urlencode($uri);

        $response = Request::factory( $url )
            ->headers( array ( 'Authorization' => 'Basic '.base64_encode($_ENV["auth"])))
            ->method('GET')					
            ->execute()
            ->body();

        $this->response->body($response);
    }


}