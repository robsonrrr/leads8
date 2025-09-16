<?php
class Controller_Lead_Clean extends Controller_Website {

    public function action_index()
    {
        $leadID    = $this->request->param('id');
        $segmentID = $this->request->param('segment');


        if ($_SESSION['MM_Nivel'] < 4)
        {
            // die('<h1>Acesso não autorizado, falar com Rogério</h1>');
        }

        if ( ! $leadID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $leadID</h3>'));

        $data = array( 
            'check'   => true,
            'segment' => $segmentID
        );

        $json = Request::factory( '/lead/build/'.$leadID )
            ->method('GET')
            ->query($data)
            ->execute()
            ->body();

        $response = json_decode($json,true);

        //s($_SESSION['MM_Userid']);
        //die();

        $sql= sprintf("SELECT count(*) as exist FROM webteam.`products_zero` where  fk_inquiry_id = '%s' LIMIT 1", $leadID );

        $query = DB::query(Database::SELECT, $sql);
        $check = $query->execute()->as_array();

        if ( isset($check[0]['exist']) and $check[0]['exist'] > 0 )
        {
            s("<h1>Já foi gravado os itens desse lead</h1>");
            die();
        }

        //s($check);
        //die();

        foreach( $response['Lead']['Produtos'] as $k => $v )
        {
            if ( $v['produtoQuantidade'] > $v['stock']['estoqueComercial'] )
            {
                $produtoQuantidade =  $v['produtoQuantidade'] - $v['stock']['estoqueComercial'];

                // s($v,$produtoQuantidade);
                // die();

                $sql= sprintf("INSERT INTO webteam.`products_zero`
                                        ( `fk_log_id`, `fk_inquiry_id`, `fk_product_id`, `fk_user_id`, `ipz_quantity`, `ipz_price`, `ipz_type`, `ipz_date`, `ipz_date_updated`)
                                        VALUES ( '0', '%s','%s', '%s', '%s', '%s', '2', '%s', '%s')", 
                              $v['leadPOID'], $v['produtoPOID'], $_SESSION['MM_Userid'], $produtoQuantidade, $v['produtoValor'], date("Y-m-d H:i:s"), date("Y-m-d H:i:s") );

                $query = DB::query(Database::INSERT, $sql);
                $negative = $query->execute();
                s($negative);

                if ( $v['stock']['estoqueComercial'] > 0)
                {
                    // $update = self::update( $v['POID'], $v['stock']['estoqueComercial'] );
                    // s($update);
                }else{
                    //$remove = self::remove( $v['POID'] );
                    //s($remove);

                    //if ( $remove )
                    // {
                    //INSERT INTO `products_zero` (`ipz_id`, `fk_log_id`, `fk_inquiry_id`, `fk_product_id`, `ipz_quantity`, `ipz_price`, `ipz_type`) VALUES ('1', '1', '1', '1', '1', '1', '2');
                    /* $sql= sprintf("INSERT INTO webteam.`products_zero`
                                        ( `fk_log_id`, `fk_inquiry_id`, `fk_product_id`, `fk_user_id`, `ipz_quantity`, `ipz_price`, `ipz_type`)
                                        VALUES ( '0', '%s','%s', '%s', '%s', '%s', '2')", 
                                  $v['leadPOID'], $v['produtoPOID'], $_SESSION['MM_Userid'], $produtoQuantidade, $v['produtoValor'] ); */

                    //$query = DB::query(Database::INSERT, $sql);
                    //$negative = $query->execute();
                    // s($negative);
                    // }
                }
                //die();
            }
        }

        s("<h1>Função em teste caso der erro me mande uma print</h1>");

        return true;
    }

    private function update( $POID, $qty)
    {
        $data = array(
            'produtoQuantidade' => $qty,
        );

        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/'.$POID;

        $json = Request::factory( $url )
            ->method('PUT')
            ->post($data)
            ->execute()
            ->body();

        return json_decode($json,true);
    }

    private function remove( $POID )
    {
        $url = $_ENV["api_vallery_v1"].'/LeadProdutos/'.$POID;

        $json = Request::factory( $url )
            ->method('DELETE')
            ->execute()
            ->body();

        return json_decode($json,true);
    }

}