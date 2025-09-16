<?php
class Controller_Sales_History extends Controller_Website {

    public function action_index()
    {
        $clienteID = $this->request->query('clienteID') ?: $this->request->param('id');
        $produtoISBN = $this->request->query('produto');
        $limit = $this->request->query('limit') ?: 50;
        $dataInicio = $this->request->query('data_inicio');
        $dataFim = $this->request->query('data_fim');

        if (!$clienteID) {
            die(s('<h3 align="center" style="margin-top:100px">ClienteID é obrigatório</h3>'));
        }

        $where = sprintf(" AND ClienteID = %s", $clienteID);

        // Filtro por produto específico
        if ($produtoISBN) {
            $where .= sprintf(" AND ProdutoISBN = '%s'", $produtoISBN);
        }

        // Filtro por período
        if ($dataInicio) {
            $where .= sprintf(" AND DataVenda >= '%s'", $dataInicio);
        }
        
        if ($dataFim) {
            $where .= sprintf(" AND DataVenda <= '%s'", $dataFim);
        }

        // Controle de acesso por vendedor
        if ((isset($_SESSION['MM_Depto']) && $_SESSION['MM_Depto'] == 'VENDAS') && (isset($_SESSION['MM_Nivel']) && $_SESSION['MM_Nivel'] < 3)) {
            $where .= sprintf(" AND VendedorID = %s", $_SESSION['MM_Userid']);
        }

        $sql = sprintf("
            SELECT 
                ClienteNome,
                ClienteID,
                ProdutoISBN,
                ProdutoModelo AS ProdutoNome,
                DataVenda,
                Quantidade,
                ValorBase,
                VendedorApelido AS VendedorNome,
                EstadoNome,
                MunicipioNome
            FROM Vendas_Historia 
            WHERE 1=1 
            %s
            ORDER BY DataVenda DESC 
            LIMIT %d", 
            $where, 
            $limit
        );

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        // Adicionar contador e calcular totais
        $count = 0;
        $totalQuantidade = 0;
        $totalValor = 0;

        foreach ($response as $k => $v) {
            $count++;
            $response[$k]['count'] = $count;
            $totalQuantidade += $v['Quantidade'];
            $totalValor += $v['ValorBase'];
        }

        $array = [
            'response' => $response,
            'clienteID' => $clienteID,
            'produtoISBN' => $produtoISBN,
            'totalRegistros' => $count,
            'totalQuantidade' => $totalQuantidade,
            'totalValor' => number_format($totalValor, 2, ',', '.'),
            'filtros' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'produto' => $produtoISBN,
                'limit' => $limit
            ]
        ];

        $template = $this->render('sales/history', $array);
    }

    /**
     * Método para obter resumo de vendas por cliente
     */
    public function action_summary()
    {
        $clienteID = $this->request->query('clienteID') ?: $this->request->param('id');
        $meses = $this->request->query('meses') ?: 12;

        if (!$clienteID) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['error' => 'ClienteID é obrigatório']));
            return;
        }

        $where = sprintf(" AND ClienteID = %s", $clienteID);
        $where .= sprintf(" AND DataVenda >= DATE_SUB(NOW(), INTERVAL %d MONTH)", $meses);

        // Controle de acesso por vendedor
        if ((isset($_SESSION['MM_Depto']) && $_SESSION['MM_Depto'] == 'VENDAS') && (isset($_SESSION['MM_Nivel']) && $_SESSION['MM_Nivel'] < 3)) {
            $where .= sprintf(" AND VendedorID = %s", $_SESSION['MM_Userid']);
        }

        $sql = sprintf("
            SELECT 
                COUNT(*) as totalPedidos,
                SUM(Quantidade) as totalQuantidade,
                SUM(ValorBase) as totalValor,
                AVG(ValorBase) as ticketMedio,
                MAX(DataVenda) as ultimaCompra,
                COUNT(DISTINCT ProdutoISBN) as produtosDistintos
            FROM Vendas_Historia 
            WHERE 1=1 
            %s", 
            $where
        );

        $query = DB::query(Database::SELECT, $sql);
        $summary = $query->execute()->as_array();

        // Produtos mais comprados
        $sqlProdutos = sprintf("
            SELECT 
                ProdutoISBN,
                ProdutoModelo AS ProdutoNome,
                SUM(Quantidade) as totalQuantidade,
                SUM(ValorBase) as totalValor,
                COUNT(*) as frequencia
            FROM Vendas_Historia 
            WHERE 1=1 
            %s
            GROUP BY ProdutoISBN, ProdutoModelo
            ORDER BY totalQuantidade DESC
            LIMIT 10", 
            $where
        );

        $queryProdutos = DB::query(Database::SELECT, $sqlProdutos);
        $produtos = $queryProdutos->execute()->as_array();

        $summaryData = $summary[0] ?? [];
        
        $result = [
            'success' => true,
            'summary' => [
                'totalPedidos' => $summaryData['totalPedidos'] ?? 0,
                'totalValor' => $summaryData['totalValor'] ?? 0,
                'ticketMedio' => $summaryData['ticketMedio'] ?? 0,
                'ultimaCompra' => $summaryData['ultimaCompra'] ?? null,
                'produtosDistintos' => $summaryData['produtosDistintos'] ?? 0
            ],
            'produtosMaisComprados' => $produtos,
            'clienteID' => $clienteID,
            'periodo' => $meses . ' meses'
        ];

        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode($result));
    }

    /**
     * Método para obter histórico de vendas para AJAX
     */
    public function action_ajax()
    {
        // Debug logs
        error_log('Sales History AJAX called');
        error_log('Session data: ' . print_r($_SESSION, true));
        
        $clienteID = $this->request->query('clienteID');
        $produtoISBN = $this->request->query('produtoISBN');
        $limit = $this->request->query('limit') ?: 10;

        error_log('ClienteID received: ' . $clienteID);

        if (!$clienteID) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['error' => 'ClienteID é obrigatório']));
            return;
        }

        $where = sprintf(" AND ClienteID = %s", $clienteID);

        if ($produtoISBN) {
            $where .= sprintf(" AND ProdutoISBN = '%s'", $produtoISBN);
        }

        // Controle de acesso por vendedor
        if ((isset($_SESSION['MM_Depto']) && $_SESSION['MM_Depto'] == 'VENDAS') && (isset($_SESSION['MM_Nivel']) && $_SESSION['MM_Nivel'] < 3)) {
            $where .= sprintf(" AND VendedorID = %s", $_SESSION['MM_Userid']);
        }

        $sql = sprintf("
            SELECT 
                DataVenda,
                Quantidade,
                ValorUnitario AS ValorBase,
                ProdutoModelo AS ProdutoNome,
                ProdutoISBN
            FROM Vendas_Historia 
            WHERE 1=1 
            %s
            ORDER BY DataVenda DESC 
            LIMIT %d", 
            $where, 
            $limit
        );

        $query = DB::query(Database::SELECT, $sql);
        $vendas = $query->execute()->as_array();

        $result = [
            'success' => true,
            'vendas' => $vendas,
            'total' => count($vendas)
        ];

        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode($result));
    }
}