<?php
class Controller_Product_Ecommerce extends Controller_Website {

    public function action_index()
    {
        $product  = $this->request->param('id');
        $unity    = $this->request->param('id2');

        if (  ! $product )
        {
            die( s('<h3 align="center" style="margin-top:100px">Sem $product</h3>'));
        }

        $where = null;

        if ( $product )
        {
            $where.=sprintf(' AND product_id = %s ', $product);
        }

        if ( $unity )
        {
            $where.=sprintf(' AND unity_id = %s ', $unity);
        }

        $sql= sprintf(" SELECT
                            cart.*, session_data.*, clientes.nome as cliente, inv.nome as produto, inv.modelo, Emitentes.Fantasia
                        FROM Ecommerce.`cart` 
                            LEFT JOIN Ecommerce.session_data ON (session_data.session_id=cart.session_id)
                            LEFT JOIN clientes on (clientes.id=cart.customer_id)
                            LEFT JOIN inv on (inv.id=cart.product_id)
                            LEFT JOIN Emitentes on (Emitentes.EmitentePOID=cart.unity_id)
                        WHERE 1=1 
                            %s
                            AND session_data.session_data <> 'null'
                        ORDER BY `cart`.`cart_id` DESC
                        LIMIT 50", $where);

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        //$profile = View::factory('profiler/stats');
        //d($response);
        //die();

        $count = 0;

        foreach ( $response as $k => $v )
        {
            $count++;
            $response[$k]['count'] = $count;
        }

        $array['response'] = $response;

        $template = $this->render( 'product/ecommerce', $array );
    }

}