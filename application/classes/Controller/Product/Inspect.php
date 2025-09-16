<?php
class Controller_Product_Inspect extends Controller_Website {

    public function action_index()
    {
        $product  = $this->request->param('id');
        $poid     = $this->request->param('id2');

        //s($product,$poid);
        //die();

        if ( ! $product )
        {
            die( s('<h3 align="center" style="margin-top:100px">Sem $product</h3>'));
        }

        $where = null;

        $sql = sprintf( "SELECT si.*, inv.nome, inv.modelo, inv.loc, inv.caixa, inv.caixaqtd, inv.peso, inv.medidas, icart.qProduct
                                FROM webteam.`shipments_inspection` as si
                                LEFT JOIN inv on (inv.id=si.si_product_id)
                                LEFT JOIN icart on (icart.cProduct = inv.id)
                                WHERE `si_product_id` = %s AND  icart.cCart = %s
                                ORDER BY si_id
                                DESC LIMIT 3 ", $product, $poid);

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        if ( count($response) > 0 )
        {
            $count = 1;

            foreach( $response as $k => $v )
            {
                $response[$k]['count']           = $count;
                $response[$k]['si_date_end_f']   = date_format( date_create( $v['si_date_end'] ) , 'd/m/y');;
                $response[$k]['volume_estimado'] = ceil($v['qProduct'] / $v['si_quantity_per_carton']);
                $count++;
            }

        }

        //s($response);

        $array['response'] = $response;

        $template = $this->render( 'product/inspect', $array );
    }

}