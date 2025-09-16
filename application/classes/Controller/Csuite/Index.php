<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Sales Manager Controller - Entry point for the leads8 application
 * Provides navigation menu to all available controllers and modules
 */
class Controller_Csuite_Index extends Controller_Website {

    public function before() {

        die('csuite');
        parent::before();
        
        if (!isset($_SESSION['MM_Userid']) || $_SESSION['MM_Nivel'] < 2) {
         //   header('Location: /leads8/');
         //   exit;
         die('not allowed to access csuite or no session');
        }
    }


    /**
     * Main dashboard action - displays sales manager interface with navigation menu
     */
    public function action_index()
    {
        // Check user authorization
        if (!isset($_SESSION['MM_Userid']) || $_SESSION['MM_Nivel'] < 1) {
            return $this->render('error', [
                'title' => 'Acesso Negado',
                'message' => 'Você não tem permissão para acessar o gerenciador de vendas.'
            ]);
        }

        // Prepare menu data with all available controllers
        $menuData = [
            'user' => [
                'name' => $_SESSION['MM_Username'] ?? 'Usuário',
                'level' => $_SESSION['MM_Nivel'] ?? 0,
                'id' => $_SESSION['MM_Userid'] ?? 0
            ],
            'modules' => [
                'leads' => [
                    'title' => 'Gerenciamento de Leads',
                    'icon' => 'fas fa-users',
                    'color' => 'blue',
                    'controllers' => [
                        ['name' => 'Listar Leads', 'url' => '/lead/index', 'icon' => 'fas fa-list'],
                        ['name' => 'Adicionar Lead', 'url' => '/lead/add', 'icon' => 'fas fa-plus'],
                        ['name' => 'Buscar Leads', 'url' => '/lead/search', 'icon' => 'fas fa-search'],
                        ['name' => 'Estatísticas', 'url' => '/lead/statistics', 'icon' => 'fas fa-chart-bar'],
                        ['name' => 'Histórico', 'url' => '/lead/history', 'icon' => 'fas fa-history'],
                        ['name' => 'Exportar', 'url' => '/lead/export', 'icon' => 'fas fa-download'],
                        ['name' => 'Importar', 'url' => '/lead/import', 'icon' => 'fas fa-upload']
                    ]
                ],
                'products' => [
                    'title' => 'Produtos',
                    'icon' => 'fas fa-box',
                    'color' => 'green',
                    'controllers' => [
                        ['name' => 'Vendas', 'url' => '/product/sales', 'icon' => 'fas fa-shopping-cart'],
                        ['name' => 'E-commerce', 'url' => '/product/ecommerce', 'icon' => 'fas fa-store'],
                        ['name' => 'Chegadas', 'url' => '/product/arrival', 'icon' => 'fas fa-truck'],
                        ['name' => 'Inspeção', 'url' => '/product/inspect', 'icon' => 'fas fa-search-plus']
                    ]
                ],
                'orders' => [
                    'title' => 'Pedidos',
                    'icon' => 'fas fa-file-invoice',
                    'color' => 'purple',
                    'controllers' => [
                        ['name' => 'Gerar Pedido', 'url' => '/order/generate', 'icon' => 'fas fa-plus-circle'],
                        ['name' => 'Checkout', 'url' => '/order/checkout', 'icon' => 'fas fa-credit-card'],
                        ['name' => 'Construir', 'url' => '/order/build', 'icon' => 'fas fa-hammer'],
                        ['name' => 'Finalizar', 'url' => '/order/done', 'icon' => 'fas fa-check-circle'],
                        ['name' => 'Imprimir', 'url' => '/order/print', 'icon' => 'fas fa-print']
                    ]
                ],
                'sales' => [
                    'title' => 'Vendas',
                    'icon' => 'fas fa-chart-line',
                    'color' => 'orange',
                    'controllers' => [
                        ['name' => 'Histórico de Vendas', 'url' => '/sales/history', 'icon' => 'fas fa-history']
                    ]
                ],
                'tickets' => [
                    'title' => 'Tickets',
                    'icon' => 'fas fa-ticket-alt',
                    'color' => 'red',
                    'controllers' => [
                        ['name' => 'Novo Ticket', 'url' => '/ticket/new', 'icon' => 'fas fa-plus'],
                        ['name' => 'Editar Ticket', 'url' => '/ticket/edit', 'icon' => 'fas fa-edit'],
                        ['name' => 'Finalizar Ticket', 'url' => '/ticket/finish', 'icon' => 'fas fa-check'],
                        ['name' => 'Gerar Ticket', 'url' => '/ticket/generate', 'icon' => 'fas fa-cog']
                    ]
                ],
                'admin' => [
                    'title' => 'Administração',
                    'icon' => 'fas fa-cogs',
                    'color' => 'gray',
                    'controllers' => [
                        ['name' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'fas fa-tachometer-alt'],
                        ['name' => 'Inventário', 'url' => '/inventory', 'icon' => 'fas fa-warehouse'],
                        ['name' => 'Variáveis', 'url' => '/vars', 'icon' => 'fas fa-sliders-h'],
                        ['name' => 'Editor SQL', 'url' => '/sqleditor', 'icon' => 'fas fa-database'],
                        ['name' => 'Visualizar SQL', 'url' => '/viewsql', 'icon' => 'fas fa-eye'],
                        ['name' => 'Editor de Views', 'url' => '/view/editor', 'icon' => 'fas fa-code']
                    ]
                ]
            ],
            'stats' => [
                'total_leads' => $this->getTotalLeads(),
                'pending_orders' => $this->getPendingOrders(),
                'monthly_sales' => $this->getMonthlySales(),
                'active_tickets' => $this->getActiveTickets()
            ]
        ];

        // Add admin-only modules for high-level users
        if ($_SESSION['MM_Nivel'] >= 5) {
            $menuData['modules']['admin']['controllers'][] = ['name' => 'Serviços', 'url' => '/service', 'icon' => 'fas fa-tools'];
        }

        return $this->render('sales_manager', $menuData);
    }

    /**
     * Get total number of leads
     */
    private function getTotalLeads()
    {
        try {
            $response = Request::factory('/lead/statistics')
                ->method('GET')
                ->execute()
                ->body();
            
            $data = json_decode($response, true);
            return $data['total_leads'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get number of pending orders
     */
    private function getPendingOrders()
    {
        try {
            // This would need to be implemented based on your order system
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get monthly sales data
     */
    private function getMonthlySales()
    {
        try {
            $response = Request::factory('/sales/history')
                ->method('GET')
                ->execute()
                ->body();
            
            $data = json_decode($response, true);
            return $data['monthly_total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get number of active tickets
     */
    private function getActiveTickets()
    {
        try {
            // This would need to be implemented based on your ticket system
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Quick access action for frequently used features
     */
    public function action_quick()
    {
        $action = $this->request->param('action');
        
        switch ($action) {
            case 'new_lead':
                return $this->redirect('/lead/add');
            case 'search':
                return $this->redirect('/lead/search');
            case 'stats':
                return $this->redirect('/lead/statistics');
            default:
                return $this->redirect('/');
        }
    }

    /**
     * API endpoint for dashboard widgets
     */
    public function action_api()
    {
        $endpoint = $this->request->param('endpoint');
        
        header('Content-Type: application/json');
        
        switch ($endpoint) {
            case 'stats':
                echo json_encode([
                    'total_leads' => $this->getTotalLeads(),
                    'pending_orders' => $this->getPendingOrders(),
                    'monthly_sales' => $this->getMonthlySales(),
                    'active_tickets' => $this->getActiveTickets()
                ]);
                break;
            default:
                echo json_encode(['error' => 'Endpoint not found']);
        }
        
        exit;
    }
}