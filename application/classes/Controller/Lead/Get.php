<?php
class Controller_Lead_Get extends Controller_Website {

    public function action_index()
    {
        // Simple test to see if controller is reached
        if ($this->request->param('id') === 'test') {
            $this->response->body(json_encode(['message' => 'Controller reached successfully']));
            return;
        }
        
        // Enable error reporting for debugging
        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        error_reporting(E_ALL);
        
        $benchmark = Profiler::start('GET', __FUNCTION__);
        $url = $_ENV["api_vallery_v1"]."/gql/?query=";

        $uri=sprintf("{
          Lead(id: %s) {
            id
            dataEmissao
            dataEntrega
            naturezaOperacaoPOID
            clientePOID
            usuarioPOID
            leadTipo
            leadFonte
            leadAutorizado
            vendedorPOID
            clienteDoClientePOID
            tipoPagamentoPOID
            prazoPagamentoVezes
            transportadoraPOID
            unidadeEmitentePOID
            unidadeLogisticaPOID
            tipoFrete
            valorFrete
            observacaoFinaceiro
            observacaoLogistica
            observacaoNotaFiscal
            observacaoInterna
            nomeComprador
            ordemDeCompra
            pedidoPOID
            Natureza {
              edges {
                node {
                  id
                  naturezaOperacao
                  naturezaOperacaoNotaFiscaL
                  naturezaOperacaoCFOP
                  naturezaOperacaoObservacaoFinanceiro
                  naturezaTipoOperacao
                  naturezaOperacaoFormaPagamento
                  naturezaOperacaoMovimentaEstoque
                  naturezaOperacaoObservacaoNFE
                  naturezaTipoEstoque
                  naturezaFinalidadeNFE
                }
              }
            }
            Prazo{
              edges {
                node {
                 id
                 PrazoTermos
                }
              }
            }
            Vendedor {
              edges {
                node {
                  id
                  UsuarioNome
                  UsuarioSegmento
                  UsuarioCargo
                  UsuarioNomeCurto
                  UsuarioEmail
                  UsuarioEmailInterno
                  UsuarioCelular
                  UsuarioSkype
                }
              }
            }
            Emitente {
              edges {
                node {
                  id
                  emitentePOID
                  emitenteFantasia 
                  emitenteCNAE
                  emitentecUF
                  emitenteUF
                  emitenteMunicipio
                  emitenteCnpj
                  emitenteRazao
                  emitenteIE
                  emitenteCodigoMunicipal
                  emitenteLogradouro
                  emitenteNumero
                  emitenteBairro
                  emitenteCEP
                }
              }
            }
            TipoPagamento {
              edges {
                node {
                  pagamentoPOID
                  pagamentoNome
                  pagamentoDescricao
                  pagamentoSobretaxa
                  pagamentoInteresse
                  pagamentoVezes
                }
              }
            }
            Transportadora {
              edges {
                node {
                  id
                  transportadoraPOID
                  transportadoraNome
                  transportadoraEstado
                  transportadoraCnpj
                  transportadoraAtiva
                  transportadoraTelefone
                }
              }
            }
            Pagamentos {
              edges {
                node {
                  id
                  pedidoPOID
                  pagamentoDataVencimento
                  pagamentoDataEmissao
                  pagamentoDataRecebido
                  pagamentoValor
                  pagamentoLancadoPorPOID
                }
              }
            }
            Cliente {
              edges {
                node {
                  id
                  clienteID
                  clienteCnae
                  clienteNome
                  clienteNumero
                  clienteFantasia
                  clienteCnpj
                  clienteInscricaoMunicipal
                  clienteInscricaoEstadual
                  clienteRegimeFiscal
                  clienteEstado
                  clienteSegmentoPOID
                  clienteLimite
                  clientePrazo
                  clienteDifal
                  clienteDesconto
                  clienteDescontoMakAutomotive
                  clienteJuros
                  clienteSobretaxa
                  clienteBNDES
                  clienteTipoPessoa
                  clienteSuframa
                  clienteIsentoIPI
                  clienteIsentoSt
                  clienteICMSSTEspecial
                  clienteEndereco
                  clienteBairro
                  clienteCidade
                  clienteEstado
                  clienteCep
                  clienteEmail
                  clienteEmailNFE
                  clienteEmailFinanceiro
                  clienteDdd
                  clienteTelefone
                  clienteGerentePOID
                  clienteDescontoPedido
                  Segmento {
                      edges {
                        node {
                          id
                          segmentoNome
                        }
                      }
                    }
                  Hierarquia {
                      edges {
                        node {
                          id
                          clienteHierarquia
                          clienteHierarquiaDescricao
                        }
                      }
                    }
                  Gerente {
                      edges {
                        node {
                          id
                          UsuarioNome
                          UsuarioSegmento
                          UsuarioCargo
                          UsuarioNomeCurto
                          UsuarioEmail
                          UsuarioEmailInterno
                          UsuarioCelular
                          UsuarioSkype
                        }
                      }
                    }
                }
              }
            }
            Produtos {
              totalCount
              LeadProdutos {
                POID
                leadPOID
                dataEmissao
                produtoTTD
                produtoPOID
                produtoSimilar
                produtoQuantidade
                produtoValor
                produtoValorClientedeCliente
                produtoValorOriginal
                produtoST
                produtoIPI
                produtoDifal
                produtoVezes
                Detalhe {
                  edges {
                    node {
                      id
                      produtoPOID
                      produtoModelo
                      produtoOrigem
                      produtoST
                      produtoIsentoST
                      produtoICMSantecipado
                      produtoNome
                      produtoPeso
                      produtoMarca
                      produtoRevenda
                      produtoFob
                      produtoMedida
                      produtoEmbalagem
                      produtoClassificacao
                      Segmento {
                        edges {
                          node {
                            id
                            segmentoPOID
                            segmentoNCM
                            segmentoCategoria
                            segmentoNome
                            ncmID
                          }
                        }
                      }
                     Promo{
                         ProdutoPromo {
                           produtoPOID
                           produtoDesconto
                         }
                     }
                     Classificacao {
                        edges {
                          node {
                            id
                            classificacaoNome
                            classificacaoDescontoVendedor
                            classificacaoDescontoGerente
                            classificacaoPercentual
                            classificacaoAtivo
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
        ", $this->request->param('id'));

        // Remove debug code for production
        
        try {
            $final_url = $url . urlencode($uri);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $final_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . AUTH]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $api_response_body = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($curl_error) {
                throw new Exception('cURL Error: ' . $curl_error);
            }

            if ($http_status !== 200) {
                throw new Exception('API returned HTTP status ' . $http_status . '. Response: ' . $api_response_body);
            }

            if (empty($api_response_body)) {
                throw new Exception('Empty response from API');
            }

            $decoded_response = json_decode($api_response_body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Failed to decode JSON response: ' . json_last_error_msg());
            }

            $response = $api_response_body;

        } catch (Exception $e) {
            $debug = [
                'exception' => $e->getMessage(),
                'final_url' => $final_url ?? 'Not set',
                'http_status' => $http_status ?? 'Not set',
                'curl_error' => $curl_error ?? 'Not set',
                'api_response_body' => $api_response_body ?? 'Not set',
            ];

            // Log the detailed error
            Kohana::$log->add(Log::ERROR, 'Lead Get Error: ' . json_encode($debug));

            // Prepare a structured error response
            $this->response->status(500);
            $response = json_encode(['debug' => $debug, 'error' => $e->getMessage()]);
        }

        $this->response->body($response);
    }
}