<?php
class Controller_Product_Arrival extends Controller_Website {

    public function action_index()
    {
        $product  = $this->request->param('id');

        if ( ! $product )
        {
            die( s('<h3 align="center" style="margin-top:100px">Sem $product</h3>'));
        }

        $where = null;

        $sql = sprintf("SELECT inv.id, importer, inv.modelo,shipments.*,next.*, shipments.id as shipmentID, sum(next.quant) as quant,
                                  IF( MONTH(shipments.arrival)>0, TO_DAYS(shipments.arrival)+15, TO_DAYS(shipments.date)+45) - TO_DAYS(now()) as Dias,
                                  ( select IF( sum(hi.quant) > 0, sum(hi.quant), 0 ) from hoje h, hist hi where hi.pedido=h.id AND h.nop=76 AND hi.isbn=inv.id AND h.obsnfe=next.shipment limit 1 ) AS PreSale
								FROM mak.inv, 
								  mak.`next` 
								LEFT JOIN mak.shipments on (shipments.id=next.shipment)	
								WHERE 1=1 AND
								 		inv.id=next.isbn AND 
                                          ( month(shipments.status)=0 or date(shipments.status) >= ( CURDATE() - INTERVAL 15 DAY ) ) and 
								 		 month(shipments.date)>0 and 
								 		 month(shipments.arrival)>0 and 
								 		inv.id = (%s) 
								GROUP BY shipments.id
								", $product );
        

        $query = DB::query(Database::SELECT, $sql);
        
        
        $response = $query->execute()->as_array();

        $count = 0;

        $total1 = 0;
        $total2 = 0;

        foreach ( $response as $k => $v )
        {
            $count++;
            $response[$k]['count'] = $count;
            $response[$k]['saldo'] = $v['quant'] - $v['PreSale'];

            if ( $v['status'] == '0000-00-00 00:00:00' or $v['status'] == '0000-00-00')
            {
                //$response[$k]['check'] = true;
            }
            
            $response[$k]['check'] = true;

            $total1+=$v['quant'];
            $total2+=$v['PreSale'];
        }
        
        //s($response);

        $array['response'] = $response;
        $array['total'] = $total1 - $total2;

        $template = $this->render( 'product/arrivals', $array );
    }

}