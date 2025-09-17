<?php
class Controller_Lead_Statistics_fixed extends Controller_Website {

    public function before()
    {
        parent::before();
        
        // Verificar permissões de acesso
        if (!isset($_SESSION['MM_Userid']) || $_SESSION['MM_Nivel'] < 2) {
            die('<h3 align="center" style="margin-top:100px">Acesso não autorizado</h3>');
        }
    }

    public function action_index()
    {
        $dataInicio = $this->request->query('data_inicio') ?: date('Y-m-01');
        $dataFim = $this->request->query('data_fim') ?: date('Y-m-d');
        $segmento = $this->request->query('segmento');
        $status = $this->request->query('status');
        $limit = $this->request->query('limit') ?: 50;

        // Construir filtros WHERE
        $where = " WHERE data_emissao_lead BETWEEN '$dataInicio' AND '$dataFim'";
        
        if ($segmento) {
            $where .= " AND lead_tipo = '$segmento'";
        }
        
        if ($status) {
            $where .= " AND lead_autorizado = '$status'";
        }

        // Controle de acesso por vendedor
        if ($_SESSION['MM_Depto'] == 'VENDAS' && $_SESSION['MM_Nivel'] < 3) {
            $where .= " AND vendedor_poid = " . $_SESSION['MM_Userid'];
        }

        // Estatísticas gerais
        $stats = $this->getGeneralStats($where);
        
        // Formatar valores monetários
        if (isset($stats['total_valor'])) {
            $stats['total_valor_formatado'] = number_format($stats['total_valor'], 2, '.', ',');
        }
        if (isset($stats['valor_medio'])) {
            $stats['valor_medio_formatado'] = number_format($stats['valor_medio'], 2, '.', ',');
        }
        
        // Calcular percentual de leads autorizados
        if (isset($stats['total_leads']) && $stats['total_leads'] > 0) {
            $stats['percentual_autorizados'] = round(($stats['leads_autorizados'] / $stats['total_leads']) * 100, 1);
        } else {
            $stats['percentual_autorizados'] = 0;
        }
        
        // Top produtos
        $topProdutos = $this->getTopProdutos($where, $limit);
        
        // Leads por período
        $leadsPorPeriodo = $this->getLeadsPorPeriodo($dataInicio, $dataFim);
        
        // Valores por segmento
        $valoresPorSegmento = $this->getValoresPorSegmento($where);

        $response = [
            'stats' => $stats,
            'topProdutos' => $topProdutos,
            'leadsPorPeriodo' => $leadsPorPeriodo,
            'valoresPorSegmento' => $valoresPorSegmento,
            'filtros' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'segmento' => $segmento,
                'status' => $status,
                'limit' => $limit,
                // Variáveis booleanas para o template Mustache
                'segmento_1' => ($segmento == '1'),
                'segmento_2' => ($segmento == '2'),
                'segmento_3' => ($segmento == '3'),
                'segmento_4' => ($segmento == '4'),
                'segmento_5' => ($segmento == '5'),
                'status_1' => ($status == '1'),
                'status_0' => ($status == '0')
            ]
        ];

        return $this->render('lead/statistics', $response);
    }

    /**
     * Obter estatísticas gerais
     */
    private function getGeneralStats($where)
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT lead_id) as total_leads,
                COUNT(DISTINCT produto_poid) as total_produtos,
                COUNT(DISTINCT cliente_poid) as total_clientes,
                SUM(produto_quantidade) as total_quantidade,
                SUM(valor_total_item) as total_valor,
                AVG(valor_total_item) as valor_medio,
                COUNT(CASE WHEN lead_autorizado = '1' THEN 1 END) as leads_autorizados,
                COUNT(CASE WHEN lead_autorizado = '0' THEN 1 END) as leads_pendentes
            FROM vw_leads_produtos
            $where
        ";

        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute()->as_array();
        
        return $result[0] ?? [];
    }

    /**
     * Obter top produtos por valor
     */
    private function getTopProdutos($where, $limit)
    {
        $sql = "
            SELECT 
                produto_poid,
                produto_similar,
                SUM(produto_quantidade) as total_quantidade,
                SUM(valor_total_item) as total_valor,
                COUNT(DISTINCT lead_id) as total_leads,
                AVG(produto_valor) as valor_medio
            FROM vw_leads_produtos
            $where
            GROUP BY produto_poid, produto_similar
            ORDER BY total_valor DESC
            LIMIT $limit
        ";

        $query = DB::query(Database::SELECT, $sql);
        return $query->execute()->as_array();
    }

    /**
     * Obter leads por período (últimos 30 dias)
     */
    private function getLeadsPorPeriodo($dataInicio, $dataFim)
    {
        $sql = "
            SELECT 
                DATE(data_emissao_lead) as data,
                COUNT(DISTINCT lead_id) as total_leads,
                SUM(valor_total_item) as total_valor
            FROM vw_leads_produtos
            WHERE data_emissao_lead BETWEEN '$dataInicio' AND '$dataFim'
            GROUP BY DATE(data_emissao_lead)
            ORDER BY data DESC
        ";

        $query = DB::query(Database::SELECT, $sql);
        return $query->execute()->as_array();
    }

    /**
     * Obter valores por segmento
     */
    private function getValoresPorSegmento($where)
    {
        $sql = "
            SELECT 
                lead_tipo,
                COUNT(DISTINCT lead_id) as total_leads,
                SUM(valor_total_item) as total_valor,
                AVG(valor_total_item) as valor_medio
            FROM vw_leads_produtos
            $where
            GROUP BY lead_tipo
            ORDER BY total_valor DESC
        ";

        $query = DB::query(Database::SELECT, $sql);
        return $query->execute()->as_array();
    }
}


