<?php
class Controller_Lead_Statistics extends Controller_Website {

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
        // Verificar se é solicitação para Top Produtos ou Top Vendedores via parâmetro URL ou id
        $view = $this->request->query('view');
        $id = $this->request->param('id');
        
        if ($view === 'top_produtos' || $id === 'top_produtos') {
            return $this->action_top_produtos();
        }
        
        if ($view === 'top_vendedores' || $id === 'top_vendedores') {
            return $this->action_top_vendedores();
        }
        
        if ($view === 'top_leads' || $id === 'top_leads') {
            return $this->action_top_leads();
        }
        
        // Data padrão: início do mês e hoje + 1 dia
        $dataInicio = $this->request->query('data_inicio') ?: date('Y-m-01');
        $dataFim = $this->request->query('data_fim') ?: date('Y-m-d', strtotime('+1 day'));
        $segmento = $this->request->query('segmento');
        $status = $this->request->query('status');
        $limit = $this->request->query('limit') ?: 50;
        
        // Parâmetros de paginação  
        $leadOffset = $this->request->query('lead_offset') ?: 0;

        // Debug: log dos parâmetros recebidos
        error_log("DEBUG - Parâmetros recebidos:");
        error_log("data_inicio: " . $dataInicio);
        error_log("data_fim: " . $dataFim);
        error_log("segmento: " . ($segmento ?: 'vazio'));
        error_log("status: " . ($status ?: 'vazio'));
        error_log("limit: " . $limit);

        // Construir filtros WHERE
        $where = " WHERE data_emissao_lead BETWEEN '$dataInicio' AND '$dataFim'";
        
        if ($segmento) {
            $where .= " AND lead_tipo = '$segmento'";
        }
        
        if ($status) {
            $where .= " AND lead_autorizado = '$status'";
        }

        // Controle de acesso por vendedor
        error_log("DEBUG - Sessão do usuário:");
        error_log("MM_Depto: " . ($_SESSION['MM_Depto'] ?? 'não definido'));
        error_log("MM_Nivel: " . ($_SESSION['MM_Nivel'] ?? 'não definido'));
        error_log("MM_Userid: " . ($_SESSION['MM_Userid'] ?? 'não definido'));
        
        // TEMPORARIAMENTE COMENTADO PARA VER TODOS OS DADOS
        /*
        if ($_SESSION['MM_Depto'] == 'VENDAS' && $_SESSION['MM_Nivel'] < 3) {
            $where .= " AND vendedor_poid = " . $_SESSION['MM_Userid'];
            error_log("DEBUG - Filtro de vendedor aplicado: vendedor_poid = " . $_SESSION['MM_Userid']);
        }
        */
        error_log("DEBUG - Filtro de vendedor DESABILITADO para mostrar todos os dados");

        // Debug: teste direto na view
        $testSql = "SELECT COUNT(*) as total FROM vw_leads_produtos WHERE data_emissao_lead = '2025-08-18'";
        $testQuery = DB::query(Database::SELECT, $testSql);
        $testResult = $testQuery->execute()->as_array();
        error_log("DEBUG - Teste direto na view para 2025-08-18: " . $testResult[0]['total'] . " registros");
        
        // DEBUG COMPLETO: Vamos descobrir exatamente onde está o problema
        error_log("DEBUG - WHERE final construído: " . $where);
        
        // Teste 1: Contar registros com WHERE construído
        $debugCountSql = "SELECT COUNT(*) as total FROM vw_leads_produtos $where";
        $debugCountQuery = DB::query(Database::SELECT, $debugCountSql);
        $debugCountResult = $debugCountQuery->execute()->as_array();
        error_log("DEBUG - Registros encontrados com WHERE construído: " . $debugCountResult[0]['total']);
        
        // Teste 2: Mostrar exatamente qual consulta está sendo executada
        $debugSampleSql = "SELECT lead_id, data_emissao_lead, valor_total_item FROM vw_leads_produtos $where LIMIT 3";
        $debugSampleQuery = DB::query(Database::SELECT, $debugSampleSql);
        $debugSampleResult = $debugSampleQuery->execute()->as_array();
        error_log("DEBUG - Primeiros 3 registros: " . json_encode($debugSampleResult));
        
        // Teste 3: Verificar se há problema com formato de data
        $debugDateSql = "SELECT COUNT(*) as total FROM vw_leads_produtos WHERE data_emissao_lead LIKE '2025-08-18%'";
        $debugDateQuery = DB::query(Database::SELECT, $debugDateSql);
        $debugDateResult = $debugDateQuery->execute()->as_array();
        error_log("DEBUG - Registros com LIKE '2025-08%': " . $debugDateResult[0]['total']);

        // Estatísticas gerais
        $stats = $this->getGeneralStats($where);
        
        // Formatar valores monetários
        if (isset($stats['total_valor'])) {
            $stats['total_valor_formatado'] = number_format($stats['total_valor'], 2, ',', '.');
        }
        if (isset($stats['valor_medio'])) {
            $stats['valor_medio_formatado'] = number_format($stats['valor_medio'], 2, ',', '.');
        }
        
        // Calcular percentual de leads autorizados
        if (isset($stats['total_leads']) && $stats['total_leads'] > 0) {
            $stats['percentual_autorizados'] = round(($stats['leads_autorizados'] / $stats['total_leads']) * 100, 1);
        } else {
            $stats['percentual_autorizados'] = 0;
        }
        
        // Formatar valores monetários
        if (isset($stats['total_valor'])) {
            $stats['total_valor_formatado'] = number_format($stats['total_valor'], 2, ',', '.');
        }
        if (isset($stats['valor_medio'])) {
            $stats['valor_medio_formatado'] = number_format($stats['valor_medio'], 2, ',', '.');
        }
        
        // Calcular percentual de leads autorizados
        if (isset($stats['total_leads']) && $stats['total_leads'] > 0) {
            $stats['percentual_autorizados'] = round(($stats['leads_autorizados'] / $stats['total_leads']) * 100, 1);
        } else {
            $stats['percentual_autorizados'] = 0;
        }
        
        // Totais por lead (nova funcionalidade)
        $totaisPorLead = $this->getTotaisPorLead($where, $limit, $leadOffset);
        
        // Debug: verificar dados dos totais por lead
        error_log("DEBUG - Totais por Lead:");
        error_log("Total de registros encontrados: " . count($totaisPorLead['data']));
        if (!empty($totaisPorLead['data'])) {
            error_log("Primeiro registro: " . json_encode($totaisPorLead['data'][0]));
        }
        
        // Leads por período
        $leadsPorPeriodo = $this->getLeadsPorPeriodo($dataInicio, $dataFim);
        
        // Valores por segmento
        $valoresPorSegmento = $this->getValoresPorSegmento($where);

        $response = [
            'page_title' => 'Estatísticas de Leads e Produtos',
            'stats' => $stats,
            'totaisPorLead' => $totaisPorLead['data'],
            'totaisPorLeadPagination' => $totaisPorLead['pagination'],
            'leadsPorPeriodo' => $leadsPorPeriodo,
            'valoresPorSegmento' => $valoresPorSegmento,
            'filtros' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'segmento' => $segmento,
                'status' => $status,
                'limit' => $limit,
                'lead_offset' => $leadOffset,
                // Filtros específicos para a tabela de leads
                'lead_filter_cliente_nome' => $this->request->query('lead_filter_cliente_nome'),
                'lead_filter_usuario_apelido' => $this->request->query('lead_filter_usuario_apelido'),
                'lead_filter_pedido_poid' => $this->request->query('lead_filter_pedido_poid'),
                'lead_filter_lead_id' => $this->request->query('lead_filter_lead_id'),
                'lead_filter_observacao_financeiro' => $this->request->query('lead_filter_observacao_financeiro'),
                'lead_filter_observacao_interna' => $this->request->query('lead_filter_observacao_interna'),
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
        // Debug: log da consulta
        error_log("Debug getGeneralStats - WHERE: " . $where);
        
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
        
        // Debug: log do resultado
        error_log("Debug getGeneralStats - Result: " . json_encode($result[0] ?? []));
        
        return $result[0] ?? [];
    }

    /**
     * Obter top produtos por valor
     */
    private function getTopProdutos($where, $limit, $orderBy = 'valor_total_item', $orderDir = 'DESC', $filtros = [], $offset = 0)
    {
        // Debug: verificar se há dados na view
        $debugSql = "SELECT COUNT(*) as total FROM vw_leads_produtos $where";
        $debugQuery = DB::query(Database::SELECT, $debugSql);
        $debugResult = $debugQuery->execute()->as_array();
        error_log("Debug - Total records found: " . $debugResult[0]['total'] . " - WHERE: " . $where);
        
        // Mapeamento de colunas para ordenação
        $allowedColumns = [
            'data_emissao' => 'DATE(data_emissao_lead)',
            'hora_emissao' => 'TIME(data_emissao_lead)',
            'pedido_poid' => 'pedido_poid',
            'tipo_lead' => 'tipo_lead',
            'produto_poid' => 'produto_poid',
            'produto_segmento_nome' => 'produto_segmento_nome',
            'cliente_nome' => 'cliente_nome',
            'cliente_cidade' => 'cliente_cidade',
            'cliente_estado' => 'cliente_estado',
            'usuario_apelido' => 'usuario_apelido',
            'produto_modelo' => 'produto_modelo',
            'total_quantidade' => 'produto_quantidade',
            'valor_medio' => 'produto_valor',
            'valor_total_item' => 'valor_total_item'
        ];
        
        // Validar coluna de ordenação
        $orderColumn = isset($allowedColumns[$orderBy]) ? $allowedColumns[$orderBy] : 'valor_total_item';
        $orderDirection = (strtoupper($orderDir) === 'ASC') ? 'ASC' : 'DESC';
        
        // Aplicar filtros de colunas
        $additionalWhere = [];
        if (!empty($filtros['cliente_nome'])) {
            $additionalWhere[] = "cliente_nome LIKE '%" . addslashes($filtros['cliente_nome']) . "%'";
        }
        if (!empty($filtros['cliente_cidade'])) {
            $additionalWhere[] = "cliente_cidade LIKE '%" . addslashes($filtros['cliente_cidade']) . "%'";
        }
        if (!empty($filtros['cliente_estado'])) {
            $additionalWhere[] = "cliente_estado LIKE '%" . addslashes($filtros['cliente_estado']) . "%'";
        }
        if (!empty($filtros['cliente_cidade'])) {
            $additionalWhere[] = "cliente_cidade LIKE '%" . addslashes($filtros['cliente_cidade']) . "%'";
        }
        if (!empty($filtros['cliente_estado'])) {
            $additionalWhere[] = "cliente_estado LIKE '%" . addslashes($filtros['cliente_estado']) . "%'";
        }
        if (!empty($filtros['usuario_apelido'])) {
            $additionalWhere[] = "usuario_apelido LIKE '%" . addslashes($filtros['usuario_apelido']) . "%'";
        }
        if (!empty($filtros['produto_modelo'])) {
            $additionalWhere[] = "produto_modelo LIKE '%" . addslashes($filtros['produto_modelo']) . "%'";
        }
        if (!empty($filtros['produto_segmento_nome'])) {
            $additionalWhere[] = "produto_segmento_nome LIKE '%" . addslashes($filtros['produto_segmento_nome']) . "%'";
        }
        if (!empty($filtros['produto_poid'])) {
            $additionalWhere[] = "produto_poid = '" . addslashes($filtros['produto_poid']) . "'";
        }
        if (!empty($filtros['pedido_poid'])) {
            $additionalWhere[] = "pedido_poid = '" . addslashes($filtros['pedido_poid']) . "'";
        }
        
        // Combinar filtros com WHERE existente
        if (!empty($additionalWhere)) {
            if (trim($where) === '' || $where === 'WHERE 1=1') {
                $where = 'WHERE ' . implode(' AND ', $additionalWhere);
            } else {
                $where .= ' AND ' . implode(' AND ', $additionalWhere);
            }
        }
        
        $sql = "
            SELECT 
                produto_poid,
                produto_segmento_nome,
                produto_modelo,
                cliente_nome,
                cliente_cidade,
                cliente_estado,
                DATE(data_emissao_lead) as data_emissao,
                TIME(data_emissao_lead) as hora_emissao,
                pedido_poid,
                CASE 
                    WHEN pedido_poid IS NOT NULL AND pedido_poid != '' THEN 'Pedido'
                    ELSE 'Consulta'
                END as tipo_lead,
                usuario_apelido,
                usuario_poid,
                produto_quantidade as total_quantidade,
                valor_total_item as total_valor,
                produto_valor as valor_medio
            FROM vw_leads_produtos
            $where
            ORDER BY $orderColumn $orderDirection,
                     DATE(data_emissao_lead) DESC,
                     TIME(data_emissao_lead) DESC
            LIMIT $limit OFFSET $offset
        ";

        // Contar total de registros para paginação
        $countSql = "
            SELECT COUNT(*) as total
            FROM vw_leads_produtos
            $where
        ";

        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute()->as_array();
        
        // Executar consulta de contagem
        $countQuery = DB::query(Database::SELECT, $countSql);
        $countResult = $countQuery->execute()->as_array();
        $totalRecords = $countResult[0]['total'];
        
        // Formatar valores monetários e quantidade
        foreach ($result as &$row) {
            if (isset($row['valor_medio'])) {
                $row['valor_medio_formatado'] = number_format($row['valor_medio'], 2, ',', '.');
            }
            if (isset($row['total_valor'])) {
                $row['total_valor_formatado'] = number_format($row['total_valor'], 2, ',', '.');
            }
            if (isset($row['total_quantidade'])) {
                $row['total_quantidade_formatado'] = number_format($row['total_quantidade'], 0, ',', '.');
            }
        }
        
        return [
            'data' => $result,
            'pagination' => [
                'total' => $totalRecords,
                'limit' => $limit,
                'offset' => $offset,
                'current_page' => $totalRecords > 0 ? floor($offset / $limit) + 1 : 1,
                'total_pages' => max(1, ceil($totalRecords / $limit)),
                'has_previous' => $offset > 0,
                'has_next' => ($offset + $limit) < $totalRecords
            ]
        ];
    }

    /**
     * Obter estatísticas gerais separando consultas e pedidos para vendedores
     */
    private function getGeneralStatsVendedores($where)
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT usuario_poid) as total_vendedores,
                COUNT(DISTINCT lead_id) as total_leads,
                COUNT(DISTINCT CASE WHEN pedido_poid IS NULL OR pedido_poid = '' THEN lead_id END) as total_consultas,
                COUNT(DISTINCT CASE WHEN pedido_poid IS NOT NULL AND pedido_poid != '' THEN lead_id END) as total_pedidos,
                COUNT(DISTINCT cliente_poid) as total_clientes,
                COUNT(CASE WHEN lead_autorizado = '1' THEN 1 END) as leads_autorizados,
                SUM(valor_total_item) as total_valor,
                SUM(CASE WHEN pedido_poid IS NULL OR pedido_poid = '' THEN valor_total_item ELSE 0 END) as valor_consultas,
                SUM(CASE WHEN pedido_poid IS NOT NULL AND pedido_poid != '' THEN valor_total_item ELSE 0 END) as valor_pedidos,
                AVG(valor_total_item) as valor_medio,
                ROUND((COUNT(DISTINCT CASE WHEN pedido_poid IS NOT NULL AND pedido_poid != '' THEN lead_id END) * 100.0 / COUNT(DISTINCT lead_id)), 1) as taxa_conversao_media
            FROM vw_leads_produtos
            $where
        ";

        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute()->as_array();
        
        return $result[0];
    }

    /**
     * Obter estatísticas gerais separando consultas e pedidos para leads
     */
    private function getGeneralStatsLeads($where)
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT lead_id) as total_leads,
                COUNT(DISTINCT CASE WHEN pedido_poid IS NULL OR pedido_poid = '' THEN lead_id END) as total_consultas,
                COUNT(DISTINCT CASE WHEN pedido_poid IS NOT NULL AND pedido_poid != '' THEN lead_id END) as total_pedidos,
                COUNT(DISTINCT cliente_poid) as total_clientes,
                COUNT(DISTINCT usuario_poid) as total_vendedores,
                COUNT(CASE WHEN lead_autorizado = '1' THEN 1 END) as leads_autorizados,
                SUM(valor_total_item) as total_valor,
                SUM(CASE WHEN pedido_poid IS NULL OR pedido_poid = '' THEN valor_total_item ELSE 0 END) as valor_consultas,
                SUM(CASE WHEN pedido_poid IS NOT NULL AND pedido_poid != '' THEN valor_total_item ELSE 0 END) as valor_pedidos,
                AVG(valor_total_item) as valor_medio,
                ROUND((COUNT(DISTINCT CASE WHEN pedido_poid IS NOT NULL AND pedido_poid != '' THEN lead_id END) * 100.0 / COUNT(DISTINCT lead_id)), 1) as taxa_conversao_media
            FROM vw_leads_produtos
            $where
        ";

        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute()->as_array();
        
        return $result[0];
    }

    /**
     * Obter top leads por período
     */
    private function getTopLeads($where, $limit, $orderBy = 'data_emissao_lead', $orderDir = 'DESC', $filtros = [], $offset = 0)
    {
        // Mapeamento de colunas para ordenação
        $allowedColumns = [
            'data_emissao_lead' => 'data_emissao_lead',
            'lead_id' => 'lead_id',
            'cliente_nome' => 'cliente_nome',
            'cliente_cidade' => 'cliente_cidade',
            'cliente_estado' => 'cliente_estado',
            'usuario_apelido' => 'usuario_apelido',
            'lead_autorizado' => 'lead_autorizado',
            'pedido_poid' => 'pedido_poid',
            'valor_total_lead' => 'valor_total_lead',
            'total_produtos' => 'total_produtos',
            'tipo_lead' => 'tipo_lead',
            'lead_tipo' => 'lead_tipo',
            'lead_tipo_nome' => 'lead_tipo_nome',
            'lead_tipo' => 'lead_tipo'
        ];
        
        // Validar coluna de ordenação
        $orderColumn = isset($allowedColumns[$orderBy]) ? $allowedColumns[$orderBy] : 'data_emissao_lead';
        $orderDirection = (strtoupper($orderDir) === 'ASC') ? 'ASC' : 'DESC';
        
        // Aplicar filtros de colunas
        $additionalWhere = [];
        if (!empty($filtros['cliente_nome'])) {
            $additionalWhere[] = "cliente_nome LIKE '%" . addslashes($filtros['cliente_nome']) . "%'";
        }
        if (!empty($filtros['cliente_cidade'])) {
            $additionalWhere[] = "cliente_cidade LIKE '%" . addslashes($filtros['cliente_cidade']) . "%'";
        }
        if (!empty($filtros['cliente_estado'])) {
            $additionalWhere[] = "cliente_estado LIKE '%" . addslashes($filtros['cliente_estado']) . "%'";
        }
        if (!empty($filtros['usuario_apelido'])) {
            $additionalWhere[] = "usuario_apelido LIKE '%" . addslashes($filtros['usuario_apelido']) . "%'";
        }
        if (!empty($filtros['tipo_lead'])) {
            if ($filtros['tipo_lead'] === 'Consulta') {
                $additionalWhere[] = "(pedido_poid IS NULL OR pedido_poid = '')";
            } elseif ($filtros['tipo_lead'] === 'Pedido') {
                $additionalWhere[] = "(pedido_poid IS NOT NULL AND pedido_poid != '')";
            }
        }
        if (!empty($filtros['lead_tipo'])) {
            $additionalWhere[] = "lead_tipo = '" . addslashes($filtros['lead_tipo']) . "'";
        }
        
        // Combinar filtros com WHERE existente
        if (!empty($additionalWhere)) {
            if (trim($where) === '' || $where === 'WHERE 1=1') {
                $where = 'WHERE ' . implode(' AND ', $additionalWhere);
            } else {
                $where .= ' AND ' . implode(' AND ', $additionalWhere);
            }
        }
        
        $sql = "
            SELECT 
                lead_id,
                DATE(data_emissao_lead) as data_emissao,
                TIME(data_emissao_lead) as hora_emissao,
                data_emissao_lead,
                cliente_nome,
                cliente_cidade,
                cliente_estado,
                usuario_apelido,
                usuario_poid,
                lead_autorizado,
                pedido_poid,
                lead_tipo,
                CASE lead_tipo
                    WHEN '1' THEN '🏭 Máquinas'
                    WHEN '2' THEN '⚙️ Rolamentos'
                    WHEN '3' THEN '🔧 Peças'
                    WHEN '4' THEN '🏗️ Metais'
                    WHEN '5' THEN '🚗 Autopeças'
                    ELSE 'Outro'
                END as lead_tipo_nome,
                CASE 
                    WHEN pedido_poid IS NOT NULL AND pedido_poid != '' THEN 'Pedido'
                    ELSE 'Consulta'
                END as tipo_lead,
                SUM(valor_total_item) as valor_total_lead,
                COUNT(DISTINCT produto_poid) as total_produtos,
                SUM(produto_quantidade) as quantidade_total
            FROM vw_leads_produtos
            $where
            GROUP BY lead_id, data_emissao_lead, cliente_nome, cliente_cidade, cliente_estado, usuario_apelido, usuario_poid, lead_autorizado, pedido_poid, lead_tipo, lead_tipo_nome
            ORDER BY $orderColumn $orderDirection
            LIMIT $limit OFFSET $offset
        ";

        // Contar total de registros para paginação
        $countSql = "
            SELECT COUNT(DISTINCT lead_id) as total
            FROM vw_leads_produtos
            $where
        ";

        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute()->as_array();
        
        // Executar consulta de contagem
        $countQuery = DB::query(Database::SELECT, $countSql);
        $countResult = $countQuery->execute()->as_array();
        $totalRecords = $countResult[0]['total'];
        
        // Formatar valores monetários
        foreach ($result as &$row) {
            if (isset($row['valor_total_lead'])) {
                $row['valor_total_lead_formatado'] = number_format($row['valor_total_lead'], 2, ',', '.');
            }
            if (isset($row['quantidade_total'])) {
                $row['quantidade_total_formatado'] = number_format($row['quantidade_total'], 0, ',', '.');
            }
        }
        
        return [
            'data' => $result,
            'pagination' => [
                'total' => $totalRecords,
                'limit' => $limit,
                'offset' => $offset,
                'current_page' => $totalRecords > 0 ? floor($offset / $limit) + 1 : 1,
                'total_pages' => max(1, ceil($totalRecords / $limit)),
                'has_previous' => $offset > 0,
                'has_next' => ($offset + $limit) < $totalRecords
            ]
        ];
    }

    /**
     * Obter top vendedores por performance
     */
    private function getTopVendedores($where, $limit, $orderBy = 'valor_total_vendedor', $orderDir = 'DESC', $filtros = [], $offset = 0)
    {
        // Mapeamento de colunas para ordenação
        $allowedColumns = [
            'usuario_apelido' => 'usuario_apelido',
            'total_leads' => 'total_leads',
            'total_consultas' => 'total_consultas',
            'total_pedidos' => 'total_pedidos',
            'total_produtos' => 'total_produtos_vendedor',
            'total_clientes' => 'total_clientes_vendedor',
            'valor_total_vendedor' => 'valor_total_vendedor',
            'valor_total_consultas' => 'valor_total_consultas',
            'valor_total_pedidos' => 'valor_total_pedidos',
            'valor_medio_vendedor' => 'valor_medio_vendedor',
            'leads_autorizados' => 'leads_autorizados',
            'taxa_aprovacao' => 'taxa_aprovacao',
            'taxa_conversao' => 'taxa_conversao'
        ];
        
        // Validar coluna de ordenação
        $orderColumn = isset($allowedColumns[$orderBy]) ? $allowedColumns[$orderBy] : 'valor_total_vendedor';
        $orderDirection = (strtoupper($orderDir) === 'ASC') ? 'ASC' : 'DESC';
        
        // Aplicar filtros de colunas
        $additionalWhere = [];
        if (!empty($filtros['usuario_apelido'])) {
            $additionalWhere[] = "usuario_apelido LIKE '%" . addslashes($filtros['usuario_apelido']) . "%'";
        }
        if (!empty($filtros['cliente_nome'])) {
            $additionalWhere[] = "clientes_atendidos LIKE '%" . addslashes($filtros['cliente_nome']) . "%'";
        }
        
        // Combinar filtros com WHERE existente
        if (!empty($additionalWhere)) {
            if (trim($where) === '' || $where === 'WHERE 1=1') {
                $where = 'WHERE ' . implode(' AND ', $additionalWhere);
            } else {
                $where .= ' AND ' . implode(' AND ', $additionalWhere);
            }
        }
        
        $sql = "
            SELECT 
                usuario_apelido,
                usuario_poid,
                COUNT(DISTINCT lead_id) as total_leads,
                COUNT(DISTINCT CASE WHEN pedido_poid IS NULL OR pedido_poid = '' THEN lead_id END) as total_consultas,
                COUNT(DISTINCT CASE WHEN pedido_poid IS NOT NULL AND pedido_poid != '' THEN lead_id END) as total_pedidos,
                COUNT(DISTINCT produto_poid) as total_produtos_vendedor,
                COUNT(DISTINCT cliente_poid) as total_clientes_vendedor,
                SUM(valor_total_item) as valor_total_vendedor,
                SUM(CASE WHEN pedido_poid IS NULL OR pedido_poid = '' THEN valor_total_item ELSE 0 END) as valor_total_consultas,
                SUM(CASE WHEN pedido_poid IS NOT NULL AND pedido_poid != '' THEN valor_total_item ELSE 0 END) as valor_total_pedidos,
                AVG(valor_total_item) as valor_medio_vendedor,
                COUNT(CASE WHEN lead_autorizado = '1' THEN 1 END) as leads_autorizados,
                ROUND((COUNT(CASE WHEN lead_autorizado = '1' THEN 1 END) * 100.0 / COUNT(DISTINCT lead_id)), 1) as taxa_aprovacao,
                ROUND((COUNT(DISTINCT CASE WHEN pedido_poid IS NOT NULL AND pedido_poid != '' THEN lead_id END) * 100.0 / COUNT(DISTINCT lead_id)), 1) as taxa_conversao,
                SUM(produto_quantidade) as quantidade_total,
                SUM(CASE WHEN pedido_poid IS NULL OR pedido_poid = '' THEN produto_quantidade ELSE 0 END) as quantidade_consultas,
                SUM(CASE WHEN pedido_poid IS NOT NULL AND pedido_poid != '' THEN produto_quantidade ELSE 0 END) as quantidade_pedidos,
                GROUP_CONCAT(DISTINCT cliente_nome ORDER BY cliente_nome SEPARATOR ', ') as clientes_atendidos
            FROM vw_leads_produtos
            $where
            GROUP BY usuario_apelido, usuario_poid
            ORDER BY $orderColumn $orderDirection
            LIMIT $limit OFFSET $offset
        ";

        // Contar total de registros para paginação
        $countSql = "
            SELECT COUNT(DISTINCT usuario_poid) as total
            FROM vw_leads_produtos
            $where
        ";

        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute()->as_array();
        
        // Executar consulta de contagem
        $countQuery = DB::query(Database::SELECT, $countSql);
        $countResult = $countQuery->execute()->as_array();
        $totalRecords = $countResult[0]['total'];
        
        // Formatar valores monetários e quantidade
        foreach ($result as &$row) {
            if (isset($row['valor_medio_vendedor'])) {
                $row['valor_medio_vendedor_formatado'] = number_format($row['valor_medio_vendedor'], 2, ',', '.');
            }
            if (isset($row['valor_total_vendedor'])) {
                $row['valor_total_vendedor_formatado'] = number_format($row['valor_total_vendedor'], 2, ',', '.');
            }
            if (isset($row['valor_total_consultas'])) {
                $row['valor_total_consultas_formatado'] = number_format($row['valor_total_consultas'], 2, ',', '.');
            }
            if (isset($row['valor_total_pedidos'])) {
                $row['valor_total_pedidos_formatado'] = number_format($row['valor_total_pedidos'], 2, ',', '.');
            }
            if (isset($row['quantidade_total'])) {
                $row['quantidade_total_formatado'] = number_format($row['quantidade_total'], 0, ',', '.');
            }
            if (isset($row['quantidade_consultas'])) {
                $row['quantidade_consultas_formatado'] = number_format($row['quantidade_consultas'], 0, ',', '.');
            }
            if (isset($row['quantidade_pedidos'])) {
                $row['quantidade_pedidos_formatado'] = number_format($row['quantidade_pedidos'], 0, ',', '.');
            }
            // Limitar lista de clientes para exibição
            if (isset($row['clientes_atendidos']) && strlen($row['clientes_atendidos']) > 100) {
                $row['clientes_atendidos_resumo'] = substr($row['clientes_atendidos'], 0, 97) . '...';
            } else {
                $row['clientes_atendidos_resumo'] = $row['clientes_atendidos'];
            }
        }
        
        return [
            'data' => $result,
            'pagination' => [
                'total' => $totalRecords,
                'limit' => $limit,
                'offset' => $offset,
                'current_page' => $totalRecords > 0 ? floor($offset / $limit) + 1 : 1,
                'total_pages' => max(1, ceil($totalRecords / $limit)),
                'has_previous' => $offset > 0,
                'has_next' => ($offset + $limit) < $totalRecords
            ]
        ];
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

    /**
     * Obter totais consolidados por lead
     */
    private function getTotaisPorLead($where, $limit = 50, $offset = 0)
    {
        // Parâmetros de ordenação para leads
        $leadOrderBy = $this->request->query('lead_order_by') ?: 'valor_total_lead';
        $leadOrderDir = $this->request->query('lead_order_dir') ?: 'DESC';
        
        // Filtros específicos para leads
        $leadFiltros = [
            'cliente_nome' => $this->request->query('lead_filter_cliente_nome'),
            'usuario_apelido' => $this->request->query('lead_filter_usuario_apelido'),
            'pedido_poid' => $this->request->query('lead_filter_pedido_poid'),
            'lead_id' => $this->request->query('lead_filter_lead_id'),
            'observacao_financeiro' => $this->request->query('lead_filter_observacao_financeiro'),
            'observacao_interna' => $this->request->query('lead_filter_observacao_interna'),
        ];

        // Mapeamento de colunas para ordenação
        $allowedColumns = [
            'data_emissao' => 'DATE(data_emissao_lead)',
            'hora_emissao' => 'TIME(data_emissao_lead)',
            'lead_id' => 'lead_id',
            'pedido_poid' => 'pedido_poid',
            'cliente_nome' => 'cliente_nome',
            'cliente_cidade' => 'cliente_cidade',
            'cliente_estado' => 'cliente_estado',
            'usuario_apelido' => 'usuario_apelido',
            'observacao_financeiro' => 'observacao_financeiro',
            'observacao_interna' => 'observacao_interna',
            'total_produtos' => 'total_produtos',
            'total_quantidade' => 'total_quantidade',
            'valor_total_lead' => 'valor_total_lead',
            'valor_medio_lead' => 'valor_medio_lead',
            'status' => 'lead_autorizado'
        ];
        
        // Validar coluna de ordenação
        $orderColumn = isset($allowedColumns[$leadOrderBy]) ? $allowedColumns[$leadOrderBy] : 'valor_total_lead';
        $orderDirection = (strtoupper($leadOrderDir) === 'ASC') ? 'ASC' : 'DESC';
        
        // Aplicar filtros de colunas
        $additionalWhere = [];
        if (!empty($leadFiltros['cliente_nome'])) {
            $additionalWhere[] = "cliente_nome LIKE '%" . addslashes($leadFiltros['cliente_nome']) . "%'";
        }
        if (!empty($leadFiltros['cliente_cidade'])) {
            $additionalWhere[] = "cliente_cidade LIKE '%" . addslashes($leadFiltros['cliente_cidade']) . "%'";
        }
        if (!empty($leadFiltros['cliente_estado'])) {
            $additionalWhere[] = "cliente_estado LIKE '%" . addslashes($leadFiltros['cliente_estado']) . "%'";
        }
        if (!empty($leadFiltros['usuario_apelido'])) {
            $additionalWhere[] = "usuario_apelido LIKE '%" . addslashes($leadFiltros['usuario_apelido']) . "%'";
        }
        if (!empty($leadFiltros['pedido_poid'])) {
            $additionalWhere[] = "pedido_poid = '" . addslashes($leadFiltros['pedido_poid']) . "'";
        }
        if (!empty($leadFiltros['lead_id'])) {
            $additionalWhere[] = "lead_id = '" . addslashes($leadFiltros['lead_id']) . "'";
        }
        
        // Combinar filtros com WHERE existente
        if (!empty($additionalWhere)) {
            if (trim($where) === '' || $where === 'WHERE 1=1') {
                $where = 'WHERE ' . implode(' AND ', $additionalWhere);
            } else {
                $where .= ' AND ' . implode(' AND ', $additionalWhere);
            }
        }

        $sql = "
            SELECT 
                lead_id,
                pedido_poid,
                cliente_poid,
                cliente_nome,
                cliente_cidade,
                cliente_estado,
                usuario_apelido,
                usuario_poid,
                DATE(data_emissao_lead) as data_emissao,
                TIME(data_emissao_lead) as hora_emissao,
                lead_autorizado as status,
                COUNT(DISTINCT produto_poid) as total_produtos,
                SUM(produto_quantidade) as total_quantidade,
                SUM(valor_total_item) as valor_total_lead,
                AVG(valor_total_item) as valor_medio_lead
            FROM vw_leads_produtos
            $where
            GROUP BY lead_id, pedido_poid, cliente_poid, cliente_nome, cliente_cidade, cliente_estado, usuario_apelido, usuario_poid, data_emissao_lead, lead_autorizado
            ORDER BY $orderColumn $orderDirection,
                     DATE(data_emissao_lead) DESC,
                     TIME(data_emissao_lead) DESC
            LIMIT $limit OFFSET $offset
        ";

        // Contar total de registros para paginação
        $countSql = "
            SELECT COUNT(DISTINCT pedido_poid) as total
            FROM vw_leads_produtos
            $where
        ";

        // Debug: log da consulta SQL
        error_log("DEBUG getTotaisPorLead - SQL: " . $sql);
        error_log("DEBUG getTotaisPorLead - WHERE: " . $where);
        error_log("DEBUG getTotaisPorLead - ORDER BY: $orderColumn $orderDirection");

        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute()->as_array();
        
        // Executar consulta de contagem
        $countQuery = DB::query(Database::SELECT, $countSql);
        $countResult = $countQuery->execute()->as_array();
        $totalRecords = $countResult[0]['total'];
        
        // Debug: log do resultado
        error_log("DEBUG getTotaisPorLead - Total de registros retornados: " . count($result));
        if (!empty($result)) {
            error_log("DEBUG getTotaisPorLead - Primeiro registro: " . json_encode($result[0]));
        }
        
        // Formatar valores monetários e quantidade
        foreach ($result as &$row) {
            if (isset($row['valor_total_lead'])) {
                $row['valor_total_lead_formatado'] = number_format($row['valor_total_lead'], 2, ',', '.');
            }
            if (isset($row['valor_medio_lead'])) {
                $row['valor_medio_lead_formatado'] = number_format($row['valor_medio_lead'], 2, ',', '.');
            }
            if (isset($row['total_quantidade'])) {
                $row['total_quantidade_formatado'] = number_format($row['total_quantidade'], 0, ',', '.');
            }
        }
        
        return [
            'data' => $result,
            'pagination' => [
                'total' => $totalRecords,
                'limit' => $limit,
                'offset' => $offset,
                'current_page' => $totalRecords > 0 ? floor($offset / $limit) + 1 : 1,
                'total_pages' => max(1, ceil($totalRecords / $limit)),
                'has_previous' => $offset > 0,
                'has_next' => ($offset + $limit) < $totalRecords
            ]
        ];
    }

    /**
     * API para dados em JSON
     */
    public function action_api()
    {
        $this->response->headers('Content-Type', 'application/json');
        
        $dataInicio = $this->request->query('data_inicio') ?: date('Y-m-01');
        $dataFim = $this->request->query('data_fim') ?: date('Y-m-d', strtotime('+1 day'));
        
        $where = " WHERE data_emissao_lead BETWEEN '$dataInicio' AND '$dataFim'";
        
        $stats = $this->getGeneralStats($where);
        $topProdutos = $this->getTopProdutos($where, 10);
        $leadsPorPeriodo = $this->getLeadsPorPeriodo($dataInicio, $dataFim);
        
        $response = [
            'stats' => $stats,
            'topProdutos' => $topProdutos,
            'leadsPorPeriodo' => $leadsPorPeriodo,
            'periodo' => [
                'inicio' => $dataInicio,
                'fim' => $dataFim
            ]
        ];
        
        return json_encode($response);
    }

    /**
     * Testar conexão com banco e verificar registros de hoje
     */
    public function action_test_db()
    {
        $this->response->headers('Content-Type', 'application/json');
        
        $dia18 = '2025-08-18';
        $dia15 = '2025-08-15';
        $tests = [];
        
        try {
            // Teste 1: Comparar dia 18 vs dia 15 na tabela original
            $sql1a = "SELECT COUNT(*) as total FROM sCart WHERE DATE(dCart) = '$dia18'";
            $query1a = DB::query(Database::SELECT, $sql1a);
            $result1a = $query1a->execute()->as_array();
            
            $sql1b = "SELECT COUNT(*) as total FROM sCart WHERE DATE(dCart) = '$dia15'";
            $query1b = DB::query(Database::SELECT, $sql1b);
            $result1b = $query1b->execute()->as_array();
            
            $tests['test1_sCart_comparison'] = [
                'dia_18' => $result1a[0]['total'],
                'dia_15' => $result1b[0]['total'],
                'description' => 'Comparação de registros na tabela sCart original'
            ];
            
            // Teste 2: Comparar dia 18 vs dia 15 na view
            $sql2a = "SELECT COUNT(*) as total FROM vw_leads_produtos WHERE DATE(data_emissao_lead) = '$dia18'";
            $query2a = DB::query(Database::SELECT, $sql2a);
            $result2a = $query2a->execute()->as_array();
            
            $sql2b = "SELECT COUNT(*) as total FROM vw_leads_produtos WHERE DATE(data_emissao_lead) = '$dia15'";
            $query2b = DB::query(Database::SELECT, $sql2b);
            $result2b = $query2b->execute()->as_array();
            
            $tests['test2_view_comparison'] = [
                'dia_18' => $result2a[0]['total'],
                'dia_15' => $result2b[0]['total'],
                'description' => 'Comparação de registros na view vw_leads_produtos'
            ];
            
            // Teste 3: Registros específicos do dia 18
            $sql3 = "SELECT lead_id, data_emissao_lead, cliente_poid, produto_poid, valor_total_item, vendedor_poid, cliente_nome FROM vw_leads_produtos WHERE DATE(data_emissao_lead) = '$dia18' LIMIT 5";
            $query3 = DB::query(Database::SELECT, $sql3);
            $result3 = $query3->execute()->as_array();
            $tests['test3_dia18_records'] = [
                'sql' => $sql3,
                'result' => $result3,
                'description' => 'Registros específicos do dia 18'
            ];
            
            // Teste 4: Aplicar os mesmos filtros da aplicação
            $where = " WHERE data_emissao_lead BETWEEN '$dia18' AND '$dia18'";
            $sql4 = "SELECT COUNT(*) as total FROM vw_leads_produtos $where";
            $query4 = DB::query(Database::SELECT, $sql4);
            $result4 = $query4->execute()->as_array();
            $tests['test4_app_filter'] = [
                'sql' => $sql4,
                'result' => $result4[0]['total'],
                'description' => 'Filtro exato da aplicação para dia 18'
            ];
            
            // Teste 5: Verificar se há restrições por vendedor nos dados do dia 18
            $sql5 = "SELECT DISTINCT vendedor_poid FROM vw_leads_produtos WHERE DATE(data_emissao_lead) = '$dia18'";
            $query5 = DB::query(Database::SELECT, $sql5);
            $result5 = $query5->execute()->as_array();
            $tests['test5_vendedores_dia18'] = [
                'sql' => $sql5,
                'result' => $result5,
                'description' => 'Vendedores que têm leads no dia 18'
            ];
            
            // Teste 6: Testar com JOIN explícito como a view faz
            $sql6 = "SELECT COUNT(*) as total FROM sCart s 
                     LEFT JOIN icart i ON i.cSCart = s.cSCart 
                     WHERE DATE(s.dCart) = '$dia18' AND i.cProduct IS NOT NULL";
            $query6 = DB::query(Database::SELECT, $sql6);
            $result6 = $query6->execute()->as_array();
            $tests['test6_join_with_icart'] = [
                'sql' => $sql6,
                'result' => $result6[0]['total'],
                'description' => 'Registros do dia 18 com JOIN icart (como na view)'
            ];
            
        } catch (Exception $e) {
            $tests['error'] = [
                'message' => $e->getMessage(),
                'description' => 'Erro durante os testes'
            ];
        }
        
        return json_encode([
            'data_testada' => $dia18,
            'comparacao_com' => $dia15,
            'timestamp' => date('Y-m-d H:i:s'),
            'tests' => $tests
        ], JSON_PRETTY_PRINT);
    }

    /**
     * EMERGÊNCIA: Função para forçar dados do dia 18
     */
    public function action_force_day18()
    {
        // Forçar dados do dia 18 sem filtros
        $dataInicio = '2025-08-18';
        $dataFim = '2026-08-18';
        $where = " WHERE data_emissao_lead BETWEEN '$dataInicio' AND '$dataFim'";
        
        // Estatísticas forçadas
        $stats = $this->getGeneralStats($where);
        
        // Top produtos forçados
        $topProdutos = $this->getTopProdutos($where, 50);
        
        // Formatar quantidade nos dados forçados
        foreach ($topProdutos['data'] as &$produto) {
            if (isset($produto['total_quantidade'])) {
                $produto['total_quantidade_formatado'] = number_format($produto['total_quantidade'], 0, ',', '.');
            }
        }
        
        // Resposta forçada
        $response = [
            'stats' => $stats,
            'topProdutos' => $topProdutos['data'],
            'topProdutosPagination' => $topProdutos['pagination'],
            'filtros' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'segmento' => '',
                'status' => '',
                'limit' => 50
            ],
            'debug_info' => [
                'where_usado' => $where,
                'total_stats' => count($stats),
                'total_produtos' => count($topProdutos['data']),
                'forcado' => true
            ]
        ];

        return $this->render('lead/statistics', $response);
    }

    /**
     * TESTE SQL LOCAL - View vw_leads_produtos
     */
    public function action_sql_test()
    {
        $this->response->headers('Content-Type', 'application/json');
        
        $tests = [];
        $startTime = microtime(true);
        
        try {
            // TESTE 1: Contagem geral da view
            $sql1 = "SELECT COUNT(*) as total_registros FROM vw_leads_produtos";
            $query1 = DB::query(Database::SELECT, $sql1);
            $result1 = $query1->execute()->as_array();
            $tests['teste_1_contagem_geral'] = [
                'sql' => $sql1,
                'resultado' => $result1[0]['total_registros'],
                'tempo_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'descricao' => 'Total de registros na view'
            ];

            // TESTE 2: Registros por data específica (18/08/2025)
            $resetTime = microtime(true);
            $sql2 = "SELECT COUNT(*) as total, MIN(data_emissao_lead) as primeira_data, MAX(data_emissao_lead) as ultima_data 
                     FROM vw_leads_produtos 
                     WHERE DATE(data_emissao_lead) => '2025-08-18'";
            $query2 = DB::query(Database::SELECT, $sql2);
            $result2 = $query2->execute()->as_array();
            $tests['teste_2_data_18_agosto'] = [
                'sql' => $sql2,
                'resultado' => $result2[0],
                'tempo_ms' => round((microtime(true) - $resetTime) * 1000, 2),
                'descricao' => 'Registros específicos do dia 18/08/2025'
            ];

            // TESTE 3: Amostra de dados com campos principais
            $resetTime = microtime(true);
            $sql3 = "SELECT lead_id, data_emissao_lead, cliente_nome, produto_poid, produto_modelo, 
                            produto_quantidade, produto_valor, valor_total_item, vendedor_poid
                     FROM vw_leads_produtos 
                     WHERE DATE(data_emissao_lead) => '2025-08-18'
                     ORDER BY valor_total_item DESC 
                     LIMIT 5";
            $query3 = DB::query(Database::SELECT, $sql3);
            $result3 = $query3->execute()->as_array();
            $tests['teste_3_amostra_dados'] = [
                'sql' => $sql3,
                'resultado' => $result3,
                'tempo_ms' => round((microtime(true) - $resetTime) * 1000, 2),
                'descricao' => 'Amostra dos 5 maiores valores do dia 18/08'
            ];

            // TESTE 4: Estatísticas agregadas
            $resetTime = microtime(true);
            $sql4 = "SELECT 
                        COUNT(DISTINCT lead_id) as total_leads,
                        COUNT(DISTINCT produto_poid) as total_produtos,
                        COUNT(DISTINCT cliente_poid) as total_clientes,
                        SUM(produto_quantidade) as soma_quantidade,
                        SUM(valor_total_item) as soma_valor_total,
                        AVG(valor_total_item) as media_valor,
                        MAX(valor_total_item) as maior_valor,
                        MIN(valor_total_item) as menor_valor
                     FROM vw_leads_produtos 
                     WHERE DATE(data_emissao_lead) = '2025-08-18'";
            $query4 = DB::query(Database::SELECT, $sql4);
            $result4 = $query4->execute()->as_array();
            $tests['teste_4_estatisticas'] = [
                'sql' => $sql4,
                'resultado' => $result4[0],
                'tempo_ms' => round((microtime(true) - $resetTime) * 1000, 2),
                'descricao' => 'Estatísticas agregadas do dia 18/08'
            ];

            // TESTE 5: Teste de JOIN com campos específicos
            $resetTime = microtime(true);
            $sql5 = "SELECT 
                        COUNT(*) as registros_com_cliente,
                        COUNT(CASE WHEN cliente_nome IS NOT NULL THEN 1 END) as com_nome_cliente,
                        COUNT(CASE WHEN produto_modelo IS NOT NULL THEN 1 END) as com_modelo_produto,
                        COUNT(CASE WHEN usuario_apelido IS NOT NULL THEN 1 END) as com_usuario
                     FROM vw_leads_produtos 
                     WHERE DATE(data_emissao_lead) => '2025-08-18'";
            $query5 = DB::query(Database::SELECT, $sql5);
            $result5 = $query5->execute()->as_array();
            $tests['teste_5_integridade_joins'] = [
                'sql' => $sql5,
                'resultado' => $result5[0],
                'tempo_ms' => round((microtime(true) - $resetTime) * 1000, 2),
                'descricao' => 'Integridade dos JOINs da view'
            ];

            // TESTE 6: Teste com filtro BETWEEN (como na aplicação)
            $resetTime = microtime(true);
            $sql6 = "SELECT COUNT(*) as total 
                     FROM vw_leads_produtos 
                     WHERE data_emissao_lead BETWEEN '2025-08-18' AND '2025-08-18'";
            $query6 = DB::query(Database::SELECT, $sql6);
            $result6 = $query6->execute()->as_array();
            $tests['teste_6_filtro_between'] = [
                'sql' => $sql6,
                'resultado' => $result6[0]['total'],
                'tempo_ms' => round((microtime(true) - $resetTime) * 1000, 2),
                'descricao' => 'Teste com filtro BETWEEN exato da aplicação'
            ];

            // TESTE 7: Verificar estrutura da view
            $resetTime = microtime(true);
            $sql7 = "DESCRIBE vw_leads_produtos";
            $query7 = DB::query(Database::SELECT, $sql7);
            $result7 = $query7->execute()->as_array();
            $tests['teste_7_estrutura_view'] = [
                'sql' => $sql7,
                'resultado' => count($result7) . ' campos encontrados',
                'campos' => array_column($result7, 'Field'),
                'tempo_ms' => round((microtime(true) - $resetTime) * 1000, 2),
                'descricao' => 'Estrutura e campos da view'
            ];

        } catch (Exception $e) {
            $tests['erro'] = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'descricao' => 'Erro durante execução dos testes'
            ];
        }

        $tempoTotal = round((microtime(true) - $startTime) * 1000, 2);

        return json_encode([
            'teste_sql_local' => true,
            'view_testada' => 'vw_leads_produtos',
            'data_teste' => date('Y-m-d H:i:s'),
            'tempo_total_ms' => $tempoTotal,
            'total_testes' => count($tests),
            'resultados' => $tests
        ], JSON_PRETTY_PRINT);
    }

    /**
     * VERIFICAR DATA DO SISTEMA - SAO PAULO
     */
    public function action_system_date()
    {
        $this->response->headers('Content-Type', 'application/json');
        
        try {
            // Data PHP - Original e São Paulo
            $phpDateUTC = date('Y-m-d H:i:s');
            $phpTimezone = date_default_timezone_get();
            
            // Definir timezone São Paulo
            date_default_timezone_set('America/Sao_Paulo');
            $phpDateSP = date('Y-m-d H:i:s');
            $phpTimezoneSP = date_default_timezone_get();
            
            // Data MySQL - Original e São Paulo
            $sqlDate = "SELECT 
                           NOW() as mysql_now,
                           CURDATE() as mysql_date,
                           CURTIME() as mysql_time,
                           CONVERT_TZ(NOW(), '+00:00', '-03:00') as mysql_sp_now,
                           DATE(CONVERT_TZ(NOW(), '+00:00', '-03:00')) as mysql_sp_date,
                           TIME(CONVERT_TZ(NOW(), '+00:00', '-03:00')) as mysql_sp_time";
            $queryDate = DB::query(Database::SELECT, $sqlDate);
            $resultDate = $queryDate->execute()->as_array();
            
            // Teste timezone MySQL
            $sqlTimezone = "SELECT @@global.time_zone as global_tz, @@session.time_zone as session_tz";
            $queryTz = DB::query(Database::SELECT, $sqlTimezone);
            $resultTz = $queryTz->execute()->as_array();
            
            // Verificar dados do mês atual vs 2025-08 (usando data São Paulo)
            $sqlCount = "SELECT 
                            COUNT(*) as total_geral,
                            COUNT(CASE WHEN YEAR(CONVERT_TZ(data_emissao_lead, '+00:00', '-03:00')) = YEAR(CONVERT_TZ(NOW(), '+00:00', '-03:00')) 
                                       AND MONTH(CONVERT_TZ(data_emissao_lead, '+00:00', '-03:00')) = MONTH(CONVERT_TZ(NOW(), '+00:00', '-03:00')) THEN 1 END) as mes_atual_sp,
                            COUNT(CASE WHEN DATE(CONVERT_TZ(data_emissao_lead, '+00:00', '-03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '-03:00')) THEN 1 END) as hoje_sp,
                            COUNT(CASE WHEN DATE(data_emissao_lead) = '2025-08-18' THEN 1 END) as dia_18_agosto,
                            COUNT(CASE WHEN YEAR(data_emissao_lead) = 2025 AND MONTH(data_emissao_lead) = 8 THEN 1 END) as agosto_2025,
                            COUNT(CASE WHEN DATE(data_emissao_lead) >= CURDATE() - INTERVAL 7 DAY THEN 1 END) as ultimos_7_dias
                         FROM vw_leads_produtos";
            $queryCount = DB::query(Database::SELECT, $sqlCount);
            $resultCount = $queryCount->execute()->as_array();
            
            // Dados de hoje específicos
            $sqlHoje = "SELECT 
                           produto_poid,
                           cliente_nome,
                           DATE(data_emissao_lead) as data_emissao,
                           TIME(data_emissao_lead) as hora_emissao,
                           DATE(CONVERT_TZ(data_emissao_lead, '+00:00', '-03:00')) as data_sp,
                           TIME(CONVERT_TZ(data_emissao_lead, '+00:00', '-03:00')) as hora_sp,
                           valor_total_item
                        FROM vw_leads_produtos 
                        WHERE DATE(CONVERT_TZ(data_emissao_lead, '+00:00', '-03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '-03:00'))
                        ORDER BY data_emissao_lead DESC
                        LIMIT 5";
            $queryHoje = DB::query(Database::SELECT, $sqlHoje);
            $resultHoje = $queryHoje->execute()->as_array();
            
            return json_encode([
                'data_sistema_sao_paulo' => [
                    'php_original' => $phpDateUTC,
                    'php_timezone_original' => $phpTimezone,
                    'php_sao_paulo' => $phpDateSP,
                    'php_timezone_sp' => $phpTimezoneSP,
                    'mysql_original' => $resultDate[0]['mysql_now'],
                    'mysql_sao_paulo' => $resultDate[0]['mysql_sp_now'],
                    'mysql_date_sp' => $resultDate[0]['mysql_sp_date'],
                    'mysql_time_sp' => $resultDate[0]['mysql_sp_time'],
                    'mysql_timezone_global' => $resultTz[0]['global_tz'],
                    'mysql_timezone_session' => $resultTz[0]['session_tz']
                ],
                'comparacao_datas_sp' => [
                    'total_registros_view' => $resultCount[0]['total_geral'],
                    'registros_mes_atual_sp' => $resultCount[0]['mes_atual_sp'],
                    'registros_hoje_sp' => $resultCount[0]['hoje_sp'],
                    'registros_18_agosto_2025' => $resultCount[0]['dia_18_agosto'],
                    'registros_agosto_2025' => $resultCount[0]['agosto_2025'],
                    'registros_ultimos_7_dias' => $resultCount[0]['ultimos_7_dias']
                ],
                'registros_hoje_sp_sample' => $resultHoje,
                'analise_sp' => [
                    'data_atual_sp' => $phpDateSP,
                    'mes_atual_sp' => substr($phpDateSP, 0, 7),
                    'dia_atual_sp' => substr($phpDateSP, 0, 10),
                    'eh_agosto_2025' => (substr($phpDateSP, 0, 7) === '2025-08'),
                    'diferenca_utc_sp_horas' => -3,
                    'observacao' => 'Se data atual for agosto 2025, dados aparecerão normalmente. Se for outra data, explica por que dados de agosto 2025 não aparecem no filtro do mês atual.'
                ]
            ], JSON_PRETTY_PRINT);
            
        } catch (Exception $e) {
            return json_encode([
                'erro' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], JSON_PRETTY_PRINT);
        }
    }

    /**
     * Novo endpoint para Top Produtos
     */
    public function action_top_produtos()
    {
        // Data padrão: início do mês e hoje + 1 dia
        $dataInicio = $this->request->query('data_inicio') ?: date('Y-m-01');
        $dataFim = $this->request->query('data_fim') ?: date('Y-m-d', strtotime('+1 day'));
        $segmento = $this->request->query('segmento');
        $status = $this->request->query('status');
        $limit = $this->request->query('limit') ?: 50;
        
        // Parâmetros de paginação
        $offset = $this->request->query('offset') ?: 0;
        
        // Parâmetros de ordenação
        $orderBy = $this->request->query('order_by') ?: 'valor_total_item';
        $orderDir = $this->request->query('order_dir') ?: 'DESC';
        
        // Filtros de colunas
        $filtros = [
            'cliente_nome' => $this->request->query('filter_cliente_nome'),
            'cliente_cidade' => $this->request->query('filter_cliente_cidade'),
            'cliente_estado' => $this->request->query('filter_cliente_estado'),
            'usuario_apelido' => $this->request->query('filter_usuario_apelido'),
            'produto_modelo' => $this->request->query('filter_produto_modelo'),
            'produto_segmento_nome' => $this->request->query('filter_produto_segmento_nome'),
            'produto_poid' => $this->request->query('filter_produto_poid'),
            'pedido_poid' => $this->request->query('filter_pedido_poid'),
        ];

        // Construir filtros WHERE
        $where = " WHERE data_emissao_lead BETWEEN '$dataInicio' AND '$dataFim'";
        
        if ($segmento) {
            $where .= " AND lead_tipo = '$segmento'";
        }
        
        if ($status) {
            $where .= " AND lead_autorizado = '$status'";
        }

        // Estatísticas gerais
        $stats = $this->getGeneralStats($where);
        
        // Formatar valores monetários
        if (isset($stats['total_valor'])) {
            $stats['total_valor_formatado'] = number_format($stats['total_valor'], 2, ',', '.');
        }
        if (isset($stats['valor_medio'])) {
            $stats['valor_medio_formatado'] = number_format($stats['valor_medio'], 2, ',', '.');
        }
        
        // Top produtos
        $topProdutos = $this->getTopProdutos($where, $limit, $orderBy, $orderDir, $filtros, $offset);

        $response = [
            'page_title' => 'Top Produtos por Valor Total',
            'stats' => $stats,
            'topProdutos' => $topProdutos['data'],
            'topProdutosPagination' => $topProdutos['pagination'],
            'filtros' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'segmento' => $segmento,
                'status' => $status,
                'limit' => $limit,
                'order_by' => $orderBy,
                'order_dir' => $orderDir,
                'filter_cliente_nome' => $filtros['cliente_nome'],
                'filter_cliente_cidade' => $filtros['cliente_cidade'],
                'filter_cliente_estado' => $filtros['cliente_estado'],
                'filter_usuario_apelido' => $filtros['usuario_apelido'],
                'filter_produto_modelo' => $filtros['produto_modelo'],
                'filter_produto_segmento_nome' => $filtros['produto_segmento_nome'],
                'filter_produto_poid' => $filtros['produto_poid'],
                'filter_pedido_poid' => $filtros['pedido_poid'],
                'offset' => $offset,
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

        return $this->render('lead/top_produtos', $response);
    }

    /**
     * Novo endpoint para Top Vendedores
     */
    public function action_top_vendedores()
    {
        // Data padrão: início do mês e hoje + 1 dia
        $dataInicio = $this->request->query('data_inicio') ?: date('Y-m-01');
        $dataFim = $this->request->query('data_fim') ?: date('Y-m-d', strtotime('+1 day'));
        $segmento = $this->request->query('segmento');
        $status = $this->request->query('status');
        $limit = $this->request->query('limit') ?: 50;
        
        // Parâmetros de paginação
        $offset = $this->request->query('offset') ?: 0;
        
        // Parâmetros de ordenação
        $orderBy = $this->request->query('order_by') ?: 'valor_total_vendedor';
        $orderDir = $this->request->query('order_dir') ?: 'DESC';
        
        // Filtros de colunas
        $filtros = [
            'usuario_apelido' => $this->request->query('filter_usuario_apelido'),
            'cliente_nome' => $this->request->query('filter_cliente_nome'),
            'cliente_cidade' => $this->request->query('filter_cliente_cidade'),
            'cliente_estado' => $this->request->query('filter_cliente_estado'),
        ];

        // Construir filtros WHERE
        $where = " WHERE data_emissao_lead BETWEEN '$dataInicio' AND '$dataFim'";
        
        if ($segmento) {
            $where .= " AND lead_tipo = '$segmento'";
        }
        
        if ($status) {
            $where .= " AND lead_autorizado = '$status'";
        }

        // Estatísticas gerais separando consultas e pedidos
        $stats = $this->getGeneralStatsVendedores($where);
        
        // Formatar valores monetários
        if (isset($stats['total_valor'])) {
            $stats['total_valor_formatado'] = number_format($stats['total_valor'], 2, ',', '.');
        }
        if (isset($stats['valor_medio'])) {
            $stats['valor_medio_formatado'] = number_format($stats['valor_medio'], 2, ',', '.');
        }
        if (isset($stats['valor_consultas'])) {
            $stats['valor_consultas_formatado'] = number_format($stats['valor_consultas'], 2, ',', '.');
        }
        if (isset($stats['valor_pedidos'])) {
            $stats['valor_pedidos_formatado'] = number_format($stats['valor_pedidos'], 2, ',', '.');
        }
        
        // Top vendedores
        $topVendedores = $this->getTopVendedores($where, $limit, $orderBy, $orderDir, $filtros, $offset);

        $response = [
            'page_title' => 'Top Vendedores por Performance',
            'stats' => $stats,
            'topVendedores' => $topVendedores['data'],
            'topVendedoresPagination' => $topVendedores['pagination'],
            'filtros' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'segmento' => $segmento,
                'status' => $status,
                'limit' => $limit,
                'order_by' => $orderBy,
                'order_dir' => $orderDir,
                'filter_usuario_apelido' => $filtros['usuario_apelido'],
                'filter_cliente_nome' => $filtros['cliente_nome'],
                'filter_cliente_cidade' => $filtros['cliente_cidade'],
                'filter_cliente_estado' => $filtros['cliente_estado'],
                'offset' => $offset,
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

        return $this->render('lead/top_vendedores', $response);
    }

    /**
     * Novo endpoint para Top Leads
     */
    public function action_top_leads()
    {
        // Data padrão: início do mês e hoje + 1 dia
        $dataInicio = $this->request->query('data_inicio') ?: date('Y-m-01');
        $dataFim = $this->request->query('data_fim') ?: date('Y-m-d', strtotime('+1 day'));
        $segmento = $this->request->query('segmento');
        $status = $this->request->query('status');
        $limit = $this->request->query('limit') ?: 50;
        
        // Parâmetros de paginação
        $offset = $this->request->query('offset') ?: 0;
        
        // Parâmetros de ordenação
        $orderBy = $this->request->query('order_by') ?: 'data_emissao_lead';
        $orderDir = $this->request->query('order_dir') ?: 'DESC';
        
        // Filtros de colunas
        $filtros = [
            'cliente_nome' => $this->request->query('filter_cliente_nome'),
            'cliente_cidade' => $this->request->query('filter_cliente_cidade'),
            'cliente_estado' => $this->request->query('filter_cliente_estado'),
            'usuario_apelido' => $this->request->query('filter_usuario_apelido'),
            'tipo_lead' => $this->request->query('filter_tipo_lead'),
            'lead_tipo' => $this->request->query('filter_lead_tipo'),
        ];

        // Construir filtros WHERE
        $where = " WHERE data_emissao_lead BETWEEN '$dataInicio' AND '$dataFim'";
        
        if ($segmento) {
            $where .= " AND lead_tipo = '$segmento'";
        }
        
        if ($status) {
            $where .= " AND lead_autorizado = '$status'";
        }

        // Estatísticas gerais separando consultas e pedidos
        $stats = $this->getGeneralStatsLeads($where);
        
        // Formatar valores monetários
        if (isset($stats['total_valor'])) {
            $stats['total_valor_formatado'] = number_format($stats['total_valor'], 2, ',', '.');
        }
        if (isset($stats['valor_medio'])) {
            $stats['valor_medio_formatado'] = number_format($stats['valor_medio'], 2, ',', '.');
        }
        if (isset($stats['valor_consultas'])) {
            $stats['valor_consultas_formatado'] = number_format($stats['valor_consultas'], 2, ',', '.');
        }
        if (isset($stats['valor_pedidos'])) {
            $stats['valor_pedidos_formatado'] = number_format($stats['valor_pedidos'], 2, ',', '.');
        }
        
        // Top leads
        $topLeads = $this->getTopLeads($where, $limit, $orderBy, $orderDir, $filtros, $offset);

        $response = [
            'page_title' => 'Top Leads por Período',
            'stats' => $stats,
            'topLeads' => $topLeads['data'],
            'topLeadsPagination' => $topLeads['pagination'],
            'filtros' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'segmento' => $segmento,
                'status' => $status,
                'limit' => $limit,
                'order_by' => $orderBy,
                'order_dir' => $orderDir,
                'filter_cliente_nome' => $filtros['cliente_nome'],
                'filter_cliente_cidade' => $filtros['cliente_cidade'],
                'filter_cliente_estado' => $filtros['cliente_estado'],
                'filter_usuario_apelido' => $filtros['usuario_apelido'],
                'filter_tipo_lead' => $filtros['tipo_lead'],
                'offset' => $offset,
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

        return $this->render('lead/top_leads', $response);
    }

    /**
     * Exportar dados para CSV
     */
    public function action_export()
    {
        $dataInicio = $this->request->query('data_inicio') ?: date('Y-m-01');
        $dataFim = $this->request->query('data_fim') ?: date('Y-m-d', strtotime('+1 day'));
        
        $where = " WHERE data_emissao_lead BETWEEN '$dataInicio' AND '$dataFim'";
        
        $sql = "
            SELECT 
                lead_id,
                data_emissao_lead,
                cliente_poid,
                cliente_nome,
                usuario_apelido,
                lead_tipo,
                lead_autorizado,
                produto_poid,
                produto_similar,
                produto_quantidade,
                produto_valor,
                valor_total_item,
                status_produto_desc
            FROM vw_leads_produtos
            $where
            ORDER BY data_emissao_lead DESC
        ";

        $query = DB::query(Database::SELECT, $sql);
        $data = $query->execute()->as_array();
        
        // Configurar headers para download CSV
        $filename = 'estatisticas_leads_' . date('Y-m-d') . '.csv';
        $this->response->headers('Content-Type', 'text/csv');
        $this->response->headers('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        // Gerar CSV
        $output = fopen('php://output', 'w');
        
        // Headers do CSV
        fputcsv($output, [
            'Lead ID', 'Data Emissão', 'Cliente POID', 'Cliente Nome', 'Usuário Apelido',
            'Tipo Lead', 'Autorizado', 'Produto POID', 'Produto Similar', 'Quantidade',
            'Valor Unitário', 'Valor Total', 'Status'
        ]);
        
        // Dados
        foreach ($data as $row) {
            fputcsv($output, [
                $row['lead_id'],
                $row['data_emissao_lead'],
                $row['cliente_poid'],
                $row['cliente_nome'],
                $row['usuario_apelido'],
                $row['lead_tipo'],
                $row['lead_autorizado'],
                $row['produto_poid'],
                $row['produto_similar'],
                $row['produto_quantidade'],
                $row['produto_valor'],
                $row['valor_total_item'],
                $row['status_produto_desc']
            ]);
        }
        
        fclose($output);
        return;
    }
}
