<?php

class Controller_Lead_Add extends Controller_Website {

    public function action_index()
    {
        $post['clientePOID'] = $this->request->param('id') ;
        $post['usuarioPOID'] = $_SESSION['MM_Userid'];
        $get                 = $this->request->query();

        s($post,$get);
        die();

        $lead = self::add($post);
        //s($lead);

        if ( $get )
        {
            if ( isset( $get['redirect'] ) )
            {
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $prefix = isset($_SERVER['HTTP_X_FORWARDED_PREFIX']) ? $_SERVER['HTTP_X_FORWARDED_PREFIX'] : '';
                $url = $protocol."://".$host.$prefix.$lead['id'];
                $this->redirect($url, 302);
            }
        }

        // s($post, $lead);
        // die();

        return $this->response->body( json_encode( $lead['id'] ) );
    }

    private function add($post)
    {
        $data = array(
            "dataEmissao"           => date('Y-m-d H:m:s'),
            "dataEntrega"           => date('Y-m-d'),
            "naturezaOperacaoPOID"  => 27,
            "clientePOID"           => $post['clientePOID'],
            "usuarioPOID"           => $_SESSION['MM_Userid'],
            "vendedorPOID"          => $_SESSION['MM_Userid'],
            "clienteDoClientePOID"  => 0,
            "tipoPagamentoPOID"     => 1,
            "prazoPagamentoVezes"   => 12,
            "prazoPagamentoNovo"    => "",
            "transportadoraPOID"    => "9",
            "unidadeEmitentePOID"   => "1",
            "unidadeLogisticaPOID"  => "1",
            "observacaoFinaceiro"   => "",
            "observacaoLogistica"   => "",
            "observacaoNotaFiscal"  => "",
            "valorFrete"            => 0
        );

        $url = $_ENV["api_vallery_v1"].'/Leads/';

        $json = Request::factory( $url )
            ->method('POST')
            ->post($data)
            ->execute()
            ->body();

        return json_decode($json,true);
    }


}