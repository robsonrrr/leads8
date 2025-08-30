<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Lead_Search extends Controller_Lead_Base {

    public function action_index()
    {
        $this->page      = $this->request->query('page');
        $this->query     = $this->request->query('query');
        $this->leadID    = $this->request->query('lead');
        $this->segmentID = $this->request->query('segment');
        $this->error     = null;

        $lead = self::get_lead();

        // s($lead);
        // die();

        $product = self::get_product();

        $array['Busca']  = $product;
        $array['Pages']  = $this->paginator;
        $array['Total']  = $this->total;
        $array['leadID'] = $this->leadID;
        $array['query']  = $this->query;
        $array['page']   = $this->page;
        $array['Lead']   = $lead;

        //Define o segmento
        $array['segmentoPOID']   = $this->segmentID;
        $array[$this->segmentID] = $this->segmentID;

        if ( $this->segmentID )
        {
            switch ( $this->segmentID )
            {
                case "1":
                    $array['machines'] = true;
                    $array['segment']  = 'machines';
                    break;
                case "2":
                    $array['bearings'] = true;
                    $array['segment']  = 'bearings';
                    break;
                case "3":
                    $array['parts']   = true;
                    $array['segment'] = 'parts';
                    break;
                case "4":
                    $array['faucets'] = true;
                    $array['segment'] = 'faucets';
                    break;
                case "5":
                    $array['auto']    = true;
                    $array['segment'] = 'auto';
                    break;
                case "6":
                    $array['moto']    = true;
                    $array['segment'] = 'moto';
                    break;
            }
        }

        if ($this->request->query('print'))
            die(s($array));

        if ( !empty ($this->error) )
            $array['error'] = $this->error;

        $this->bench($array);

        //if ( $_SESSION['MM_Userid'] == 84 )
            //d($array);

        if ($this->request->query('format') == 'json' )
          return $this->response->body( json_encode($array, true) );

        $template = $this->render( 'search/index', $array );
    }

    private function get_lead()
    {
        $data = array(
            'check' => false
        );

        $json = Request::factory( '/lead/get/'.$this->leadID  )
            ->method('GET')
            ->query($data)
            ->execute()
            ->body();

        $response = json_decode($json,true);

        // s($response);
        // die();

        if( isset($response['data']['Lead']) )
        {
            return $response['data']['Lead'];
        }else{
            return array();
        }
    }

    public function get_product( )
    {
        $lead = $this->get_lead_base( $this->leadID );

        if ( $_SESSION['MM_Userid'] == 84 )
        {
            //s($lead);
            //die();
        }
        $query = strtolower(trim($this->query));

        // s($query);
        // die();

        $where  = null;
        $having = null;
        $limit  = 50;
        $page   = 1;

        if ( isset( $this->page ) and !empty( $this->page ) )
        {
          $page = $this->page;
        }

        $offset =  ( $page - 1 ) * $limit;

        if ( $this->request->query('stock') )
        {
            if ( $this->request->query('stock')  == 'total')
            {
                $having.= sprintf(" AND estoqueTotal > 0");
            }

            if ( $this->request->query('stock')  == 'sem')
            {
                $having.= sprintf(" AND estoqueTotal = 0");
            }

            if ( $this->request->query('stock') == '6')
            {
                $having.= sprintf(" AND estoqueBlumenau > 0");
            }

            if ( $this->request->query('stock') == '1')
            {
                $having.= sprintf(" AND estoqueSaoPaulo > 0");
            }

            if ( $this->request->query('stock') == '8')
            {
                $having.= sprintf(" AND estoqueSaoPauloBarraFunda > 0");
            }

            if ( $this->request->query('stock') == 'previsao')
            {
                $having.= sprintf(" AND Next > 0");
            }
        }

        $order = 'estoqueTotal desc';

        if ( $this->request->query('order') )
        {
            $order = $this->request->query('order');
        }

        if ( $this->segmentID == '2' or $this->segmentID == '6')
        {
            $where.= sprintf(" AND ( produtos.segmento_id = 2 or produtos.segmento_id = 5 ) AND ( inv.modelo LIKE '%%%s%%' OR inv.nome LIKE '%%%s%%' or CONCAT(furo, 'x', diametro, 'x', largura) LIKE '%s%%' or REPLACE(inv.modelo, '-', '') LIKE '%%%s%%' or REPLACE(inv.modelo,' ','') LIKE '%%%s%%' )", $query,$query, $query, $query, $query );
        }

       if ($this->segmentID == '1') {
            // Divide a consulta em palavras
            $terms = explode(' ', $query);
            $whereClauses = [];

            // Verifica se há pelo menos dois termos para buscar por marca e nome do produto
            if (count($terms) >= 2)
            {
                // Primeiro termo para marca, segundo termo para nome do produto
                $marca = $terms[0];
                $produto = $terms[1];

                $whereClauses[] = sprintf(" inv.marca = '%s' ", str_replace('-',' ', $marca));
                $whereClauses[] = sprintf(" inv.nome LIKE '%%%s%%' OR inv.nome LIKE '%%%s%%'  ", $produto, $marca.' '.$produto );
            } else {
                // Caso padrão para um único termo
                $term = $terms[0];

                $searchTerm = str_replace('-', '', $terms[0]); // Remove hífens da string de busca

                $brand = str_replace('-',' ', $query);

                $whereClauses[] = sprintf("(inv.marca = '%s' OR inv.marca = '%s' OR inv.modelo LIKE '%%%s%%' OR inv.nome LIKE '%%%s%%' or CONCAT(furo, 'x', diametro, 'x', largura) LIKE '%%%s%%' or REPLACE(inv.modelo, '-', '') LIKE '%%%s%%' or REPLACE(inv.modelo, ' ', '') LIKE '%%%s%%')", $term, $brand, $term, $term, $term, $searchTerm, $term);
            }

            // Combina as condições com AND
            $combinedWhereClause = implode(' AND ', $whereClauses);

            // Adiciona ao WHERE principal
            $where .= " AND (produtos.segmento_id = 1) AND ($combinedWhereClause)";
        }

        if (  $this->segmentID == '3')
        {
            $where.= sprintf(" AND ( produtos.segmento_id = 3 or produtos.segmento_id = 2  ) AND ( inv.modelo LIKE '%%%s%%' OR inv.nome LIKE '%%%s%%' or CONCAT(furo, 'x', diametro, 'x', largura) LIKE '%s%%' or REPLACE(inv.modelo, '-', '') LIKE '%%%s%%' or REPLACE(inv.modelo,' ','') LIKE '%%%s%%' )", $query,$query, $query, $query, $query );
        }

        if (  $this->segmentID == '4')
        {
            $where.= sprintf(" AND ( produtos.segmento_id = 4 ) AND ( inv.modelo LIKE '%%%s%%' OR inv.nome LIKE '%%%s%%' or CONCAT(furo, 'x', diametro, 'x', largura) LIKE '%s%%' or REPLACE(inv.modelo, '-', '') LIKE '%%%s%%' or REPLACE(inv.modelo,' ','') LIKE '%%%s%%' )", $query,$query, $query, $query, $query );
        }

        if ( $this->segmentID == '5')
        {
            $where.= sprintf(" AND ( produtos.segmento_id = 5 or produtos.segmento_id = 2 ) AND ( inv.modelo LIKE '%%%s%%' OR inv.nome LIKE '%%%s%%' or REPLACE(inv.modelo, '-', '') LIKE '%%%s%%' )", $query, $query, $query );
        }

        if ( $_SESSION['MM_Segment'] == 'geral')
        {
            //$where.= sprintf(" AND ( inv.modelo LIKE '%%%s%%' OR inv.nome LIKE '%%%s%%' or CONCAT(furo, 'x', diametro, 'x', largura) LIKE '%s%%' or REPLACE(inv.modelo, '-', '') LIKE '%%%s%%' or REPLACE(inv.modelo,' ','') LIKE '%%%s%%' ) ", $query, $query, $query, $query, $query );
        }

        $sql= sprintf("SELECT   SQL_CALC_FOUND_ROWS inv.id as produtoPOID, inv.unidade, inv.embalagem as produtoEmbalagem, inv.modelo as produtoModelo,inv.peso as produtoPeso,
                                 CONCAT(furo, 'x', diametro, 'x', largura) AS produtoMedida, inv.abc as produtoABC,
                                inv.marca as produtoMarca, inv.OrigemPOID as produtoOrigem, inv.icms_antecipado as produtoICMSantecipado,
                                inv.nome as produtoNome, inv.isento_st as produtoIsentoST, inv.marca as produtoMarca, inv.st as produtoST,
                                inv.revenda as produtoRevenda, packing.packing,
                                produtos.segmento_id as segmentoPOID, produtos.ncm as segmentoNCM, produtos.segmento as segmentoNome,
                                e13ttd.EstoqueDisponivel + e13.EstoqueDisponivel as estoqueBlumenau,
                                e09.EstoqueDisponivel as estoqueSaoPaulo,
                                e70.EstoqueDisponivel as estoqueSaoPauloLoja,
                                e85.EstoqueDisponivel as estoqueSaoPauloBarraFunda,
                                e09.EstoqueDisponivel + e70.EstoqueDisponivel + e13ttd.EstoqueDisponivel + e13.EstoqueDisponivel + e85.EstoqueDisponivel as estoqueTotal,
                                e09.EstoqueReservado + e70.EstoqueReservado + e13.EstoqueReservado + e85.EstoqueReservado + e13ttd.EstoqueReservado as estoqueReservado,
                                ( select if (ISNULL(MAX(h.data)) ,-1,TO_DAYS(NOW()) - TO_DAYS(MAX(h.data))) from hoje h, hist hi where hi.pedido=h.id AND h.nop=27 AND hi.isbn=inv.id limit 1 ) AS LastDaysSales,
                                ( select sum(hi.quant) from hoje h, hist hi where hi.pedido=h.id AND h.nop=76 AND hi.isbn=inv.id limit 1 ) AS PreSale,
                                ( select sum(quant) from shipments, next where shipments.id=next.shipment AND inv.id=next.isbn AND month(shipments.status)=0 and month(shipments.date)>0 and month(shipments.arrival)>0 limit 1 ) AS Next,
                                ( select if (MONTH(shipments.arrival)>0, TO_DAYS(shipments.arrival)+15, TO_DAYS(shipments.date)+45) - TO_DAYS(now()) from shipments, next where shipments.id=next.shipment AND inv.id=next.isbn AND month(shipments.status)=0 and month(shipments.date)>0  limit 1 ) AS NextDays
                        FROM inv
                          LEFT JOIN produtos on (produtos.id=inv.idcf)
                          LEFT JOIN Catalogo.packing on (packing.id=inv.embalagem)
                          LEFT JOIN mak_0109.Estoque as e09 on ( e09.ProdutoPOID = inv.id )
                          LEFT JOIN mak_0370.Estoque as e70 on ( e70.ProdutoPOID = inv.id )
                          LEFT JOIN mak_0613.Estoque as e13 on ( e13.ProdutoPOID = inv.id )
                          LEFT JOIN mak_0885.Estoque as e85 on ( e85.ProdutoPOID = inv.id )
                          LEFT JOIN mak_0613.Estoque_TTD_1 as e13ttd on ( e13ttd.ProdutoPOID = inv.id )
                        WHERE 1=1
                        %s
                        AND inv.vip <> 9
                        AND inv.idcf > 0
                        AND inv.revenda > 0
                        HAVING 1=1
                        %s
                        ORDER BY %s
                        LIMIT %s
                        OFFSET %s", $where, $having, $order, $limit, $offset );

        $query2 = DB::query(Database::SELECT, $sql);
        $response = $query2->execute()->as_array();

        if ( $_SESSION['MM_Userid'] == 84 or $_SESSION['MM_Userid'] == 999 )
        {
            // s($response);
            // s($sql);
            // die();
        }

        $count=0 + $offset;

        foreach( $response as $k => $v)
        {
            $products_ids[] = $v['produtoPOID'] ; // group product id
        }

        if ( !empty ($products_ids) )
        {
            //// sub routines by group
            $ids="'".implode("','",$products_ids)."'";
            // stock
            $price_group = $this->get_price_group( $ids, $lead['cEmitUnity'], $lead['cCustomer'],$lead['vPaymentTerms'], $this->segmentID);
            //s($price_group);
            //die();
        }

        if ( $_SESSION['MM_Userid'] == 999 )
        {
            // s($price_group);
            // die();
        }

        foreach ( $response as $k => $v )
        {
            $response[$k]['Segmento']['segmentoNCM']  = $v['segmentoNCM'];
            $response[$k]['Segmento']['segmentoNome'] = $v['segmentoNome'];
            $response[$k]['Segmento']['segmentoPOID'] = $v['segmentoPOID'];

            //$response[$k]['Preco'] = $this->get_price( $v['produtoPOID'], $lead['cEmitUnity'], $lead['cCustomer'], $lead['vPaymentTerms'],$this->segmentID );

            $product['Preco']['precoVista']      = $price_group[$v["produtoPOID"]]['precoVista'];
            $product["produtoPOID"]              = $v["produtoPOID"];
            $product["produtoIsentoST"]          = $v["produtoIsentoST"];
            $product['Segmento']['segmentoNCM']  = $v['segmentoNCM'];
            $product['Segmento']['segmentoNome'] = $v['segmentoNome'];
            $product["produtoST"]                = $v['produtoST'];
            $product["produtoOrigem"]            = $v["produtoOrigem"];
            $product["produtoICMSantecipado"]    = $v["produtoICMSantecipado"];

            $response[$k]['produtoPOIDbase64'] = base64_encode($v["produtoPOID"]);

            //s($v, $product);
            //die();

            $count++;

            $response[$k]['marcaSeo'] = $this->clean_string( $v['produtoMarca'] );
            $response[$k]['item'] = $count;

            //s($this->segmentID, $price_group);

            if ( $this->segmentID == '1')
            {
                $response[$k]['Lista'] = $price_group[$v["produtoPOID"]]['Lista'];
            }else{
                $response[$k]['Impostos'] = $this->get_tax( $lead, $product );

                if ( $_SESSION['MM_Userid'] == 84 )
                {
                    // s($v, $product);
                    // s( $response[$k]['Impostos'] );
                    // die();
                }

                if( isset( $response[$k]['Impostos']['valor_produto'] )  )
                {
                    foreach ( $response[$k]['Impostos'] as $kk => $vv)
                    {
                        //s($vv);

                        if (is_numeric($vv))
                        {
                            $response[$k]['Impostos'][$kk] = $this->formatPrice($vv);
                        }
                    }
                }
            }

            //$response[$k]['solr'] = $this->get_solr_query( $v['produtoPOID'], $v['segmentoPOID'] );

            $response[$k]['estoqueEstado'] = $lead['UF'];
            $response[$k]['estoqueLocal']  = $lead['local'];

            if ( $v['NextDays'] < 21 and $v['NextDays'] > 0 )
                $response[$k]['NextDaysActive'] = true;

            if ( $lead['EmitentePOID'] == 1 )
                $response[$k]['estoqueSelecionado'] = $v['estoqueSaoPaulo'];

            if ( $lead['EmitentePOID'] == 3 )
                $response[$k]['estoqueSelecionado'] = $v['estoqueSaoPauloLoja'];

            if ( $lead['EmitentePOID'] == 8 )
                $response[$k]['estoqueSelecionado'] = $v['estoqueSaoPauloBarraFunda'];

            if ( $lead['EmitentePOID'] == 6 )
                $response[$k]['estoqueSelecionado'] = $v['estoqueBlumenau'];
        }

        if ( $_SESSION['MM_Userid'] == 84 )
        {
            // s($response);
        }
        //die();

        //COUNT
        $query3 = DB::query(Database::SELECT, sprintf("SELECT FOUND_ROWS() as total" ) );
        $rows = $query3->execute()->as_array();

        if ( count( $rows ) > 0)
        {
           $this->total = $rows[0]['total'];
        }else{
            $this->total = 0;
        }

        $totalItems   = $this->total;
        $itemsPerPage = $limit;
        $currentPage  = $page;
        $urlPattern   = sprintf('/leads/leads5/%s/%s/', $this->leadID, $this->segmentID );

        $numPages = ($itemsPerPage == 0 ? 0 : (int) ceil($totalItems/$itemsPerPage));

        $pages = array();

        if ( $numPages > 10 )
        {
            $numPages = 10;

            for ($i = 1; $i <= $numPages; $i++) {
                $pages[] = array(
                    'num'       => $i,
                    'url'       => $urlPattern.'?page='.$i.'&query='.$query.'&segment='.$this->segmentID.'&lead='.$this->leadID,
                    'isCurrent' => ($currentPage == $i ? true : false ),
                );
            }

            $pages[] = array(
                'num'       => $totalItems,
                'url'       => $urlPattern.'?page='.$totalItems.'&query='.$query.'&segment='.$this->segmentID.'&lead='.$this->leadID,
                'isCurrent' => ($currentPage == $totalItems ? true : false ),
            );

        }else{

            for ($i = 1; $i <= $numPages; $i++) {
                $pages[] = array(
                    'num'       => $i,
                    'url'       => $urlPattern.'?page='.$i.'&query='.$query.'&segment='.$this->segmentID.'&lead='.$this->leadID,
                    'isCurrent' => ($currentPage == $i ? true : false ),
                );
            }

        }

        if ($numPages <= 1)
        {
           $pages = array();
        }

        //s($pages);

        $this->paginator = $pages;

        return $response;
    }


    public function get_api_product()
    {
        $query = strtolower($this->query );

        //s($_SESSION);
        //die();
        /* 'MM_Username' => string (26) "rogeriobbvn@rolemak.com.br"
        'MM_check' => string (2) "84"
        'MM_Userid' => string (2) "84"
        'MM_Nick' => UTF-8 string (13) "rogério bispo"
        'MM_Depto' => string (6) "VENDAS"
        'MM_Segment' => string (5) "geral"
        'MM_Nivel' => string (1) "5"
        'MM_CSS' => string (7) "mak.css"
        'MM_CompanyId' => string (1) "1"
        'MM_CompanyName' => string (26) "rogeriobbvn@rolemak.com.br"
        'MM_Email' => string (26) "rogeriobbvn@rolemak.com.br" */

        // s($query);
        // die();

        $where = null;

        if ( $_SESSION['MM_Depto'] == 'VENDAS' and $_SESSION['MM_Segment'] == 'auto')
        {
            $where.= sprintf(" AND ( produtos.segmento_id = 5 ) AND ( inv.modelo LIKE '%%%s%%' OR inv.nome LIKE '%%%s%%' )", $query, $query );
        }

        if ( $_SESSION['MM_Depto'] == 'VENDAS' and $_SESSION['MM_Segment'] == 'bearings')
        {
            $where.= sprintf(" AND ( produtos.segmento_id = 2 ) AND ( inv.modelo LIKE '%s%%' OR inv.nome LIKE '%s%%' )", $query, $query );
        }

        if ( $_SESSION['MM_Depto'] == 'VENDAS' and $_SESSION['MM_Segment'] == 'geral')
        {
            $where.= sprintf(" AND ( inv.modelo LIKE '%%%s%%' OR inv.nome LIKE '%%%s%%' )", $query, $query );
        }

        $sql= sprintf("SELECT inv.id as produtoPOID, inv.embalagem as produtoEmbalagem, inv.modelo as produtoModelo,inv.peso as produtoPeso, inv.marca as produtoMarca, inv.OrigemPOID as produtoOrigem, inv.icms_antecipado as produtoICMSantecipado,
                                inv.nome as produtoNome, inv.isento_st as produtoIsentoST, inv.marca as produtoMarca, inv.st as produtoST, inv.revenda as produtoRevenda, packing.packing,
                                produtos.segmento_id as segmentoPOID, produtos.ncm as segmentoNCM, produtos.segmento as segmentoNome
                        FROM inv
                        left join produtos on (produtos.id=inv.idcf)
                        LEFT JOIN Catalogo.packing on (packing.id=inv.embalagem)
                        WHERE 1=1
                        %s
                        AND produtos.segmento_id <> 4
                        AND inv.vip <> 9
                        ORDER BY inv.nome desc
                        LIMIT 10 ", $where );

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        foreach ( $response as $k => $v )
        {
            $response[$k]['Segmento']['segmentoNCM'] = $v['segmentoNCM'];
            $response[$k]['Segmento']['segmentoNome'] = $v['segmentoNome'];
            $response[$k]['Segmento']['segmentoPOID'] = $v['segmentoPOID'];
        }

        // s($response);
        // die();

        return $response;
    }

    public function action_bootcomplete()
    {
        $this->auto_render = FALSE;

        $query = strtoupper($this->request->query('query'));

        $sql= sprintf("SELECT inv.id, inv.modelo, inv.nome, inv.marca, packing.packing, produtos.segmento_id, produtos.segmento
                        FROM inv
                        left join produtos on (produtos.id=inv.idcf)
                        LEFT JOIN Catalogo.packing on (packing.id=inv.embalagem)
                        WHERE 1=1
                        AND inv.vip <> 9
                        AND ( inv.modelo LIKE '%s%%' OR inv.nome LIKE '%s%%' )
                        LIMIT 10 ", $query, $query );

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        //s($response);
        //die();

        $newarr['query'] = $query;

        if ( count($response) > 0)
        {
            foreach ( $response as $k => $v)
            {
                $newarr['suggestions'][$k]['id']     = $v['id'];
                $newarr['suggestions'][$k]['modelo'] = $v['modelo'];
                $newarr['suggestions'][$k]['nome']   = $v['nome'];
                $newarr['suggestions'][$k]['value']  = $v['modelo'].' - '.$v['marca'].' - '.$v['packing'].' - '.$v['segmento'];
            }
        }

        header("Content-Type: application/json; charset=utf-8");

        echo json_encode($newarr);
    }

}