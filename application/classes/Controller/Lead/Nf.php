    <?php
class Controller_Lead_Nf extends Controller_Lead_Base {

    public function before()
    {
        parent::before();
    }

    public function action_index()
    {
        $leadID    = $this->request->param('id');
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

        $response['query']  = $this->request->query('q');
        $response['leadID'] = $leadID;

        //s($response);
        //die();

        if ( empty($response['Lead']['Produtos'] ))
            die('sem produto');

        foreach ( $response['Lead']['Produtos'] as $k => $v )
        {
            $source  = array('.', ',');
            $replace = array('', '.');
            $product['Preco']['precoVista']      =  str_replace($source, $replace, $v['produtoValor'] );

            $product["produtoPOID"]              = $v['produtoPOID'];
            $product["produtoIsentoST"]          = $v['Detalhe']['produtoIsentoST'];
            $product['Segmento']['segmentoNCM']  = $v['Detalhe']['Segmento']['segmentoNCM'];
            $product['Segmento']['segmentoNome'] = $v['Detalhe']['Segmento']['segmentoNome'];
            $product["produtoST"]                = $v['Detalhe']['produtoST'];
            $product["produtoOrigem"]            = $v['Detalhe']['produtoOrigem'];
            $product["produtoICMSantecipado"]    = $v['Detalhe']["produtoICMSantecipado"];

            $response['Lead']['Produtos'][$k]['Impostos'] = $this->get_tax( $response['Lead'], $product );
        }

        if ( $response['Lead']['Emitente']['emitenteUF'] <> $response['Lead']['Cliente']['clienteEstado'] )
        {
            $response['cfop'] = 6;
        }else{
            $response['cfop'] = 5;
        }

        //s($tax);
        //die();

        $icms    = 0;
        $icms_st = 0;

        //s($response['Lead']['Produtos']);
        //die();

        foreach ( $response['Lead']['Produtos'] as $k => $v )
        {
            //s($v);

            $icms     += $v['Impostos']['valor_subtotal_icms_proprio']*$v['produtoQuantidade'];
            $response['Lead']['Produtos'][$k]['Impostos']['valor_subtotal_icms_proprio'] = str_replace(".",",",round($v['Impostos']['valor_subtotal_icms_proprio']*$v['produtoQuantidade'], 2));

            $icms_st  += $v['Impostos']['valor_subtotal_base_de_calculo_st']*$v['produtoQuantidade'];
            $response['Lead']['Produtos'][$k]['Impostos']['valor_subtotal_base_de_calculo_st'] =str_replace(".",",",round($v['Impostos']['valor_subtotal_base_de_calculo_st']*$v['produtoQuantidade'],2));
        }

        $response['Lead']['Total']['icms']    = round($icms,2);
        $response['Lead']['Total']['icms_st'] = round($icms_st,2);

        $cnpj = $response['Lead']['Emitente']['emitenteCnpj'];
        $cep  = $response['Lead']['Emitente']['emitenteCEP'];

        $cnpj_1 = substr($cnpj,0,2);
        $cnpj_2 = substr($cnpj,2,3);
        $cnpj_3 = substr($cnpj,5,3);
        $cnpj_4 = substr($cnpj,8,4);
        $digito = substr($cnpj,-2);
        
        $cep_1 = substr($cep,0,5);
        $cep_2 = substr($cep,5,3);

        $response['Lead']['Emitente']['emitenteCnpj'] = $cnpj_1.".".$cnpj_2.".".$cnpj_3."/".$cnpj_4."-".$digito;
        $response['Lead']['Emitente']['emitenteCEP'] = $cep_1."-".$cep_2;

        //s($response);

        return $this->render( 'lead/nf', $response );
    }
}