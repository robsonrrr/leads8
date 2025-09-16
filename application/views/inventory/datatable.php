<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Inventário - DataTables</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .filters-container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        
        .filter-group {
            margin-bottom: 15px;
        }
        
        .filter-group:last-child {
            margin-bottom: 0;
        }
        
        .badge-stock {
            font-size: 0.75em;
            padding: 0.375rem 0.75rem;
        }
        
        .stock-value {
            font-weight: bold;
            color: #198754;
        }
        
        .stock-low {
            color: #fd7e14;
        }
        
        .stock-out {
            color: #dc3545;
        }
        
        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
        
        .btn-filter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 500;
        }
        
        .btn-filter:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }
        
        .btn-clear {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            color: white;
            font-weight: 500;
        }
        
        .btn-clear:hover {
            background: linear-gradient(135deg, #ee82f0 0%, #f3455a 100%);
            color: white;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        
        .abc-A { background-color: #d4edda; color: #155724; }
        .abc-B { background-color: #fff3cd; color: #856404; }
        .abc-C { background-color: #f8d7da; color: #721c24; }
        
        .rfm-High { background-color: #d1ecf1; color: #0c5460; }
        .rfm-Medium { background-color: #e2e3e5; color: #383d41; }
        .rfm-Low { background-color: #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-boxes me-2"></i>Relatório de Inventário</h2>
                    <div>
                        <button class="btn btn-outline-secondary" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-1"></i>Atualizar
                        </button>
                        <a href="/leads8/inventory" class="btn btn-outline-primary ms-2">
                            <i class="fas fa-chart-bar me-1"></i>Dashboard
                        </a>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="filters-container">
                    <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filtros</h5>
                    <form id="filtersForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="filter-group">
                                    <label for="brandFilter" class="form-label">Marca</label>
                                    <select class="form-select" id="brandFilter" name="brand_filter">
                                        <option value="">Todas as marcas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="filter-group">
                                    <label for="stockFilter" class="form-label">Status do Estoque</label>
                                    <select class="form-select" id="stockFilter" name="stock_filter">
                                        <option value="">Todos</option>
                                        <option value="with_stock">Com Estoque</option>
                                        <option value="without_stock">Sem Estoque</option>
                                        <option value="low_stock">Estoque Baixo (≤10)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="filter-group">
                                    <label for="abcFilter" class="form-label">Categoria ABC</label>
                                    <select class="form-select" id="abcFilter" name="abc_filter">
                                        <option value="">Todas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="filter-group">
                                    <label for="rfmFilter" class="form-label">Categoria RFM</label>
                                    <select class="form-select" id="rfmFilter" name="rfm_filter">
                                        <option value="">Todas</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-filter me-2" id="applyFilters">
                                    <i class="fas fa-search me-1"></i>Aplicar Filtros
                                </button>
                                <button type="button" class="btn btn-clear" id="clearFilters">
                                    <i class="fas fa-times me-1"></i>Limpar Filtros
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Tabela -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="inventoryTable" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Marca</th>
                                        <th>Produto</th>
                                        <th>Estoque</th>
                                        <th>Valor Estoque</th>
                                        <th>Preço FOB</th>
                                        <th>Vendas 30d</th>
                                        <th>Giro (meses)</th>
                                        <th>ABC</th>
                                        <th>RFM</th>
                                        <th>Última Venda</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <div class="mt-2">Carregando dados...</div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <script>
    $(document).ready(function() {
        console.log('Initializing Inventory DataTable...');
        
        // Initialize DataTable
        var table = $('#inventoryTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '/leads8/inventory/ajax',
                type: 'POST',
                data: function(d) {
                    // Add custom filters
                    d.brand_filter = $('#brandFilter').val();
                    d.stock_filter = $('#stockFilter').val();
                    d.abc_filter = $('#abcFilter').val();
                    d.rfm_filter = $('#rfmFilter').val();
                    
                    console.log('DataTables request data:', d);
                    return d;
                },
                beforeSend: function() {
                    $('#loadingOverlay').show();
                },
                complete: function() {
                    $('#loadingOverlay').hide();
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables AJAX error:', error, thrown);
                    console.error('Response:', xhr.responseText);
                    $('#loadingOverlay').hide();
                    alert('Erro ao carregar dados: ' + error);
                }
            },
            columns: [
                { data: 'model_code', name: 'model_code' },
                { data: 'brand_name', name: 'brand_name' },
                { data: 'product_name', name: 'product_name' },
                { 
                    data: 'total_stock_formatted', 
                    name: 'total_stock',
                    className: 'text-end'
                },
                { 
                    data: 'total_stock_value_formatted', 
                    name: 'total_stock_value',
                    className: 'text-end stock-value'
                },
                { 
                    data: 'fob_price_formatted', 
                    name: 'fob_price',
                    className: 'text-end'
                },
                { 
                    data: 'sales_last_30_days_formatted', 
                    name: 'sales_last_30_days',
                    className: 'text-end'
                },
                { 
                    data: 'stock_life_months_formatted', 
                    name: 'stock_life_months',
                    className: 'text-center'
                },
                { 
                    data: 'abc_category', 
                    name: 'abc_category',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (data) {
                            return '<span class="badge abc-' + data + '">' + data + '</span>';
                        }
                        return '<span class="badge bg-secondary">N/A</span>';
                    }
                },
                { 
                    data: 'rfm_category', 
                    name: 'rfm_category',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (data) {
                            return '<span class="badge rfm-' + data + '">' + data + '</span>';
                        }
                        return '<span class="badge bg-secondary">N/A</span>';
                    }
                },
                { 
                    data: 'last_sale_date_formatted', 
                    name: 'last_sale_date',
                    className: 'text-center'
                },
                { 
                    data: 'stock_status', 
                    name: 'stock_status',
                    className: 'text-center',
                    render: function(data, type, row) {
                        var badgeClass = 'bg-secondary';
                        if (row.stock_status_class) {
                            switch(row.stock_status_class) {
                                case 'badge-success':
                                    badgeClass = 'bg-success';
                                    break;
                                case 'badge-warning':
                                    badgeClass = 'bg-warning text-dark';
                                    break;
                                case 'badge-danger':
                                    badgeClass = 'bg-danger';
                                    break;
                            }
                        }
                        return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                    }
                }
            ],
            order: [[4, 'desc']], // Order by stock value descending
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm'
                },
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-info btn-sm'
                }
            ]
        });
        
        // Load filter options
        loadFilterOptions();
        
        // Filter event handlers
        $('#applyFilters').click(function() {
            console.log('Applying filters...');
            table.ajax.reload();
        });
        
        $('#clearFilters').click(function() {
            console.log('Clearing filters...');
            $('#filtersForm')[0].reset();
            table.ajax.reload();
        });
        
        // Auto-apply filters on change
        $('#brandFilter, #stockFilter, #abcFilter, #rfmFilter').change(function() {
            table.ajax.reload();
        });
        
        function loadFilterOptions() {
            console.log('Loading filter options...');
            
            // Load brands
            $.post('/leads8/inventory/ajax', { action: 'get_brands' })
                .done(function(response) {
                    console.log('Brands loaded:', response);
                    if (response.data) {
                        var brandSelect = $('#brandFilter');
                        brandSelect.find('option:not(:first)').remove();
                        $.each(response.data, function(index, brand) {
                            brandSelect.append('<option value="' + brand + '">' + brand + '</option>');
                        });
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Error loading brands:', error);
                });
            
            // Load ABC categories
            $.post('/leads8/inventory/ajax', { action: 'get_abc' })
                .done(function(response) {
                    console.log('ABC categories loaded:', response);
                    if (response.data) {
                        var abcSelect = $('#abcFilter');
                        abcSelect.find('option:not(:first)').remove();
                        $.each(response.data, function(index, abc) {
                            abcSelect.append('<option value="' + abc + '">' + abc + '</option>');
                        });
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Error loading ABC categories:', error);
                });
            
            // Load RFM categories
            $.post('/leads8/inventory/ajax', { action: 'get_rfm' })
                .done(function(response) {
                    console.log('RFM categories loaded:', response);
                    if (response.data) {
                        var rfmSelect = $('#rfmFilter');
                        rfmSelect.find('option:not(:first)').remove();
                        $.each(response.data, function(index, rfm) {
                            rfmSelect.append('<option value="' + rfm + '">' + rfm + '</option>');
                        });
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Error loading RFM categories:', error);
                });
        }
        
        console.log('Inventory DataTable initialized successfully');
    });
    </script>
</body>
</html>