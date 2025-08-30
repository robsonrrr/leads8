<?php
class Controller_Product_Sales extends Controller_Website {

    public function action_index()
    {
        $product  = $this->request->param('id');
        $customer = $this->request->param('id2');

        if ( ! $product )
        {
            die( s('<h3 align="center" style="margin-top:100px">Sem $product</h3>'));
        }

        $where = null;

        $nop  = $this->request->query('nop');

        if ( $nop )
        {
            $where.=sprintf(" AND hoje.nop = %s", $nop);
        }

        if ( $customer )
        {
            $where.=sprintf(" AND hoje.idcli = %s", $customer);
        }

        if ( $product )
        {
            $where.=sprintf(" AND hist.isbn = %s", $product);
        }

        if ( ! $customer and $_SESSION['MM_Depto'] == 'VENDAS' and $_SESSION['MM_Nivel'] < 3 )
        {
            $where.=sprintf(" AND hoje.vendedor = %s", $_SESSION['MM_Userid']);
        }

        $sql= sprintf(" SELECT 
                            hoje.data, hoje.datae, hoje.nf, hoje.id as pedido,
                            hist.*,
                            inv.*,
                            clientes.nome as cliente,
                            clientes.rfm,
                            clientes.estado,
                            ns.segmento as clientes_novo_segmento,
                            grupos.*,
                            Emitentes.Fantasia,
                            produtos.segmento, produtos.segmento_id
                        FROM hoje 
                        LEFT JOIN Emitentes on (Emitentes.EmitentePOID=hoje.EmissorPOID)  
                        LEFT JOIN clientes on ( clientes.id=hoje.idcli )
                        LEFT JOIN hist on ( hist.pedido=hoje.id )
                        LEFT JOIN inv on  ( inv.id=hist.isbn)
                        LEFT JOIN produtos on  (produtos.id=inv.idcf)
                        LEFT JOIN grupos on  (grupos.id=inv.idgrupo)
                        LEFT JOIN novo_segmento as ns on (ns.id=clientes.novo_segmento)
                        WHERE 1=1
                        %s
                        ORDER BY hoje.data DESC
                        LIMIT 50", $where); 

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        //$profile = View::factory('profiler/stats');
        //s($response);
        //die();

        $count = 0;

        foreach ( $response as $k => $v )
        {
            $count++;
            $response[$k]['count'] = $count;
        }

        $array['response'] = $response;

        $template = $this->render( 'product/sales', $array );
    }

}