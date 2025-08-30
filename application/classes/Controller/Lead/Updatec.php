<?php
class Controller_Lead_Updatec extends Controller_Website {

    public function action_index()
    {
        $this->auto_render = FALSE;
        
        $leadPOID = $this->request->param('id');
        $get = $this->request->query();
        
        if ( ! isset($get) )
            die(s('mandar $post'));
        
        if ( empty($get['products']) )
            die(s('mandar $products'));
        
        //s($leadPOID,$get);
        
        $type = $get['type'];
        
        //s($type);
        
        $explode = explode('&', $get['products']);
        
        //s($explode);
        
        $remove = array();
        
        if ( $type == 'remove')
        {
            foreach ( $explode as $k => $v )
            {
                $poid = explode('=', $v);
                $remove[] = self::remove( $poid[1] );
            }
        }
        
        if ( $type == 'add')
        {
            $json = Request::factory( '/lead/get/'.$leadPOID )
                ->method('GET')					
                ->execute()
                ->body();
            
            $lead = json_decode( $json, true);
            
            foreach ( $lead['data']['Lead']['Produtos']['LeadProdutos'] as $k => $v )
            {
                $count = 0;
                
                foreach ( $explode as $kk => $vv )
                {
                    $poid = explode('=', $vv);
                    
                    s( $poid[1], $v );
                    
                    if ( $v['POID'] == $poid[1] )
                    {
                        $count = 1;
                    }
                }
                
                if ( $count == 0 )
                {
                    s('deletar produto'.$v['POID'] );
                    $remove[] = self::remove( $v['POID'] );
                }
                
                if ( $count == 1 )
                {
                    s('manter produto');
                }
            }
            
            s($remove);
        }
        
        if( $type == "notify" )
        {
            self::saveProductsInTable($type, $leadPOID, $explode);
        }
        
        if( $type == "lost" )
        {
            
            self::saveProductsInTable($type, $leadPOID, $explode);
        }
        //die();
        //s($remove);

        return $this->response->body(json_encode(true));
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
    
    private function saveProductsInTable($type, $leadID, $products)
    {
        $data = self::getLeadProductsInfo($leadID, $products);
        $exists = self::checkIfExistsInTable($type, $data);
        
        if($exists)
        {
            $data['products'] = array_diff($data['products'], $exists);
        }
        $list = self::formatDataToInsert($data);
        
        if(!$list)
        {
            return false;
        }
        
        $table = "";
        if($type === "notify")
        {
            $table = "`Ecommerce`.`aviseme`";
        }
        elseif( $type === "lost" )
        {
            $table = "`Ecommerce`.`itens_perdidos`";
        }
        
        $sql= sprintf("INSERT INTO %s (id_unidade, id_cliente, id_usuario, id_produto, data) VALUES %s", $table, implode(", ",$list));
        
        $query = DB::query(Database::INSERT, $sql);
        $insert = $query->execute();
        
        return $insert;
        
    }
    
    private function formatDataToInsert($data)
    {
        $user   = $_SESSION['MM_Userid'];
        $values = [];
        
        if(empty($data['products']))
        {
            return false;
        }
        
        foreach($data['products'] as $v)
        {
            $now = date("Y-m-d H:i:s");
            $values[] = sprintf("('%s','%s','%s','%s','%s')", $data['unity'], $data['client'], $user, $v, $now);
        }
        
        return $values;
    }
    
    private function checkIfExistsInTable($type, $data)
    {
        $table = "";
        if($type === "notify")
        {
            $table = "`Ecommerce`.`aviseme`";
        }
        elseif( $type === "lost" )
        {
            $table = "`Ecommerce`.`itens_perdidos`";
        }
        
        $sql = sprintf(" SELECT id_produto
						FROM  %s
						WHERE id_cliente='%s'
                        AND id_unidade='%s'
                        AND id_produto IN (%s)
                        AND status=1", $table, $data['client'], $data['unity'], implode(",", $data['products']));

        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute()->as_array(); 
        
        if(!$result)
        {
            return false;
        }
        
        $ids = [];
        
        foreach($result as $k => $v)
        {
            $ids[] = $v['id_produto'];
        }
        
        return $ids;
    }
    
    private function getLeadProductsInfo($leadID, $products)
    {
        
        $json = Request::factory( '/lead/get/'.$leadID )
                ->method('GET')					
                ->execute()
                ->body();

        $lead = json_decode( $json, true);
        
        if(!isset($lead['data']['Lead']['Produtos']['LeadProdutos']))
        {
            return false;
        }
        
        $unity = $lead['data']['Lead']['unidadeLogisticaPOID'];
        $client = $lead['data']['Lead']['clientePOID'];
        $lead_products =  $lead['data']['Lead']['Produtos']['LeadProdutos'];
        $ids = [];
            
        foreach( $products as $k => $v )
        {
            $id=str_replace("product=", "", $v);
            $found_key = array_search($id, array_column($lead_products, 'POID'));
                
            if($found_key === false)
            {
                continue;
            }
                
            $ids[] = $lead_products[$found_key]['produtoPOID'];
        }
        
        return [
            "unity" => $unity,
            "client" => $client,
            "products" => $ids
        ];
    }
}