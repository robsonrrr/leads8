<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Clientes</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/leads8/assets/css/tailwind.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="assets/css/dataTables.responsive.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/colreorder/1.7.0/css/colReorder.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.dataTables.min.css">
    <!-- Mobile Improvements CSS -->
    <link rel="stylesheet" href="css/buttons-links-improvements.css">
    <!-- Notifications System CSS -->
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="assets/css/datatable-performance.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/colreorder/1.7.0/js/dataTables.colReorder.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    <!-- Notifications System JS -->
    <script src="assets/js/notifications.js"></script>
    <script src="assets/js/datatable-performance.js"></script>
    <script src="assets/js/dark-mode.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="w-[95vw] mx-auto px-4 py-8 mobile-container">
        <div class="bg-white rounded-lg shadow-lg p-6 mobile-md-p">
            <h1 class="text-3xl font-bold text-gray-800 mb-6 mobile-text-xl">Dashboard de Clientes</h1>
            
            <!-- KPI Dashboard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 mobile-grid mobile-space-y">
                <!-- Total Clientes -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition-transform duration-200 mobile-md-p touch-target">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium uppercase tracking-wide mobile-text-sm">Total Clientes</p>
                            <p class="text-3xl font-bold mt-2 mobile-text-lg" id="kpi-total-clientes" data-sales="monthly"><?= count($customers_data) ?></p>
                            <p class="text-blue-100 text-xs mt-1 mobile-text-sm">Ativos no sistema</p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-30 p-3 rounded-full">
                            <svg class="w-8 h-8 mobile-icon-lg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Vendas do Mês -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition-transform duration-200 mobile-md-p touch-target">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium uppercase tracking-wide mobile-text-sm">Vendas do Mês</p>
                            <p class="text-3xl font-bold mt-2 mobile-text-lg" id="kpi-vendas-mes" data-sales="daily">R$ 0,00</p>
                            <p class="text-green-100 text-xs mt-1 mobile-text-sm" id="kpi-vendas-mes-change">+0% vs mês anterior</p>
                        </div>
                        <div class="bg-green-400 bg-opacity-30 p-3 rounded-full">
                            <svg class="w-8 h-8 mobile-icon-lg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1-1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Vendas do Ano -->
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition-transform duration-200 mobile-md-p touch-target">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium uppercase tracking-wide mobile-text-sm">Vendas do Ano</p>
                            <p class="text-3xl font-bold mt-2 mobile-text-lg" id="kpi-vendas-ano" data-sales="yearly">R$ 0,00</p>
                            <p class="text-purple-100 text-xs mt-1 mobile-text-sm" id="kpi-vendas-ano-change">+0% vs ano anterior</p>
                        </div>
                        <div class="bg-purple-400 bg-opacity-30 p-3 rounded-full">
                            <svg class="w-8 h-8 mobile-icon-lg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Ticket Médio -->
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition-transform duration-200 mobile-md-p touch-target">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium uppercase tracking-wide mobile-text-sm">Ticket Médio</p>
                            <p class="text-3xl font-bold mt-2 mobile-text-lg" id="kpi-ticket-medio">R$ 0,00</p>
                            <p class="text-orange-100 text-xs mt-1 mobile-text-sm" id="kpi-ticket-medio-change">+0% vs período anterior</p>
                        </div>
                        <div class="bg-orange-400 bg-opacity-30 p-3 rounded-full">
                            <svg class="w-8 h-8 mobile-icon-lg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>


            

            
            <!-- Filtros Customizados -->
             <div class="mb-4 bg-gray-50 p-4 rounded-lg hidden">
                 <h3 class="text-sm font-medium text-gray-700 mb-3">Filtros avançados</h3>
                 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                     <div>
                         <label class="block text-xs font-medium text-gray-700 mb-1">estado</label>
                         <select id="filter-estado" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                             <option value="">Todos os estados</option>
                         </select>
                     </div>
                     <div>
                         <label class="block text-xs font-medium text-gray-700 mb-1">Vendedor</label>
                         <select id="filter-vendedor" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                             <option value="">Todos os vendedores</option>
                         </select>
                     </div>
                     <div>
                         <label class="block text-xs font-medium text-gray-700 mb-1">RFM</label>
                         <select id="filter-rfm" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                             <option value="">Todos os RFM</option>
                         </select>
                     </div>

                 </div>
                 <div class="mt-3 flex gap-2">
                     <button id="apply-filters" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-xs">
                         Aplicar filtros
                     </button>
                     <button id="clear-filters" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-xs">
                         Limpar filtros
                     </button>
                 </div>
             </div>

          

             <!-- Container for Locations (Cities, Neighborhoods and States) -->
             <div class="mb-4">

                 <!-- Grid de Localidades (Cidades, Bairros e Estados) -->
                 <div class="bg-slate-50 p-4 rounded-lg">
                     <h3 class="text-sm font-medium text-slate-700 mb-3">Filtros por Localidade</h3>
                     <div class="flex flex-col lg:flex-row gap-4">

                         <!-- Filtros de Bairros (Left side) -->
                         <div class="flex-1 bg-purple-50 p-3 rounded">
                             <h4 class="text-xs font-medium text-purple-700 mb-2">Bairros</h4>
                             <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
                                 <button id="filter-bras" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-bras-text">Brás</span>
                                     <span id="filter-bras-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-rua-sao-caetano" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-rua-sao-caetano-text">Rua São Caetano</span>
                                     <span id="filter-rua-sao-caetano-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-bom-retiro" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-bom-retiro-text">Bom Retiro</span>
                                     <span id="filter-bom-retiro-icon" class="ml-1 hidden">✓</span>
                                 </button>
                             </div>
                         </div>
                     
                         <!-- Filtros de Cidades (Center) -->
                         <div class="flex-1 bg-blue-50 p-3 rounded">
                             <h4 class="text-xs font-medium text-blue-700 mb-2">Cidades</h4>
                             <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
                                 <button id="filter-sao-paulo" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-sao-paulo-text">São Paulo</span>
                                     <span id="filter-sao-paulo-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-sao-paulo-center" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-sao-paulo-center-text">SP Center</span>
                                     <span id="filter-sao-paulo-center-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-sao-paulo-side" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-sao-paulo-side-text">SP Side</span>
                                     <span id="filter-sao-paulo-side-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-blumenau" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-blumenau-text">Blumenau</span>
                                     <span id="filter-blumenau-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-curitiba" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-curitiba-text">Curitiba</span>
                                     <span id="filter-curitiba-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-maringa" class="bg-pink-500 hover:bg-pink-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-maringa-text">Maringá</span>
                                     <span id="filter-maringa-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-bauru" class="bg-cyan-500 hover:bg-cyan-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-bauru-text">Bauru</span>
                                     <span id="filter-bauru-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-birigui" class="bg-lime-500 hover:bg-lime-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-birigui-text">Birigui</span>
                                     <span id="filter-birigui-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-belo-horizonte" class="bg-amber-500 hover:bg-amber-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-belo-horizonte-text">Belo Horizonte</span>
                                     <span id="filter-belo-horizonte-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-franca" class="bg-emerald-500 hover:bg-emerald-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-franca-text">Franca</span>
                                     <span id="filter-franca-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-fortaleza" class="bg-violet-500 hover:bg-violet-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-fortaleza-text">Fortaleza</span>
                                     <span id="filter-fortaleza-icon" class="ml-1 hidden">✓</span>
                                 </button>
                                 <button id="filter-juiz-de-fora" class="bg-rose-500 hover:bg-rose-700 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                     <span id="filter-juiz-de-fora-text">Juiz de Fora</span>
                                     <span id="filter-juiz-de-fora-icon" class="ml-1 hidden">✓</span>
                                 </button>
                             </div>
                         </div>

                         <!-- Filtros de Estados (Right side) -->             
                         <div class="flex-1 bg-green-50 p-3 rounded">
                             <h4 class="text-xs font-medium text-green-700 mb-2">Estados</h4>
                             <div class="space-y-2">
                         <!-- Região Norte -->
                         <div class="flex flex-wrap items-start gap-1">
                             <span class="text-xs font-semibold text-green-700 bg-green-100 px-2 py-1 rounded flex-shrink-0 w-20">Norte:</span>
                             <button id="filter-acre" class="bg-green-600 hover:bg-green-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-acre-text">AC</span>
                                 <span id="filter-acre-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-amapa" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-amapa-text">AP</span>
                                 <span id="filter-amapa-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-amazonas" class="bg-indigo-600 hover:bg-indigo-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-amazonas-text">AM</span>
                                 <span id="filter-amazonas-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-para" class="bg-purple-600 hover:bg-purple-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-para-text">PA</span>
                                 <span id="filter-para-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-rondonia" class="bg-pink-600 hover:bg-pink-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-rondonia-text">RO</span>
                                 <span id="filter-rondonia-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-roraima" class="bg-red-600 hover:bg-red-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-roraima-text">RR</span>
                                 <span id="filter-roraima-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-tocantins" class="bg-orange-600 hover:bg-orange-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-tocantins-text">TO</span>
                                 <span id="filter-tocantins-icon" class="ml-1 hidden">✓</span>
                             </button>
                         </div>
                         
                         <!-- Região Nordeste -->
                         <div class="flex flex-wrap items-start gap-1">
                             <span class="text-xs font-semibold text-yellow-700 bg-yellow-100 px-2 py-1 rounded flex-shrink-0 w-20">Nordeste:</span>
                             <button id="filter-alagoas" class="bg-yellow-600 hover:bg-yellow-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-alagoas-text">AL</span>
                                 <span id="filter-alagoas-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-bahia" class="bg-amber-600 hover:bg-amber-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-bahia-text">BA</span>
                                 <span id="filter-bahia-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-ceara" class="bg-lime-600 hover:bg-lime-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-ceara-text">CE</span>
                                 <span id="filter-ceara-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-maranhao" class="bg-emerald-600 hover:bg-emerald-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-maranhao-text">MA</span>
                                 <span id="filter-maranhao-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-paraiba" class="bg-teal-600 hover:bg-teal-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-paraiba-text">PB</span>
                                 <span id="filter-paraiba-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-pernambuco" class="bg-cyan-600 hover:bg-cyan-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-pernambuco-text">PE</span>
                                 <span id="filter-pernambuco-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-piaui" class="bg-sky-600 hover:bg-sky-800 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-piaui-text">PI</span>
                                 <span id="filter-piaui-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-rio-grande-do-norte" class="bg-blue-700 hover:bg-blue-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-rio-grande-do-norte-text">RN</span>
                                 <span id="filter-rio-grande-do-norte-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-sergipe" class="bg-indigo-700 hover:bg-indigo-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-sergipe-text">SE</span>
                                 <span id="filter-sergipe-icon" class="ml-1 hidden">✓</span>
                             </button>
                         </div>
                         
                         <!-- Região Centro-Oeste -->
                         <div class="flex flex-wrap items-start gap-1">
                             <span class="text-xs font-semibold text-purple-700 bg-purple-100 px-2 py-1 rounded flex-shrink-0 w-20">Centro-Oeste:</span>
                             <button id="filter-distrito-federal" class="bg-purple-700 hover:bg-purple-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-distrito-federal-text">DF</span>
                                 <span id="filter-distrito-federal-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-goias" class="bg-pink-700 hover:bg-pink-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-goias-text">GO</span>
                                 <span id="filter-goias-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-mato-grosso" class="bg-red-700 hover:bg-red-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-mato-grosso-text">MT</span>
                                 <span id="filter-mato-grosso-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-mato-grosso-do-sul" class="bg-orange-700 hover:bg-orange-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-mato-grosso-do-sul-text">MS</span>
                                 <span id="filter-mato-grosso-do-sul-icon" class="ml-1 hidden">✓</span>
                             </button>
                         </div>
                         
                         <!-- Região Sudeste -->
                         <div class="flex flex-wrap items-start gap-1">
                             <span class="text-xs font-semibold text-blue-700 bg-blue-100 px-2 py-1 rounded flex-shrink-0 w-20">Sudeste:</span>
                             <button id="filter-espirito-santo" class="bg-yellow-700 hover:bg-yellow-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-espirito-santo-text">ES</span>
                                 <span id="filter-espirito-santo-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-minas-gerais" class="bg-amber-700 hover:bg-amber-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-minas-gerais-text">MG</span>
                                 <span id="filter-minas-gerais-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-rio-de-janeiro" class="bg-lime-700 hover:bg-lime-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-rio-de-janeiro-text">RJ</span>
                                 <span id="filter-rio-de-janeiro-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-sao-paulo-estado" class="bg-emerald-700 hover:bg-emerald-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-sao-paulo-estado-text">SP</span>
                                 <span id="filter-sao-paulo-estado-icon" class="ml-1 hidden">✓</span>
                             </button>
                         </div>
                         
                         <!-- Região Sul -->
                         <div class="flex flex-wrap items-start gap-1">
                             <span class="text-xs font-semibold text-red-700 bg-red-100 px-2 py-1 rounded flex-shrink-0 w-20">Sul:</span>
                             <button id="filter-parana" class="bg-teal-700 hover:bg-teal-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-parana-text">PR</span>
                                 <span id="filter-parana-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-rio-grande-do-sul" class="bg-cyan-700 hover:bg-cyan-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-rio-grande-do-sul-text">RS</span>
                                 <span id="filter-rio-grande-do-sul-icon" class="ml-1 hidden">✓</span>
                             </button>
                             <button id="filter-santa-catarina" class="bg-sky-700 hover:bg-sky-900 text-white font-bold py-2 px-3 rounded text-xs transition-all duration-200">
                                 <span id="filter-santa-catarina-text">SC</span>
                                 <span id="filter-santa-catarina-icon" class="ml-1 hidden">✓</span>
                             </button>
                             </div>
                         </div>

                     </div>
                 </div>
             </div>


             <!-- Grid de Filtros de Vendas e Segmento -->
             <div class="mb-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                 <!-- Filtros de Vendas -->
                 <div class="bg-gray-50 p-4 rounded-lg mobile-md-p">
                     <h3 class="text-sm font-medium text-gray-700 mb-3 mobile-text-sm">Filtros de Vendas</h3>
                     <div class="flex flex-wrap gap-2 mobile-space-y">
                         <button id="filter-ultima-compra" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs">
                             <span id="filter-ultima-compra-text">Última Compra</span>
                             <span id="filter-ultima-compra-icon" class="ml-1 hidden">✓</span>
                         </button>
                         <button id="filter-vendas-mes-zero" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs">
                             <span id="filter-vendas-mes-zero-text">Vendas Mês</span>
                             <span id="filter-vendas-mes-zero-icon" class="ml-1 hidden">✓</span>
                         </button>
                         <button id="filter-vendas-ano-zero" class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs">
                             <span id="filter-vendas-ano-zero-text">Vendas Ano</span>
                             <span id="filter-vendas-ano-zero-icon" class="ml-1 hidden">✓</span>
                         </button>
                         <button id="filter-vendas-ano-passado-zero" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs">
                             <span id="filter-vendas-ano-passado-zero-text">Vendas Ano Passado</span>
                             <span id="filter-vendas-ano-passado-zero-icon" class="ml-1 hidden">✓</span>
                         </button>
                     </div>
                 </div>

                 <!-- Filtros de Segmento -->
                 <div class="bg-gray-50 p-4 rounded-lg mobile-md-p">
                     <h3 class="text-sm font-medium text-gray-700 mb-3 mobile-text-sm">Filtros de Segmento</h3>
                     <div class="flex flex-wrap gap-2 mobile-space-y">
                         <button id="filter-segmento-machines" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs">
                             <span id="filter-segmento-machines-text">🏭 Machines</span>
                             <span id="filter-segmento-machines-icon" class="ml-1 hidden">✓</span>
                         </button>
                         <button id="filter-segmento-bearings" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs">
                             <span id="filter-segmento-bearings-text">⚙️ Bearings</span>
                             <span id="filter-segmento-bearings-icon" class="ml-1 hidden">✓</span>
                         </button>
                         <button id="filter-segmento-auto" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs">
                             <span id="filter-segmento-auto-text">🚗 Auto</span>
                             <span id="filter-segmento-auto-icon" class="ml-1 hidden">✓</span>
                         </button>
                         <button id="filter-segmento-parts" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs">
                             <span id="filter-segmento-parts-text">🔧 Parts</span>
                             <span id="filter-segmento-parts-icon" class="ml-1 hidden">✓</span>
                         </button>
                         <button id="filter-segmento-faucets" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs">
                             <span id="filter-segmento-faucets-text">🚿 Faucets</span>
                             <span id="filter-segmento-faucets-icon" class="ml-1 hidden">✓</span>
                         </button>
                     </div>
                 </div>
             </div>

             <!-- Grid de Filtros de Vendedores -->
             <div class="mb-4 bg-gray-50 p-4 rounded-lg mobile-md-p">
                 <h3 class="text-sm font-medium text-gray-700 mb-3 mobile-text-sm">Filtros de Vendedores</h3>
                 <div class="flex flex-wrap gap-2 mobile-space-y">
                     <button id="filter-vendedor-regiane" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs" data-vendedor-id="107">
                         <span id="filter-vendedor-regiane-text">👩‍💼 Regiane</span>
                         <span id="filter-vendedor-regiane-icon" class="ml-1 hidden">✓</span>
                     </button>
                     <button id="filter-vendedor-rosana" class="bg-pink-500 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs" data-vendedor-id="131">
                         <span id="filter-vendedor-rosana-text">👩‍💼 Rosana</span>
                         <span id="filter-vendedor-rosana-icon" class="ml-1 hidden">✓</span>
                     </button>
                     <button id="filter-vendedor-edilene" class="bg-emerald-500 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs" data-vendedor-id="32">
                         <span id="filter-vendedor-edilene-text">👩‍💼 Edilene</span>
                         <span id="filter-vendedor-edilene-icon" class="ml-1 hidden">✓</span>
                     </button>
                     <button id="filter-vendedor-debora" class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs" data-vendedor-id="89">
                         <span id="filter-vendedor-debora-text">👩‍💼 Debora</span>
                         <span id="filter-vendedor-debora-icon" class="ml-1 hidden">✓</span>
                     </button>
                     <button id="filter-vendedor-miriam" class="bg-cyan-500 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs" data-vendedor-id="117">
                         <span id="filter-vendedor-miriam-text">👩‍💼 Miriam</span>
                         <span id="filter-vendedor-miriam-icon" class="ml-1 hidden">✓</span>
                     </button>
                     <button id="filter-vendedor-andrea" class="bg-violet-500 hover:bg-violet-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs" data-vendedor-id="218">
                         <span id="filter-vendedor-andrea-text">👩‍💼 Andrea</span>
                         <span id="filter-vendedor-andrea-icon" class="ml-1 hidden">✓</span>
                     </button>
                     <button id="filter-vendedor-cristiane" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs" data-vendedor-id="197">
                         <span id="filter-vendedor-cristiane-text">👩‍💼 Cristiane</span>
                         <span id="filter-vendedor-cristiane-icon" class="ml-1 hidden">✓</span>
                     </button>
                     <button id="filter-vendedor-luana" class="bg-rose-500 hover:bg-rose-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs" data-vendedor-id="1030">
                         <span id="filter-vendedor-luana-text">👩‍💼 Luana</span>
                         <span id="filter-vendedor-luana-icon" class="ml-1 hidden">✓</span>
                     </button>
                     <button id="filter-vendedor-revisar" class="bg-slate-500 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded text-xs transition-all duration-200 touch-target mobile-text-xs" data-vendedor-id="8">
                         <span id="filter-vendedor-revisar-text">🔍 Revisar</span>
                         <span id="filter-vendedor-revisar-icon" class="ml-1 hidden">✓</span>
                     </button>
                 </div>
             </div>

             <div class="overflow-x-auto shadow-lg rounded-lg w-full mobile-md-p">
                 <table id="customersTable" class="w-full bg-white border border-gray-200 display responsive nowrap mobile-table">
                     <thead class="bg-gray-50">
                         <tr>
                             <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-tight border-b">#</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-tight border-b">Nome</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-tight border-b">Cidade</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-tight border-b">Estado</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-tight border-b">Bairro</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-tight border-b">Cliente Endereço</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-tight border-b">Vendedor</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-tight border-b">Segmento</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-tight border-b">RFM</th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-tight border-b">Vendas Mês</th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-tight border-b">Vendas Ano</th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-tight border-b">Vendas Ano Passado</th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-tight border-b">Total Vendas</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-tight border-b">Última Compra</th>
                         </tr>
                     </thead>
                     <tbody>
                         <!-- DataTables Will Populate This Via AJAX -->
                     </tbody>
                     <tfoot class="bg-gray-100">
                         <tr>
                             <th class="px-2 py-2 text-center text-xs font-medium text-gray-700 uppercase tracking-tight border-t">#</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-tight border-t">Total:</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-tight border-t" id="total-vendas-mes">0</th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-tight border-t" id="total-vendas-ano">0</th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-tight border-t" id="total-vendas-ano-passado">0</th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-tight border-t" id="total-vendas-total">0</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-tight border-t"></th>
                         </tr>
                         <tr>
                             <th class="px-2 py-2 text-center text-xs font-medium text-gray-600 uppercase tracking-tight border-t">#</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-tight border-t">Média:</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-600 uppercase tracking-tight border-t"></th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-600 uppercase tracking-tight border-t" id="avg-vendas-mes">0</th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-600 uppercase tracking-tight border-t" id="avg-vendas-ano">0</th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-600 uppercase tracking-tight border-t" id="avg-vendas-ano-passado">0</th>
                             <th class="px-2 py-2 text-right text-xs font-medium text-gray-600 uppercase tracking-tight border-t" id="avg-vendas-total">0</th>
                             <th class="px-2 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-tight border-t"></th>
                         </tr>
                     </tfoot>
                 </table>
             </div>
        </div>
    </div>

<script>
 $(document).ready(function() {
     // Function to save column visibility state
     function saveColumnVisibility() {
         var visibleColumns = [];
         table.columns().every(function(index) {
             if (this.visible()) {
                 visibleColumns.push(index);
             }
         });
         localStorage.setItem('customerTableColumns', JSON.stringify(visibleColumns));
     }

     // Function to restore column visibility state
     function restoreColumnVisibility() {
         var savedColumns = localStorage.getItem('customerTableColumns');
         if (savedColumns) {
             var visibleColumns = JSON.parse(savedColumns);
             table.columns().every(function(index) {
                 var shouldBeVisible = visibleColumns.includes(index);
                 this.visible(shouldBeVisible, false);
             });
             table.columns.adjust().draw(false);
         }
     }

     var table = $('#customersTable').DataTable({
         processing: true,
         serverSide: true,
         deferRender: true,
         scroller: {
             loadingIndicator: true,
             displayBuffer: 10
         },
         scrollY: '60vh',
         scrollCollapse: true,
         ajax: {
             url: '/leads8/customer/ajax',
             type: 'POST',
             cache: true,
             data: function(d) {
                 // Add Custom Filter Parameters
                 d.filter_estado = $('#filter-estado').val();
                 d.filter_vendedor = $('#filter-vendedor').val();
                 d.filter_rfm = $('#filter-rfm').val();
                 // Vendedor Filters
                 d.filter_vendedor_regiane = $('#filter-vendedor-regiane').hasClass('active') ? 'vendedor_regiane' : '';
                 d.filter_vendedor_rosana = $('#filter-vendedor-rosana').hasClass('active') ? 'vendedor_rosana' : '';
                 d.filter_vendedor_edilene = $('#filter-vendedor-edilene').hasClass('active') ? 'vendedor_edilene' : '';
                 d.filter_vendedor_debora = $('#filter-vendedor-debora').hasClass('active') ? 'vendedor_debora' : '';
                 d.filter_vendedor_miriam = $('#filter-vendedor-miriam').hasClass('active') ? 'vendedor_miriam' : '';
                 d.filter_vendedor_andrea = $('#filter-vendedor-andrea').hasClass('active') ? 'vendedor_andrea' : '';
                 d.filter_vendedor_cristiane = $('#filter-vendedor-cristiane').hasClass('active') ? 'vendedor_cristiane' : '';
                 d.filter_vendedor_luana = $('#filter-vendedor-luana').hasClass('active') ? 'vendedor_luana' : '';
                 d.filter_vendedor_revisar = $('#filter-vendedor-revisar').hasClass('active') ? 'vendedor_revisar' : '';
                 var ultimaCompraState = $('#filter-ultima-compra').data('state') || 'inactive';
                 d.filter_ultima_compra = ultimaCompraState === '30-60' ? '30-60' : (ultimaCompraState === '60-90' ? '60-90' : (ultimaCompraState === '90-180' ? '90-180' : (ultimaCompraState === '180-365' ? '180-365' : (ultimaCompraState === '>365' ? '>365' : ''))));
                 d.filter_rua_sao_caetano = $('#filter-rua-sao-caetano').hasClass('active') ? 'sao_caetano' : '';
                 d.filter_bom_retiro = $('#filter-bom-retiro').hasClass('active') ? 'bom_retiro' : '';
                 d.filter_bras = $('#filter-bras').hasClass('active') ? 'bras' : '';
                 var vendasMesState = $('#filter-vendas-mes-zero').data('state') || 'inactive';
                 d.filter_vendas_mes_zero = vendasMesState === 'zero' ? 'mes_zero' : (vendasMesState === 'positive' ? 'mes_positive' : '');
                 var vendasAnoState = $('#filter-vendas-ano-zero').data('state') || 'inactive';
                 d.filter_vendas_ano_zero = vendasAnoState === 'zero' ? 'ano_zero' : (vendasAnoState === 'positive' ? 'ano_positive' : '');
                 var vendasAnoPassadoState = $('#filter-vendas-ano-passado-zero').data('state') || 'inactive';
                 d.filter_vendas_ano_passado_zero = vendasAnoPassadoState === 'zero' ? 'ano_passado_zero' : (vendasAnoPassadoState === 'positive' ? 'ano_passado_positive' : '');
                 // Segmento Filters
                 d.filter_segmento_machines = $('#filter-segmento-machines').hasClass('active') ? 'machines' : '';
                 d.filter_segmento_bearings = $('#filter-segmento-bearings').hasClass('active') ? 'bearings' : '';
                 d.filter_segmento_auto = $('#filter-segmento-auto').hasClass('active') ? 'auto' : '';
                 d.filter_segmento_parts = $('#filter-segmento-parts').hasClass('active') ? 'parts' : '';
                 d.filter_segmento_faucets = $('#filter-segmento-faucets').hasClass('active') ? 'faucets' : '';
                 // City Filters
                 d.filter_sao_paulo = $('#filter-sao-paulo').hasClass('active') ? 'sao_paulo' : '';
                 d.filter_sao_paulo_center = $('#filter-sao-paulo-center').hasClass('active') ? 'sao_paulo_center' : '';
                 d.filter_sao_paulo_side = $('#filter-sao-paulo-side').hasClass('active') ? 'sao_paulo_side' : '';
                 d.filter_blumenau = $('#filter-blumenau').hasClass('active') ? 'blumenau' : '';
                 d.filter_curitiba = $('#filter-curitiba').hasClass('active') ? 'curitiba' : '';
                 d.filter_maringa = $('#filter-maringa').hasClass('active') ? 'maringa' : '';
                 d.filter_bauru = $('#filter-bauru').hasClass('active') ? 'bauru' : '';
                 d.filter_birigui = $('#filter-birigui').hasClass('active') ? 'birigui' : '';
                 d.filter_belo_horizonte = $('#filter-belo-horizonte').hasClass('active') ? 'belo_horizonte' : '';
                 d.filter_franca = $('#filter-franca').hasClass('active') ? 'franca' : '';
                 d.filter_fortaleza = $('#filter-fortaleza').hasClass('active') ? 'fortaleza' : '';
                 d.filter_juiz_de_fora = $('#filter-juiz-de-fora').hasClass('active') ? 'juiz_de_fora' : '';
                 // State Filters - Norte
                 d.filter_acre = $('#filter-acre').hasClass('active') ? 'acre' : '';
                 d.filter_amapa = $('#filter-amapa').hasClass('active') ? 'amapa' : '';
                 d.filter_amazonas = $('#filter-amazonas').hasClass('active') ? 'amazonas' : '';
                 d.filter_para = $('#filter-para').hasClass('active') ? 'para' : '';
                 d.filter_rondonia = $('#filter-rondonia').hasClass('active') ? 'rondonia' : '';
                 d.filter_roraima = $('#filter-roraima').hasClass('active') ? 'roraima' : '';
                 d.filter_tocantins = $('#filter-tocantins').hasClass('active') ? 'tocantins' : '';
                 // State Filters - Nordeste
                 d.filter_alagoas = $('#filter-alagoas').hasClass('active') ? 'alagoas' : '';
                 d.filter_bahia = $('#filter-bahia').hasClass('active') ? 'bahia' : '';
                 d.filter_ceara = $('#filter-ceara').hasClass('active') ? 'ceara' : '';
                 d.filter_maranhao = $('#filter-maranhao').hasClass('active') ? 'maranhao' : '';
                 d.filter_paraiba = $('#filter-paraiba').hasClass('active') ? 'paraiba' : '';
                 d.filter_pernambuco = $('#filter-pernambuco').hasClass('active') ? 'pernambuco' : '';
                 d.filter_piaui = $('#filter-piaui').hasClass('active') ? 'piaui' : '';
                 d.filter_rio_grande_do_norte = $('#filter-rio-grande-do-norte').hasClass('active') ? 'rio_grande_do_norte' : '';
                 d.filter_sergipe = $('#filter-sergipe').hasClass('active') ? 'sergipe' : '';
                 // State Filters - Centro-Oeste
                 d.filter_distrito_federal = $('#filter-distrito-federal').hasClass('active') ? 'distrito_federal' : '';
                 d.filter_goias = $('#filter-goias').hasClass('active') ? 'goias' : '';
                 d.filter_mato_grosso = $('#filter-mato-grosso').hasClass('active') ? 'mato_grosso' : '';
                 d.filter_mato_grosso_do_sul = $('#filter-mato-grosso-do-sul').hasClass('active') ? 'mato_grosso_do_sul' : '';
                 // State Filters - Sudeste
                 d.filter_espirito_santo = $('#filter-espirito-santo').hasClass('active') ? 'espirito_santo' : '';
                 d.filter_minas_gerais = $('#filter-minas-gerais').hasClass('active') ? 'minas_gerais' : '';
                 d.filter_rio_de_janeiro = $('#filter-rio-de-janeiro').hasClass('active') ? 'rio_de_janeiro' : '';
                 d.filter_sao_paulo_estado = $('#filter-sao-paulo-estado').hasClass('active') ? 'sao_paulo_estado' : '';
                 // State Filters - Sul
                 d.filter_parana = $('#filter-parana').hasClass('active') ? 'parana' : '';
                 d.filter_rio_grande_do_sul = $('#filter-rio-grande-do-sul').hasClass('active') ? 'rio_grande_do_sul' : '';
                 d.filter_santa_catarina = $('#filter-santa-catarina').hasClass('active') ? 'santa_catarina' : '';
             },
             error: function(xhr, error, thrown) {
                 console.log('AJAX Error:', error);
                 console.log('Status:', xhr.status);
                 console.log('Response:', xhr.responseText);
                 console.log('Thrown:', thrown);
             }
         },
         responsive: true,
         pageLength: 50,
         lengthMenu: [[25, 50, 100, 200], [25, 50, 100, 200]],
         stateSave: true,
         stateDuration: 60 * 60 * 24,
         search: {
             smart: false,
             regex: false,
             caseInsensitive: true
         },
         searchDelay: 500,
         language: {
             "sProcessing": "Processando...",
             "sLengthMenu": "Mostrar _MENU_ registros",
             "sZeroRecords": "Não foram encontrados resultados",
             "sInfo": "Mostrando registros de _START_ a _END_ de um total de _TOTAL_ registros",
             "sInfoEmpty": "Mostrando registros de 0 a 0 de um total de 0 registros",
             "sInfoFiltered": "(Filtrado de um total de _MAX_ registros)",
             "sInfoPostFix": "",
             "sSearch": "Buscar:",
             "sUrl": "",
             "sInfoThousands": ".",
             "sLoadingRecords": "Carregando...",
             "oPaginate": {
                 "sFirst": "Primeiro",
                 "sLast": "Último",
                 "sNext": "Seguinte",
                 "sPrevious": "Anterior"
             },
             "oAria": {
                 "sSortAscending": ": Ordenar colunas de forma ascendente",
                 "sSortDescending": ": Ordenar colunas de forma descendente"
             }
         },
         dom: 'Blfrtip',
         buttons: [
             {
                 extend: 'copy',
                 text: '📋 Copiar',
                 className: 'bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm touch-target mobile-text-xs',
                 exportOptions: {
                     columns: ':visible:not(.noVis)'
                 }
             },
             {
                 extend: 'csv',
                 text: '📄 CSV',
                 className: 'bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm ml-2 touch-target mobile-text-xs',
                 exportOptions: {
                     columns: ':visible:not(.noVis)'
                 },
                 filename: function() {
                     return 'dashboard-clientes-' + new Date().toISOString().slice(0,10);
                 }
             },
             {
                 extend: 'excel',
                 text: '📊 Excel',
                 className: 'bg-emerald-500 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded text-sm ml-2 touch-target mobile-text-xs',
                 exportOptions: {
                     columns: ':visible:not(.noVis)'
                 },
                 filename: function() {
                     return 'dashboard-clientes-' + new Date().toISOString().slice(0,10);
                 },
                 title: 'Dashboard de Clientes - ' + new Date().toLocaleDateString('pt-BR')
             },
             {
                 extend: 'pdf',
                 text: '📑 PDF',
                 className: 'bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm ml-2 touch-target mobile-text-xs',
                 exportOptions: {
                     columns: ':visible:not(.noVis)'
                 },
                 filename: function() {
                     return 'dashboard-clientes-' + new Date().toISOString().slice(0,10);
                 },
                 title: 'Dashboard de Clientes',
                 orientation: 'landscape',
                 pageSize: 'A4',
                 customize: function(doc) {
                     doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                     doc.styles.tableHeader.fontSize = 8;
                     doc.defaultStyle.fontSize = 7;
                 }
             },
             {
                 extend: 'print',
                 text: '🖨️ Imprimir',
                 className: 'bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm ml-2 touch-target mobile-text-xs',
                 exportOptions: {
                     columns: ':visible:not(.noVis)'
                 },
                 title: 'Dashboard de Clientes - ' + new Date().toLocaleDateString('pt-BR')
             },
             {
                 extend: 'colvis',
                 text: '👁️ Colunas',
                 className: 'bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm ml-2 touch-target mobile-text-xs',
                 columns: ':not(.noVis)'
             }
         ],
         colReorder: true,
         columns: [
             { data: null, title: '#', className: 'noVis' },  // Counter column - no data needed
             { data: 0, title: 'Nome' },     // Nome
             { data: 1, title: 'Cidade' },     // Cidade
             { data: 2, title: 'Estado' },     // Estado
             { data: 3, title: 'Bairro' },     // Bairro
             { data: 4, title: 'Cliente Endereço' },     // Cliente Endereço
             { data: 5, title: 'Vendedor' },     // Vendedor
             { data: 6, title: 'Segmento' },     // Segmento
             { data: 7, title: 'RFM' },     // RFM
             { data: 8, title: 'Vendas Mês' },     // Vendas Mês
             { data: 9, title: 'Vendas Ano' },     // Vendas Ano
             { data: 10, title: 'Vendas Ano Passado' },     // Vendas Ano Passado
             { data: 11, title: 'Total Vendas' },      // Total Vendas
             { data: 12, title: 'Última Compra' }    // Última Compra
         ],
         order: [[12, 'desc']],
         columnDefs: [
             {
                 targets: [0],
                 orderable: false,
                 searchable: false,
                 render: function (data, type, row, meta) {
                     return meta.row + meta.settings._iDisplayStart + 1;
                 }
             },
             {
                 targets: [12],
                 orderable: false
             },
             {
                 targets: [9, 10, 11, 12],
                 type: 'num-fmt'
             },
             {
                 targets: [9, 10, 11, 12],
                 className: 'text-right'
             }
         ],
         drawCallback: function(settings) {
             // Optimize rendering for large datasets
             var api = this.api();
             var rows = api.rows({page: 'current'}).nodes();
             
             // Use requestAnimationFrame for smooth rendering
             requestAnimationFrame(function() {
                 $(rows).addClass('fade-in');
             });
             
             // Update charts with current page data
             updateChartsWithData();
             updateSalesChart();
         },
         initComplete: function(settings, json) {
              // Restore column visibility after initialization
              restoreColumnVisibility();
              
              // Initialize performance monitoring
              console.log('DataTable initialized with', json.recordsTotal, 'total records');
          },
          footerCallback: function(row, data, start, end, display) {
             var api = this.api();
             
             // Helper function to convert Brazilian formatted string to number
              var intVal = function(i) {
                  if (typeof i === 'string') {
                      // Remove HTML tags first
                      var cleanStr = i.replace(/<[^>]*>/g, '');
                      // Handle Brazilian number format: remove periods (thousands separator) and replace comma with dot
                      cleanStr = cleanStr.replace(/\./g, '').replace(',', '.');
                      // Remove any remaining non-numeric characters except decimal point and minus sign
                      cleanStr = cleanStr.replace(/[^\d.-]/g, '');
                      return parseFloat(cleanStr) || 0;
                  }
                  return typeof i === 'number' ? i : 0;
              };
             
             // Calculate totals for numeric columns
             // Column 8: Vendas Mês
             var totalVendasMes = api
                 .column(8, { page: 'current' })
                 .data()
                 .reduce(function(a, b) {
                     return intVal(a) + intVal(b);
                 }, 0);
             
             // Column 9: Vendas Ano
             var totalVendasAno = api
                 .column(9, { page: 'current' })
                 .data()
                 .reduce(function(a, b) {
                     return intVal(a) + intVal(b);
                 }, 0);
             
             // Column 10: Vendas Ano Passado
             var totalVendasAnoPassado = api
                 .column(10, { page: 'current' })
                 .data()
                 .reduce(function(a, b) {
                     return intVal(a) + intVal(b);
                 }, 0);
             
             // Column 11: Total Vendas
             var totalVendasTotal = api
                 .column(11, { page: 'current' })
                 .data()
                 .reduce(function(a, b) {
                     return intVal(a) + intVal(b);
                 }, 0);
             
             // Calculate row count for averages
             var rowCount = api.column(8, { page: 'current' }).data().length;
             
             // Calculate averages
             var avgVendasMes = rowCount > 0 ? totalVendasMes / rowCount : 0;
             var avgVendasAno = rowCount > 0 ? totalVendasAno / rowCount : 0;
             var avgVendasAnoPassado = rowCount > 0 ? totalVendasAnoPassado / rowCount : 0;
             var avgVendasTotal = rowCount > 0 ? totalVendasTotal / rowCount : 0;
             
             // Update footer with formatted totals
             $('#total-vendas-mes').html(totalVendasMes.toLocaleString('pt-BR', {
                 minimumFractionDigits: 0,
                 maximumFractionDigits: 0
             }));
             $('#total-vendas-ano').html(totalVendasAno.toLocaleString('pt-BR', {
                 minimumFractionDigits: 0,
                 maximumFractionDigits: 0
             }));
             $('#total-vendas-ano-passado').html(totalVendasAnoPassado.toLocaleString('pt-BR', {
                 minimumFractionDigits: 0,
                 maximumFractionDigits: 0
             }));
             $('#total-vendas-total').html(totalVendasTotal.toLocaleString('pt-BR', {
                 minimumFractionDigits: 0,
                 maximumFractionDigits: 0
             }));
             
             // Update footer with formatted averages
             $('#avg-vendas-mes').html(avgVendasMes.toLocaleString('pt-BR', {
                 minimumFractionDigits: 0,
                 maximumFractionDigits: 0
             }));
             $('#avg-vendas-ano').html(avgVendasAno.toLocaleString('pt-BR', {
                 minimumFractionDigits: 0,
                 maximumFractionDigits: 0
             }));
             $('#avg-vendas-ano-passado').html(avgVendasAnoPassado.toLocaleString('pt-BR', {
                 minimumFractionDigits: 0,
                 maximumFractionDigits: 0
             }));
             $('#avg-vendas-total').html(avgVendasTotal.toLocaleString('pt-BR', {
                 minimumFractionDigits: 0,
                 maximumFractionDigits: 0
             }));
             
             // Update KPI cards with real data
             $('#kpi-total-clientes').html(rowCount.toLocaleString('pt-BR'));
             $('#kpi-vendas-mes').html('R$ ' + totalVendasMes.toLocaleString('pt-BR', {
                 minimumFractionDigits: 2,
                 maximumFractionDigits: 2
             }));
             $('#kpi-vendas-ano').html('R$ ' + totalVendasAno.toLocaleString('pt-BR', {
                 minimumFractionDigits: 2,
                 maximumFractionDigits: 2
             }));
             
             // Calculate ticket médio (total vendas / total clientes)
             var ticketMedio = rowCount > 0 ? totalVendasTotal / rowCount : 0;
             $('#kpi-ticket-medio').html('R$ ' + ticketMedio.toLocaleString('pt-BR', {
                 minimumFractionDigits: 2,
                 maximumFractionDigits: 2
             }));
             
             // Calculate percentage changes (simplified - comparing with previous year)
             var changeVendasMes = totalVendasAnoPassado > 0 ? ((totalVendasMes - (totalVendasAnoPassado/12)) / (totalVendasAnoPassado/12) * 100) : 0;
             var changeVendasAno = totalVendasAnoPassado > 0 ? ((totalVendasAno - totalVendasAnoPassado) / totalVendasAnoPassado * 100) : 0;
             
             $('#kpi-vendas-mes-change').html((changeVendasMes >= 0 ? '+' : '') + changeVendasMes.toFixed(1) + '% vs mês anterior');
             $('#kpi-vendas-ano-change').html((changeVendasAno >= 0 ? '+' : '') + changeVendasAno.toFixed(1) + '% vs ano anterior');
             $('#kpi-ticket-medio-change').html('+0% vs período anterior'); // Placeholder
             
             // Charts removed - no longer needed
         },
         drawCallback: function() {
             // Reapply Tailwind classes after each draw
             $('.dataTables_wrapper').addClass('bg-white rounded-lg shadow-lg p-4');
         }
     })
     .on('draw', function() {
         // Update counter column after each draw
         table.column(0, { page: 'current' }).nodes().each(function(cell, i) {
             var info = table.page.info();
             cell.innerHTML = info.start + i + 1;
         });
     })
     .on('column-visibility.dt', function() {
         // Save column visibility state when changed
         saveColumnVisibility();
     });

     // Restore column visibility on page load
     restoreColumnVisibility();

     // Load filter options
     loadFilterOptions();

     // Apply filters button
     $('#apply-filters').on('click', function() {
         table.ajax.reload();
         // Update sales chart with new filters
         setTimeout(function() {
             updateSalesChart();
         }, 500);
     });

     // Clear filters button
     $('#clear-filters').on('click', function() {
         $('#filter-estado').val('');
         $('#filter-vendedor').val('');
         $('#filter-rfm').val('');
         $('#filter-ultima-compra').data('state', 'inactive').removeClass('bg-green-600 hover:bg-green-700 bg-yellow-600 hover:bg-yellow-700 bg-indigo-600 hover:bg-indigo-700 bg-red-600 hover:bg-red-700').addClass('bg-orange-500 hover:bg-orange-700');
         $('#filter-ultima-compra-text').text('Última Compra');
         $('#filter-ultima-compra-icon').addClass('hidden');
         $('#filter-rua-sao-caetano').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-500 hover:bg-purple-700');
         $('#filter-rua-sao-caetano-text').text('Rua São Caetano');
         $('#filter-rua-sao-caetano-icon').addClass('hidden');
         $('#filter-bom-retiro').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-indigo-500 hover:bg-indigo-700');
         $('#filter-bom-retiro-text').text('Bom Retiro');
         $('#filter-bom-retiro-icon').addClass('hidden');
         $('#filter-bras').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-yellow-500 hover:bg-yellow-700');
         $('#filter-bras-text').text('Brás');
         $('#filter-bras-icon').addClass('hidden');
         $('#filter-vendas-mes-zero').data('state', 'inactive').removeClass('bg-green-600 hover:bg-green-700 bg-blue-600 hover:bg-blue-700').addClass('bg-red-500 hover:bg-red-700');
         $('#filter-vendas-mes-zero-text').text('Vendas Mês');
         $('#filter-vendas-mes-zero-icon').addClass('hidden');
         $('#filter-vendas-ano-zero').data('state', 'inactive').removeClass('bg-green-600 hover:bg-green-700 bg-blue-600 hover:bg-blue-700').addClass('bg-teal-500 hover:bg-teal-700');
         $('#filter-vendas-ano-zero-text').text('Vendas Ano');
         $('#filter-vendas-ano-zero-icon').addClass('hidden');
         // Clear city filters
         $('#filter-sao-paulo').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-blue-500 hover:bg-blue-700');
         $('#filter-sao-paulo-text').text('São Paulo');
         $('#filter-sao-paulo-icon').addClass('hidden');
         $('#filter-sao-paulo-center').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-green-500 hover:bg-green-700');
         $('#filter-sao-paulo-center-text').text('SP Center');
         $('#filter-sao-paulo-center-icon').addClass('hidden');
         $('#filter-sao-paulo-side').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-500 hover:bg-purple-700');
         $('#filter-sao-paulo-side-text').text('SP Side');
         $('#filter-sao-paulo-side-icon').addClass('hidden');
         $('#filter-blumenau').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-orange-500 hover:bg-orange-700');
         $('#filter-blumenau-text').text('Blumenau');
         $('#filter-blumenau-icon').addClass('hidden');
         $('#filter-curitiba').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-red-500 hover:bg-red-700');
         $('#filter-curitiba-text').text('Curitiba');
         $('#filter-curitiba-icon').addClass('hidden');
         $('#filter-maringa').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-pink-500 hover:bg-pink-700');
         $('#filter-maringa-text').text('Maringá');
         $('#filter-maringa-icon').addClass('hidden');
         $('#filter-bauru').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-cyan-500 hover:bg-cyan-700');
         $('#filter-bauru-text').text('Bauru');
         $('#filter-bauru-icon').addClass('hidden');
         $('#filter-birigui').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-lime-500 hover:bg-lime-700');
         $('#filter-birigui-text').text('Birigui');
         $('#filter-birigui-icon').addClass('hidden');
         $('#filter-belo-horizonte').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-amber-500 hover:bg-amber-700');
         $('#filter-belo-horizonte-text').text('Belo Horizonte');
         $('#filter-belo-horizonte-icon').addClass('hidden');
         $('#filter-franca').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-emerald-500 hover:bg-emerald-700');
         $('#filter-franca-text').text('Franca');
         $('#filter-franca-icon').addClass('hidden');
         $('#filter-fortaleza').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-violet-500 hover:bg-violet-700');
         $('#filter-fortaleza-text').text('Fortaleza');
         $('#filter-fortaleza-icon').addClass('hidden');
         $('#filter-juiz-de-fora').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-rose-500 hover:bg-rose-700');
         $('#filter-juiz-de-fora-text').text('Juiz de Fora');
         $('#filter-juiz-de-fora-icon').addClass('hidden');
         
         // Clear state filters - Norte
         $('#filter-acre').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-green-600 hover:bg-green-800');
         $('#filter-acre-text').text('AC');
         $('#filter-acre-icon').addClass('hidden');
         $('#filter-amapa').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-blue-600 hover:bg-blue-800');
         $('#filter-amapa-text').text('AP');
         $('#filter-amapa-icon').addClass('hidden');
         $('#filter-amazonas').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-indigo-600 hover:bg-indigo-800');
         $('#filter-amazonas-text').text('AM');
         $('#filter-amazonas-icon').addClass('hidden');
         $('#filter-para').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-600 hover:bg-purple-800');
         $('#filter-para-text').text('PA');
         $('#filter-para-icon').addClass('hidden');
         $('#filter-rondonia').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-pink-600 hover:bg-pink-800');
         $('#filter-rondonia-text').text('RO');
         $('#filter-rondonia-icon').addClass('hidden');
         $('#filter-roraima').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-red-600 hover:bg-red-800');
         $('#filter-roraima-text').text('RR');
         $('#filter-roraima-icon').addClass('hidden');
         $('#filter-tocantins').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-orange-600 hover:bg-orange-800');
         $('#filter-tocantins-text').text('TO');
         $('#filter-tocantins-icon').addClass('hidden');
         
         // Clear state filters - Nordeste
         $('#filter-alagoas').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-yellow-600 hover:bg-yellow-800');
         $('#filter-alagoas-text').text('AL');
         $('#filter-alagoas-icon').addClass('hidden');
         $('#filter-bahia').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-teal-600 hover:bg-teal-800');
         $('#filter-bahia-text').text('BA');
         $('#filter-bahia-icon').addClass('hidden');
         $('#filter-ceara').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-cyan-600 hover:bg-cyan-800');
         $('#filter-ceara-text').text('CE');
         $('#filter-ceara-icon').addClass('hidden');
         $('#filter-maranhao').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-lime-600 hover:bg-lime-800');
         $('#filter-maranhao-text').text('MA');
         $('#filter-maranhao-icon').addClass('hidden');
         $('#filter-paraiba').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-emerald-600 hover:bg-emerald-800');
         $('#filter-paraiba-text').text('PB');
         $('#filter-paraiba-icon').addClass('hidden');
         $('#filter-pernambuco').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-violet-600 hover:bg-violet-800');
         $('#filter-pernambuco-text').text('PE');
         $('#filter-pernambuco-icon').addClass('hidden');
         $('#filter-piaui').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-fuchsia-600 hover:bg-fuchsia-800');
         $('#filter-piaui-text').text('PI');
         $('#filter-piaui-icon').addClass('hidden');
         $('#filter-rio-grande-do-norte').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-rose-600 hover:bg-rose-800');
         $('#filter-rio-grande-do-norte-text').text('RN');
         $('#filter-rio-grande-do-norte-icon').addClass('hidden');
         $('#filter-sergipe').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-slate-600 hover:bg-slate-800');
         $('#filter-sergipe-text').text('SE');
         $('#filter-sergipe-icon').addClass('hidden');
         
         // Clear state filters - Centro-Oeste
         $('#filter-distrito-federal').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-700 hover:bg-purple-900');
         $('#filter-distrito-federal-text').text('DF');
         $('#filter-distrito-federal-icon').addClass('hidden');
         $('#filter-goias').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-pink-700 hover:bg-pink-900');
         $('#filter-goias-text').text('GO');
         $('#filter-goias-icon').addClass('hidden');
         $('#filter-mato-grosso').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-red-700 hover:bg-red-900');
         $('#filter-mato-grosso-text').text('MT');
         $('#filter-mato-grosso-icon').addClass('hidden');
         $('#filter-mato-grosso-do-sul').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-orange-700 hover:bg-orange-900');
         $('#filter-mato-grosso-do-sul-text').text('MS');
         $('#filter-mato-grosso-do-sul-icon').addClass('hidden');
         
         // Clear state filters - Sudeste
         $('#filter-espirito-santo').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-yellow-700 hover:bg-yellow-900');
         $('#filter-espirito-santo-text').text('ES');
         $('#filter-espirito-santo-icon').addClass('hidden');
         $('#filter-minas-gerais').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-green-700 hover:bg-green-900');
         $('#filter-minas-gerais-text').text('MG');
         $('#filter-minas-gerais-icon').addClass('hidden');
         $('#filter-rio-de-janeiro').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-blue-700 hover:bg-blue-900');
         $('#filter-rio-de-janeiro-text').text('RJ');
         $('#filter-rio-de-janeiro-icon').addClass('hidden');
         $('#filter-sao-paulo-estado').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-indigo-700 hover:bg-indigo-900');
         $('#filter-sao-paulo-estado-text').text('SP');
         $('#filter-sao-paulo-estado-icon').addClass('hidden');
         
         // Clear state filters - Sul
         $('#filter-parana').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-700 hover:bg-purple-900');
         $('#filter-parana-text').text('PR');
         $('#filter-parana-icon').addClass('hidden');
         $('#filter-rio-grande-do-sul').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-pink-700 hover:bg-pink-900');
         $('#filter-rio-grande-do-sul-text').text('RS');
         $('#filter-rio-grande-do-sul-icon').addClass('hidden');
         $('#filter-santa-catarina').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-sky-700 hover:bg-sky-900');
         $('#filter-santa-catarina-text').text('SC');
         $('#filter-santa-catarina-icon').addClass('hidden');
         
         table.ajax.reload();
         // Update sales chart after clearing filters
         setTimeout(function() {
             updateSalesChart();
         }, 500);
     });

     // Última Compra filter button with 5 stages
     $('#filter-ultima-compra').on('click', function() {
         var currentState = $(this).data('state') || 'inactive';
         
         if (currentState === 'inactive') {
             // Stage 1: 30-60 days
             $(this).data('state', '30-60');
             $(this).removeClass('bg-orange-500 hover:bg-orange-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-ultima-compra-text').text('Filtro ativo: 30-60 dias');
             $('#filter-ultima-compra-icon').removeClass('hidden');
         } else if (currentState === '30-60') {
             // Stage 2: 60-90 days
             $(this).data('state', '60-90');
             $(this).removeClass('bg-green-600 hover:bg-green-700').addClass('bg-yellow-600 hover:bg-yellow-700');
             $('#filter-ultima-compra-text').text('Filtro ativo: 60-90 dias');
             $('#filter-ultima-compra-icon').removeClass('hidden');
         } else if (currentState === '60-90') {
             // Stage 3: 90-180 days
             $(this).data('state', '90-180');
             $(this).removeClass('bg-yellow-600 hover:bg-yellow-700').addClass('bg-indigo-600 hover:bg-indigo-700');
             $('#filter-ultima-compra-text').text('Filtro ativo: 90-180 dias');
             $('#filter-ultima-compra-icon').removeClass('hidden');
         } else if (currentState === '90-180') {
             // Stage 4: 180-365 days
             $(this).data('state', '180-365');
             $(this).removeClass('bg-indigo-600 hover:bg-indigo-700').addClass('bg-red-600 hover:bg-red-700');
             $('#filter-ultima-compra-text').text('Filtro ativo: 180-365 dias');
             $('#filter-ultima-compra-icon').removeClass('hidden');
         } else if (currentState === '180-365') {
             // Stage 5: >365 days
             $(this).data('state', '>365');
             $(this).removeClass('bg-red-600 hover:bg-red-700').addClass('bg-gray-800 hover:bg-gray-900');
             $('#filter-ultima-compra-text').text('Filtro ativo: >365 dias');
             $('#filter-ultima-compra-icon').removeClass('hidden');
         } else {
             // Stage 6: deactivate
             $(this).data('state', 'inactive');
             $(this).removeClass('bg-gray-800 hover:bg-gray-900').addClass('bg-orange-500 hover:bg-orange-700');
             $('#filter-ultima-compra-text').text('Última Compra');
             $('#filter-ultima-compra-icon').addClass('hidden');
         }
         table.ajax.reload();
     });

     // Instant deactivation for Última Compra filter (right-click)
     $('#filter-ultima-compra').on('contextmenu', function(e) {
         e.preventDefault(); // Prevent context menu
         var currentState = $(this).data('state') || 'inactive';
         
         // Only deactivate if currently active
         if (currentState !== 'inactive') {
             $(this).data('state', 'inactive');
             $(this).removeClass('bg-green-600 hover:bg-green-700 bg-yellow-600 hover:bg-yellow-700 bg-indigo-600 hover:bg-indigo-700 bg-red-600 hover:bg-red-700 bg-gray-800 hover:bg-gray-900').addClass('bg-orange-500 hover:bg-orange-700');
             $('#filter-ultima-compra-text').text('Última Compra');
             $('#filter-ultima-compra-icon').addClass('hidden');
             table.ajax.reload();
         }
     });

     // Rua São Caetano filter button
     $('#filter-rua-sao-caetano').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-500 hover:bg-purple-700');
             $('#filter-rua-sao-caetano-text').text('Rua São Caetano');
             $('#filter-rua-sao-caetano-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-purple-500 hover:bg-purple-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-rua-sao-caetano-text').text('Filtro ativo: São Caetano');
             $('#filter-rua-sao-caetano-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Bom Retiro filter button
     $('#filter-bom-retiro').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-indigo-500 hover:bg-indigo-700');
             $('#filter-bom-retiro-text').text('Bom Retiro');
             $('#filter-bom-retiro-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-indigo-500 hover:bg-indigo-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-bom-retiro-text').text('Filtro ativo: Bom Retiro');
             $('#filter-bom-retiro-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Brás filter button
     $('#filter-bras').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-yellow-500 hover:bg-yellow-700');
             $('#filter-bras-text').text('Brás');
             $('#filter-bras-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-yellow-500 hover:bg-yellow-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-bras-text').text('Filtro ativo: Brás');
             $('#filter-bras-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Vendas Mês filter button with 3 stages
     $('#filter-vendas-mes-zero').on('click', function() {
         var currentState = $(this).data('state') || 'inactive';
         
         if (currentState === 'inactive') {
             // Stage 1: vendas = 0
             $(this).data('state', 'zero');
             $(this).removeClass('bg-red-500 hover:bg-red-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-vendas-mes-zero-text').text('Filtro ativo: Vendas = 0');
             $('#filter-vendas-mes-zero-icon').removeClass('hidden');
         } else if (currentState === 'zero') {
             // Stage 2: vendas > 0
             $(this).data('state', 'positive');
             $(this).removeClass('bg-green-600 hover:bg-green-700').addClass('bg-blue-600 hover:bg-blue-700');
             $('#filter-vendas-mes-zero-text').text('Filtro ativo: Vendas > 0');
             $('#filter-vendas-mes-zero-icon').removeClass('hidden');
         } else {
             // Stage 3: deactivate
             $(this).data('state', 'inactive');
             $(this).removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-red-500 hover:bg-red-700');
             $('#filter-vendas-mes-zero-text').text('Vendas Mês');
             $('#filter-vendas-mes-zero-icon').addClass('hidden');
         }
         table.ajax.reload();
     });

     // Vendas Ano filter button with 3 stages
     $('#filter-vendas-ano-zero').on('click', function() {
         var currentState = $(this).data('state') || 'inactive';
         
         if (currentState === 'inactive') {
             // Stage 1: vendas = 0
             $(this).data('state', 'zero');
             $(this).removeClass('bg-teal-500 hover:bg-teal-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-vendas-ano-zero-text').text('Filtro ativo: Vendas = 0');
             $('#filter-vendas-ano-zero-icon').removeClass('hidden');
         } else if (currentState === 'zero') {
             // Stage 2: vendas > 0
             $(this).data('state', 'positive');
             $(this).removeClass('bg-green-600 hover:bg-green-700').addClass('bg-blue-600 hover:bg-blue-700');
             $('#filter-vendas-ano-zero-text').text('Filtro ativo: Vendas > 0');
             $('#filter-vendas-ano-zero-icon').removeClass('hidden');
         } else {
             // Stage 3: deactivate
             $(this).data('state', 'inactive');
             $(this).removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-teal-500 hover:bg-teal-700');
             $('#filter-vendas-ano-zero-text').text('Vendas Ano');
             $('#filter-vendas-ano-zero-icon').addClass('hidden');
         }
         table.ajax.reload();
     });

     // Vendas Ano Passado filter button with 3 stages
     $('#filter-vendas-ano-passado-zero').on('click', function() {
         var currentState = $(this).data('state') || 'inactive';
         
         if (currentState === 'inactive') {
             // Stage 1: vendas = 0
             $(this).data('state', 'zero');
             $(this).removeClass('bg-purple-500 hover:bg-purple-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-vendas-ano-passado-zero-text').text('Filtro ativo: Vendas = 0');
             $('#filter-vendas-ano-passado-zero-icon').removeClass('hidden');
         } else if (currentState === 'zero') {
             // Stage 2: vendas > 0
             $(this).data('state', 'positive');
             $(this).removeClass('bg-green-600 hover:bg-green-700').addClass('bg-blue-600 hover:bg-blue-700');
             $('#filter-vendas-ano-passado-zero-text').text('Filtro ativo: Vendas > 0');
             $('#filter-vendas-ano-passado-zero-icon').removeClass('hidden');
         } else {
             // Stage 3: deactivate
             $(this).data('state', 'inactive');
             $(this).removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-purple-500 hover:bg-purple-700');
             $('#filter-vendas-ano-passado-zero-text').text('Vendas Ano Passado');
             $('#filter-vendas-ano-passado-zero-icon').addClass('hidden');
         }
         table.ajax.reload();
     });

     // Function to deactivate all neighborhood filters
     function deactivateAllNeighborhoodFilters() {
         // Deactivate Brás
         $('#filter-bras').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-yellow-500 hover:bg-yellow-700');
         $('#filter-bras-text').text('Brás');
         $('#filter-bras-icon').addClass('hidden');
         
         // Deactivate Rua São Caetano
         $('#filter-rua-sao-caetano').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-500 hover:bg-purple-700');
         $('#filter-rua-sao-caetano-text').text('Rua São Caetano');
         $('#filter-rua-sao-caetano-icon').addClass('hidden');
         
         // Deactivate Bom Retiro
         $('#filter-bom-retiro').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-indigo-500 hover:bg-indigo-700');
         $('#filter-bom-retiro-text').text('Bom Retiro');
         $('#filter-bom-retiro-icon').addClass('hidden');
     }

     // Function to deactivate all city filters
     function deactivateAllCityFilters() {
         // Deactivate São Paulo
         $('#filter-sao-paulo').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-blue-500 hover:bg-blue-700');
         $('#filter-sao-paulo-text').text('São Paulo');
         $('#filter-sao-paulo-icon').addClass('hidden');
         
         // Deactivate São Paulo Center
         $('#filter-sao-paulo-center').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-green-500 hover:bg-green-700');
         $('#filter-sao-paulo-center-text').text('SP Center');
         $('#filter-sao-paulo-center-icon').addClass('hidden');
         
         // Deactivate São Paulo Side
         $('#filter-sao-paulo-side').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-500 hover:bg-purple-700');
         $('#filter-sao-paulo-side-text').text('SP Side');
         $('#filter-sao-paulo-side-icon').addClass('hidden');
         
         // Deactivate Blumenau
         $('#filter-blumenau').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-orange-500 hover:bg-orange-700');
         $('#filter-blumenau-text').text('Blumenau');
         $('#filter-blumenau-icon').addClass('hidden');
         
         // Deactivate Curitiba
         $('#filter-curitiba').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-red-500 hover:bg-red-700');
         $('#filter-curitiba-text').text('Curitiba');
         $('#filter-curitiba-icon').addClass('hidden');
         
         // Deactivate Maringá
         $('#filter-maringa').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-pink-500 hover:bg-pink-700');
         $('#filter-maringa-text').text('Maringá');
         $('#filter-maringa-icon').addClass('hidden');
         
         // Deactivate Bauru
         $('#filter-bauru').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-cyan-500 hover:bg-cyan-700');
         $('#filter-bauru-text').text('Bauru');
         $('#filter-bauru-icon').addClass('hidden');
         
         // Deactivate Birigui
         $('#filter-birigui').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-lime-500 hover:bg-lime-700');
         $('#filter-birigui-text').text('Birigui');
         $('#filter-birigui-icon').addClass('hidden');
         
         // Deactivate Belo Horizonte
         $('#filter-belo-horizonte').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-amber-500 hover:bg-amber-700');
         $('#filter-belo-horizonte-text').text('Belo Horizonte');
         $('#filter-belo-horizonte-icon').addClass('hidden');
         
         // Deactivate Franca
         $('#filter-franca').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-emerald-500 hover:bg-emerald-700');
         $('#filter-franca-text').text('Franca');
         $('#filter-franca-icon').addClass('hidden');
         
         // Deactivate Fortaleza
         $('#filter-fortaleza').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-violet-500 hover:bg-violet-700');
         $('#filter-fortaleza-text').text('Fortaleza');
         $('#filter-fortaleza-icon').addClass('hidden');
         
         // Deactivate Juiz de Fora
         $('#filter-juiz-de-fora').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-rose-500 hover:bg-rose-700');
         $('#filter-juiz-de-fora-text').text('Juiz de Fora');
         $('#filter-juiz-de-fora-icon').addClass('hidden');
     }

     // São Paulo filter button
     $('#filter-sao-paulo').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-blue-500 hover:bg-blue-700');
             $('#filter-sao-paulo-text').text('São Paulo');
             $('#filter-sao-paulo-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-blue-500 hover:bg-blue-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-sao-paulo-text').text('Filtro ativo: São Paulo');
             $('#filter-sao-paulo-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // São Paulo Center filter button
     $('#filter-sao-paulo-center').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-green-500 hover:bg-green-700');
             $('#filter-sao-paulo-center-text').text('SP Center');
             $('#filter-sao-paulo-center-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-green-500 hover:bg-green-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-sao-paulo-center-text').text('Filtro ativo: SP Center');
             $('#filter-sao-paulo-center-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // São Paulo Side filter button
     $('#filter-sao-paulo-side').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-500 hover:bg-purple-700');
             $('#filter-sao-paulo-side-text').text('SP Side');
             $('#filter-sao-paulo-side-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-purple-500 hover:bg-purple-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-sao-paulo-side-text').text('Filtro ativo: SP Side');
             $('#filter-sao-paulo-side-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Blumenau filter button
     $('#filter-blumenau').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-orange-500 hover:bg-orange-700');
             $('#filter-blumenau-text').text('Blumenau');
             $('#filter-blumenau-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-orange-500 hover:bg-orange-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-blumenau-text').text('Filtro ativo: Blumenau');
             $('#filter-blumenau-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Curitiba filter button
     $('#filter-curitiba').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-red-500 hover:bg-red-700');
             $('#filter-curitiba-text').text('Curitiba');
             $('#filter-curitiba-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-red-500 hover:bg-red-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-curitiba-text').text('Filtro ativo: Curitiba');
             $('#filter-curitiba-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Maringá filter button
     $('#filter-maringa').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-pink-500 hover:bg-pink-700');
             $('#filter-maringa-text').text('Maringá');
             $('#filter-maringa-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-pink-500 hover:bg-pink-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-maringa-text').text('Filtro ativo: Maringá');
             $('#filter-maringa-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Bauru filter button
     $('#filter-bauru').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-cyan-500 hover:bg-cyan-700');
             $('#filter-bauru-text').text('Bauru');
             $('#filter-bauru-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-cyan-500 hover:bg-cyan-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-bauru-text').text('Filtro ativo: Bauru');
             $('#filter-bauru-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Birigui filter button
     $('#filter-birigui').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-lime-500 hover:bg-lime-700');
             $('#filter-birigui-text').text('Birigui');
             $('#filter-birigui-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-lime-500 hover:bg-lime-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-birigui-text').text('Filtro ativo: Birigui');
             $('#filter-birigui-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Belo Horizonte filter button
     $('#filter-belo-horizonte').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-amber-500 hover:bg-amber-700');
             $('#filter-belo-horizonte-text').text('Belo Horizonte');
             $('#filter-belo-horizonte-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-amber-500 hover:bg-amber-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-belo-horizonte-text').text('Filtro ativo: Belo Horizonte');
             $('#filter-belo-horizonte-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Franca filter button
     $('#filter-franca').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-emerald-500 hover:bg-emerald-700');
             $('#filter-franca-text').text('Franca');
             $('#filter-franca-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-emerald-500 hover:bg-emerald-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-franca-text').text('Filtro ativo: Franca');
             $('#filter-franca-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Fortaleza filter button
     $('#filter-fortaleza').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-violet-500 hover:bg-violet-700');
             $('#filter-fortaleza-text').text('Fortaleza');
             $('#filter-fortaleza-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-violet-500 hover:bg-violet-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-fortaleza-text').text('Filtro ativo: Fortaleza');
             $('#filter-fortaleza-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Regiane filter button (vendedor ID 107)
     $('#filter-vendedor-regiane').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-indigo-500 hover:bg-indigo-700');
             $('#filter-vendedor-regiane-text').text('👩‍💼 Regiane');
             $('#filter-vendedor-regiane-icon').addClass('hidden');
         } else {
             // Deactivate all other vendor filters first
             deactivateAllVendorFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-indigo-500 hover:bg-indigo-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-vendedor-regiane-text').text('Filtro ativo: Regiane');
             $('#filter-vendedor-regiane-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Rosana filter button (vendedor ID 131)
     $('#filter-vendedor-rosana').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-pink-500 hover:bg-pink-700');
             $('#filter-vendedor-rosana-text').text('👩‍💼 Rosana');
             $('#filter-vendedor-rosana-icon').addClass('hidden');
         } else {
             // Deactivate all other vendor filters first
             deactivateAllVendorFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-pink-500 hover:bg-pink-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-vendedor-rosana-text').text('Filtro ativo: Rosana');
             $('#filter-vendedor-rosana-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Edilene filter button (vendedor ID 32)
     $('#filter-vendedor-edilene').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-emerald-500 hover:bg-emerald-700');
             $('#filter-vendedor-edilene-text').text('👩‍💼 Edilene');
             $('#filter-vendedor-edilene-icon').addClass('hidden');
         } else {
             // Deactivate all other vendor filters first
             deactivateAllVendorFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-emerald-500 hover:bg-emerald-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-vendedor-edilene-text').text('Filtro ativo: Edilene');
             $('#filter-vendedor-edilene-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Debora filter button (vendedor ID 89)
     $('#filter-vendedor-debora').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-teal-500 hover:bg-teal-700');
             $('#filter-vendedor-debora-text').text('👩‍💼 Debora');
             $('#filter-vendedor-debora-icon').addClass('hidden');
         } else {
             // Deactivate all other vendor filters first
             deactivateAllVendorFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-teal-500 hover:bg-teal-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-vendedor-debora-text').text('Filtro ativo: Debora');
             $('#filter-vendedor-debora-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Miriam filter button (vendedor ID 117)
     $('#filter-vendedor-miriam').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-cyan-500 hover:bg-cyan-700');
             $('#filter-vendedor-miriam-text').text('👩‍💼 Miriam');
             $('#filter-vendedor-miriam-icon').addClass('hidden');
         } else {
             // Deactivate all other vendor filters first
             deactivateAllVendorFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-cyan-500 hover:bg-cyan-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-vendedor-miriam-text').text('Filtro ativo: Miriam');
             $('#filter-vendedor-miriam-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Andrea filter button (vendedor ID 218)
     $('#filter-vendedor-andrea').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-violet-500 hover:bg-violet-700');
             $('#filter-vendedor-andrea-text').text('👩‍💼 Andrea');
             $('#filter-vendedor-andrea-icon').addClass('hidden');
         } else {
             // Deactivate all other vendor filters first
             deactivateAllVendorFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-violet-500 hover:bg-violet-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-vendedor-andrea-text').text('Filtro ativo: Andrea');
             $('#filter-vendedor-andrea-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Cristiane filter button (vendedor ID 197)
     $('#filter-vendedor-cristiane').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-orange-500 hover:bg-orange-700');
             $('#filter-vendedor-cristiane-text').text('👩‍💼 Cristiane');
             $('#filter-vendedor-cristiane-icon').addClass('hidden');
         } else {
             // Deactivate all other vendor filters first
             deactivateAllVendorFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-orange-500 hover:bg-orange-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-vendedor-cristiane-text').text('Filtro ativo: Cristiane');
             $('#filter-vendedor-cristiane-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Luana filter button (vendedor ID 1030)
     $('#filter-vendedor-luana').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-rose-500 hover:bg-rose-700');
             $('#filter-vendedor-luana-text').text('👩‍💼 Luana');
             $('#filter-vendedor-luana-icon').addClass('hidden');
         } else {
             // Deactivate all other vendor filters first
             deactivateAllVendorFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-rose-500 hover:bg-rose-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-vendedor-luana-text').text('Filtro ativo: Luana');
             $('#filter-vendedor-luana-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Revisar filter button (vendedor ID 8)
     $('#filter-vendedor-revisar').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-slate-500 hover:bg-slate-700');
             $('#filter-vendedor-revisar-text').text('🔍 Revisar');
             $('#filter-vendedor-revisar-icon').addClass('hidden');
         } else {
             // Deactivate all other vendor filters first
             deactivateAllVendorFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-slate-500 hover:bg-slate-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-vendedor-revisar-text').text('Filtro ativo: Revisar');
             $('#filter-vendedor-revisar-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Juiz de Fora filter button
     $('#filter-juiz-de-fora').on('click', function() {
         if ($(this).hasClass('active')) {
             // If already active, deactivate it
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-rose-500 hover:bg-rose-700');
             $('#filter-juiz-de-fora-text').text('Juiz de Fora');
             $('#filter-juiz-de-fora-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             // Then activate this one
             $(this).addClass('active').removeClass('bg-rose-500 hover:bg-rose-700').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-juiz-de-fora-text').text('Filtro ativo: Juiz de Fora');
             $('#filter-juiz-de-fora-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // State filter handlers
     // Acre filter button
     $('#filter-acre').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-green-600 hover:bg-green-800');
             $('#filter-acre-text').text('AC');
             $('#filter-acre-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-green-600 hover:bg-green-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-acre-text').text('Filtro ativo: AC');
             $('#filter-acre-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Amapá filter button
     $('#filter-amapa').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-blue-600 hover:bg-blue-800');
             $('#filter-amapa-text').text('AP');
             $('#filter-amapa-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-blue-600 hover:bg-blue-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-amapa-text').text('Filtro ativo: AP');
             $('#filter-amapa-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Amazonas filter button
     $('#filter-amazonas').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-indigo-600 hover:bg-indigo-800');
             $('#filter-amazonas-text').text('AM');
             $('#filter-amazonas-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-indigo-600 hover:bg-indigo-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-amazonas-text').text('Filtro ativo: AM');
             $('#filter-amazonas-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Pará filter button
     $('#filter-para').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-600 hover:bg-purple-800');
             $('#filter-para-text').text('PA');
             $('#filter-para-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-purple-600 hover:bg-purple-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-para-text').text('Filtro ativo: PA');
             $('#filter-para-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Rondônia filter button
     $('#filter-rondonia').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-pink-600 hover:bg-pink-800');
             $('#filter-rondonia-text').text('RO');
             $('#filter-rondonia-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-pink-600 hover:bg-pink-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-rondonia-text').text('Filtro ativo: RO');
             $('#filter-rondonia-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Roraima filter button
     $('#filter-roraima').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-red-600 hover:bg-red-800');
             $('#filter-roraima-text').text('RR');
             $('#filter-roraima-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-red-600 hover:bg-red-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-roraima-text').text('Filtro ativo: RR');
             $('#filter-roraima-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Tocantins filter button
     $('#filter-tocantins').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-orange-600 hover:bg-orange-800');
             $('#filter-tocantins-text').text('TO');
             $('#filter-tocantins-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-orange-600 hover:bg-orange-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-tocantins-text').text('Filtro ativo: TO');
             $('#filter-tocantins-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Alagoas filter button
     $('#filter-alagoas').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-yellow-600 hover:bg-yellow-800');
             $('#filter-alagoas-text').text('AL');
             $('#filter-alagoas-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-yellow-600 hover:bg-yellow-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-alagoas-text').text('Filtro ativo: AL');
             $('#filter-alagoas-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Bahia filter button
     $('#filter-bahia').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-amber-600 hover:bg-amber-800');
             $('#filter-bahia-text').text('BA');
             $('#filter-bahia-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-amber-600 hover:bg-amber-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-bahia-text').text('Filtro ativo: BA');
             $('#filter-bahia-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Ceará filter button
     $('#filter-ceara').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-lime-600 hover:bg-lime-800');
             $('#filter-ceara-text').text('CE');
             $('#filter-ceara-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-lime-600 hover:bg-lime-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-ceara-text').text('Filtro ativo: CE');
             $('#filter-ceara-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Maranhão filter button
     $('#filter-maranhao').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-emerald-600 hover:bg-emerald-800');
             $('#filter-maranhao-text').text('MA');
             $('#filter-maranhao-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-emerald-600 hover:bg-emerald-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-maranhao-text').text('Filtro ativo: MA');
             $('#filter-maranhao-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Paraíba filter button
     $('#filter-paraiba').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-teal-600 hover:bg-teal-800');
             $('#filter-paraiba-text').text('PB');
             $('#filter-paraiba-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-teal-600 hover:bg-teal-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-paraiba-text').text('Filtro ativo: PB');
             $('#filter-paraiba-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Pernambuco filter button
     $('#filter-pernambuco').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-cyan-600 hover:bg-cyan-800');
             $('#filter-pernambuco-text').text('PE');
             $('#filter-pernambuco-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-cyan-600 hover:bg-cyan-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-pernambuco-text').text('Filtro ativo: PE');
             $('#filter-pernambuco-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Piauí filter button
     $('#filter-piaui').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-sky-600 hover:bg-sky-800');
             $('#filter-piaui-text').text('PI');
             $('#filter-piaui-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-sky-600 hover:bg-sky-800').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-piaui-text').text('Filtro ativo: PI');
             $('#filter-piaui-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Rio Grande do Norte filter button
     $('#filter-rio-grande-do-norte').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-blue-700 hover:bg-blue-900');
             $('#filter-rio-grande-do-norte-text').text('RN');
             $('#filter-rio-grande-do-norte-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-blue-700 hover:bg-blue-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-rio-grande-do-norte-text').text('Filtro ativo: RN');
             $('#filter-rio-grande-do-norte-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Sergipe filter button
     $('#filter-sergipe').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-indigo-700 hover:bg-indigo-900');
             $('#filter-sergipe-text').text('SE');
             $('#filter-sergipe-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-indigo-700 hover:bg-indigo-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-sergipe-text').text('Filtro ativo: SE');
             $('#filter-sergipe-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Distrito Federal filter button
     $('#filter-distrito-federal').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-700 hover:bg-purple-900');
             $('#filter-distrito-federal-text').text('DF');
             $('#filter-distrito-federal-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-purple-700 hover:bg-purple-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-distrito-federal-text').text('Filtro ativo: DF');
             $('#filter-distrito-federal-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Goiás filter button
     $('#filter-goias').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-pink-700 hover:bg-pink-900');
             $('#filter-goias-text').text('GO');
             $('#filter-goias-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-pink-700 hover:bg-pink-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-goias-text').text('Filtro ativo: GO');
             $('#filter-goias-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Mato Grosso filter button
     $('#filter-mato-grosso').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-red-700 hover:bg-red-900');
             $('#filter-mato-grosso-text').text('MT');
             $('#filter-mato-grosso-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-red-700 hover:bg-red-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-mato-grosso-text').text('Filtro ativo: MT');
             $('#filter-mato-grosso-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Mato Grosso do Sul filter button
     $('#filter-mato-grosso-do-sul').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-orange-700 hover:bg-orange-900');
             $('#filter-mato-grosso-do-sul-text').text('MS');
             $('#filter-mato-grosso-do-sul-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-orange-700 hover:bg-orange-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-mato-grosso-do-sul-text').text('Filtro ativo: MS');
             $('#filter-mato-grosso-do-sul-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Espírito Santo filter button
     $('#filter-espirito-santo').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-yellow-700 hover:bg-yellow-900');
             $('#filter-espirito-santo-text').text('ES');
             $('#filter-espirito-santo-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-yellow-700 hover:bg-yellow-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-espirito-santo-text').text('Filtro ativo: ES');
             $('#filter-espirito-santo-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Minas Gerais filter button
     $('#filter-minas-gerais').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-amber-700 hover:bg-amber-900');
             $('#filter-minas-gerais-text').text('MG');
             $('#filter-minas-gerais-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-amber-700 hover:bg-amber-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-minas-gerais-text').text('Filtro ativo: MG');
             $('#filter-minas-gerais-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Rio de Janeiro filter button
     $('#filter-rio-de-janeiro').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-lime-700 hover:bg-lime-900');
             $('#filter-rio-de-janeiro-text').text('RJ');
             $('#filter-rio-de-janeiro-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-lime-700 hover:bg-lime-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-rio-de-janeiro-text').text('Filtro ativo: RJ');
             $('#filter-rio-de-janeiro-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // São Paulo Estado filter button
     $('#filter-sao-paulo-estado').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-emerald-700 hover:bg-emerald-900');
             $('#filter-sao-paulo-estado-text').text('SP');
             $('#filter-sao-paulo-estado-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-emerald-700 hover:bg-emerald-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-sao-paulo-estado-text').text('Filtro ativo: SP');
             $('#filter-sao-paulo-estado-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Paraná filter button
     $('#filter-parana').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-teal-700 hover:bg-teal-900');
             $('#filter-parana-text').text('PR');
             $('#filter-parana-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-teal-700 hover:bg-teal-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-parana-text').text('Filtro ativo: PR');
             $('#filter-parana-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Rio Grande do Sul filter button
     $('#filter-rio-grande-do-sul').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-cyan-700 hover:bg-cyan-900');
             $('#filter-rio-grande-do-sul-text').text('RS');
             $('#filter-rio-grande-do-sul-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-cyan-700 hover:bg-cyan-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-rio-grande-do-sul-text').text('Filtro ativo: RS');
             $('#filter-rio-grande-do-sul-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

     // Santa Catarina filter button
     $('#filter-santa-catarina').on('click', function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-sky-700 hover:bg-sky-900');
             $('#filter-santa-catarina-text').text('SC');
             $('#filter-santa-catarina-icon').addClass('hidden');
         } else {
             // Deactivate all other filters first
             deactivateAllNeighborhoodFilters();
             deactivateAllCityFilters();
             deactivateAllStateFilters();
             $(this).addClass('active').removeClass('bg-sky-700 hover:bg-sky-900').addClass('bg-green-600 hover:bg-green-700');
             $('#filter-santa-catarina-text').text('Filtro ativo: SC');
             $('#filter-santa-catarina-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });

      // Function to deactivate all vendor filters
     function deactivateAllVendorFilters() {
         // Deactivate Regiane
         $('#filter-vendedor-regiane').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-indigo-500 hover:bg-indigo-700');
         $('#filter-vendedor-regiane-text').text('👩‍💼 Regiane');
         $('#filter-vendedor-regiane-icon').addClass('hidden');
         
         // Deactivate Rosana
         $('#filter-vendedor-rosana').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-pink-500 hover:bg-pink-700');
         $('#filter-vendedor-rosana-text').text('👩‍💼 Rosana');
         $('#filter-vendedor-rosana-icon').addClass('hidden');
         
         // Deactivate Edilene
         $('#filter-vendedor-edilene').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-emerald-500 hover:bg-emerald-700');
         $('#filter-vendedor-edilene-text').text('👩‍💼 Edilene');
         $('#filter-vendedor-edilene-icon').addClass('hidden');
         
         // Deactivate Debora
         $('#filter-vendedor-debora').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-teal-500 hover:bg-teal-700');
         $('#filter-vendedor-debora-text').text('👩‍💼 Debora');
         $('#filter-vendedor-debora-icon').addClass('hidden');
         
         // Deactivate Miriam
         $('#filter-vendedor-miriam').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-cyan-500 hover:bg-cyan-700');
         $('#filter-vendedor-miriam-text').text('👩‍💼 Miriam');
         $('#filter-vendedor-miriam-icon').addClass('hidden');
         
         // Deactivate Andrea
         $('#filter-vendedor-andrea').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-violet-500 hover:bg-violet-700');
         $('#filter-vendedor-andrea-text').text('👩‍💼 Andrea');
         $('#filter-vendedor-andrea-icon').addClass('hidden');
         
         // Deactivate Cristiane
         $('#filter-vendedor-cristiane').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-orange-500 hover:bg-orange-700');
         $('#filter-vendedor-cristiane-text').text('👩‍💼 Cristiane');
         $('#filter-vendedor-cristiane-icon').addClass('hidden');
         
         // Deactivate Luana
         $('#filter-vendedor-luana').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-rose-500 hover:bg-rose-700');
         $('#filter-vendedor-luana-text').text('👩‍💼 Luana');
         $('#filter-vendedor-luana-icon').addClass('hidden');
         
         // Deactivate Revisar
         $('#filter-vendedor-revisar').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-slate-500 hover:bg-slate-700');
         $('#filter-vendedor-revisar-text').text('🔍 Revisar');
         $('#filter-vendedor-revisar-icon').addClass('hidden');
     }

     // Function to deactivate all state filters
     function deactivateAllStateFilters() {
         // Norte
         $('#filter-acre').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-green-600 hover:bg-green-800');
         $('#filter-acre-text').text('AC');
         $('#filter-acre-icon').addClass('hidden');
         
         $('#filter-amapa').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-blue-600 hover:bg-blue-800');
         $('#filter-amapa-text').text('AP');
         $('#filter-amapa-icon').addClass('hidden');
         
         $('#filter-amazonas').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-indigo-600 hover:bg-indigo-800');
         $('#filter-amazonas-text').text('AM');
         $('#filter-amazonas-icon').addClass('hidden');
         
         $('#filter-para').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-600 hover:bg-purple-800');
         $('#filter-para-text').text('PA');
         $('#filter-para-icon').addClass('hidden');
         
         $('#filter-rondonia').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-pink-600 hover:bg-pink-800');
         $('#filter-rondonia-text').text('RO');
         $('#filter-rondonia-icon').addClass('hidden');
         
         $('#filter-roraima').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-red-600 hover:bg-red-800');
         $('#filter-roraima-text').text('RR');
         $('#filter-roraima-icon').addClass('hidden');
         
         $('#filter-tocantins').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-orange-600 hover:bg-orange-800');
         $('#filter-tocantins-text').text('TO');
         $('#filter-tocantins-icon').addClass('hidden');
         
         // Nordeste
         $('#filter-alagoas').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-yellow-600 hover:bg-yellow-800');
         $('#filter-alagoas-text').text('AL');
         $('#filter-alagoas-icon').addClass('hidden');
         
         $('#filter-bahia').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-amber-600 hover:bg-amber-800');
         $('#filter-bahia-text').text('BA');
         $('#filter-bahia-icon').addClass('hidden');
         
         $('#filter-ceara').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-lime-600 hover:bg-lime-800');
         $('#filter-ceara-text').text('CE');
         $('#filter-ceara-icon').addClass('hidden');
         
         $('#filter-maranhao').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-emerald-600 hover:bg-emerald-800');
         $('#filter-maranhao-text').text('MA');
         $('#filter-maranhao-icon').addClass('hidden');
         
         $('#filter-paraiba').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-teal-600 hover:bg-teal-800');
         $('#filter-paraiba-text').text('PB');
         $('#filter-paraiba-icon').addClass('hidden');
         
         $('#filter-pernambuco').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-cyan-600 hover:bg-cyan-800');
         $('#filter-pernambuco-text').text('PE');
         $('#filter-pernambuco-icon').addClass('hidden');
         
         $('#filter-piaui').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-sky-600 hover:bg-sky-800');
         $('#filter-piaui-text').text('PI');
         $('#filter-piaui-icon').addClass('hidden');
         
         $('#filter-rio-grande-do-norte').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-blue-700 hover:bg-blue-900');
         $('#filter-rio-grande-do-norte-text').text('RN');
         $('#filter-rio-grande-do-norte-icon').addClass('hidden');
         
         $('#filter-sergipe').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-indigo-700 hover:bg-indigo-900');
         $('#filter-sergipe-text').text('SE');
         $('#filter-sergipe-icon').addClass('hidden');
         
         // Centro-Oeste
         $('#filter-distrito-federal').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-purple-700 hover:bg-purple-900');
         $('#filter-distrito-federal-text').text('DF');
         $('#filter-distrito-federal-icon').addClass('hidden');
         
         $('#filter-goias').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-pink-700 hover:bg-pink-900');
         $('#filter-goias-text').text('GO');
         $('#filter-goias-icon').addClass('hidden');
         
         $('#filter-mato-grosso').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-red-700 hover:bg-red-900');
         $('#filter-mato-grosso-text').text('MT');
         $('#filter-mato-grosso-icon').addClass('hidden');
         
         $('#filter-mato-grosso-do-sul').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-orange-700 hover:bg-orange-900');
         $('#filter-mato-grosso-do-sul-text').text('MS');
         $('#filter-mato-grosso-do-sul-icon').addClass('hidden');
         
         // Sudeste
         $('#filter-espirito-santo').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-yellow-700 hover:bg-yellow-900');
         $('#filter-espirito-santo-text').text('ES');
         $('#filter-espirito-santo-icon').addClass('hidden');
         
         $('#filter-minas-gerais').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-amber-700 hover:bg-amber-900');
         $('#filter-minas-gerais-text').text('MG');
         $('#filter-minas-gerais-icon').addClass('hidden');
         
         $('#filter-rio-de-janeiro').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-lime-700 hover:bg-lime-900');
         $('#filter-rio-de-janeiro-text').text('RJ');
         $('#filter-rio-de-janeiro-icon').addClass('hidden');
         
         $('#filter-sao-paulo-estado').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-emerald-700 hover:bg-emerald-900');
         $('#filter-sao-paulo-estado-text').text('SP');
         $('#filter-sao-paulo-estado-icon').addClass('hidden');
         
         // Sul
         $('#filter-parana').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-teal-700 hover:bg-teal-900');
         $('#filter-parana-text').text('PR');
         $('#filter-parana-icon').addClass('hidden');
         
         $('#filter-rio-grande-do-sul').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-cyan-700 hover:bg-cyan-900');
         $('#filter-rio-grande-do-sul-text').text('RS');
         $('#filter-rio-grande-do-sul-icon').addClass('hidden');
         
         $('#filter-santa-catarina').removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-sky-700 hover:bg-sky-900');
         $('#filter-santa-catarina-text').text('SC');
         $('#filter-santa-catarina-icon').addClass('hidden');
     }

     // Function to load filter options
     function loadFilterOptions() {
         // Load estados
         $.ajax({
             url: '/leads8/customer/ajax',
             type: 'POST',
             data: { action: 'get_estados' },
             success: function(response) {
                 if (response.data) {
                     var estadoSelect = $('#filter-estado');
                     response.data.forEach(function(estado) {
                         estadoSelect.append('<option value="' + estado + '">' + estado + '</option>');
                     });
                 }
             }
         });

         // Load vendedores
         $.ajax({
             url: '/leads8/customer/ajax',
             type: 'POST',
             data: { action: 'get_vendedores' },
             success: function(response) {
                 if (response.data) {
                     var vendedorSelect = $('#filter-vendedor');
                     response.data.forEach(function(vendedor) {
                         vendedorSelect.append('<option value="' + vendedor + '">' + vendedor + '</option>');
                     });
                 }
             }
         });

         // Load RFM values
         $.ajax({
              url: '/leads8/customer/ajax',
              type: 'POST',
              data: { action: 'get_rfm' },
             success: function(response) {
                 if (response.data) {
                     var rfmSelect = $('#filter-rfm');
                     response.data.forEach(function(rfm) {
                         rfmSelect.append('<option value="' + rfm + '">' + rfm + '</option>');
                     });
                 }
             }
         });
     }
     

     

     

     
     // Segmento Filter Handlers
     $('#filter-segmento-machines').click(function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-blue-500 hover:bg-blue-700');
             $('#filter-segmento-machines-text').text('🏭 Machines');
             $('#filter-segmento-machines-icon').addClass('hidden');
         } else {
             $(this).addClass('active bg-green-600 hover:bg-green-700').removeClass('bg-blue-500 hover:bg-blue-700');
             $('#filter-segmento-machines-text').text('🏭 Machines ✓');
             $('#filter-segmento-machines-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });
     
     $('#filter-segmento-bearings').click(function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-green-500 hover:bg-green-700');
             $('#filter-segmento-bearings-text').text('⚙️ Bearings');
             $('#filter-segmento-bearings-icon').addClass('hidden');
         } else {
             $(this).addClass('active bg-green-600 hover:bg-green-700').removeClass('bg-green-500 hover:bg-green-700');
             $('#filter-segmento-bearings-text').text('⚙️ Bearings ✓');
             $('#filter-segmento-bearings-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });
     
     $('#filter-segmento-auto').click(function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-red-500 hover:bg-red-700');
             $('#filter-segmento-auto-text').text('🚗 Auto');
             $('#filter-segmento-auto-icon').addClass('hidden');
         } else {
             $(this).addClass('active bg-green-600 hover:bg-green-700').removeClass('bg-red-500 hover:bg-red-700');
             $('#filter-segmento-auto-text').text('🚗 Auto ✓');
             $('#filter-segmento-auto-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });
     
     $('#filter-segmento-parts').click(function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-yellow-500 hover:bg-yellow-700');
             $('#filter-segmento-parts-text').text('🔧 Parts');
             $('#filter-segmento-parts-icon').addClass('hidden');
         } else {
             $(this).addClass('active bg-green-600 hover:bg-green-700').removeClass('bg-yellow-500 hover:bg-yellow-700');
             $('#filter-segmento-parts-text').text('🔧 Parts ✓');
             $('#filter-segmento-parts-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });
     
     $('#filter-segmento-faucets').click(function() {
         if ($(this).hasClass('active')) {
             $(this).removeClass('active bg-green-600 hover:bg-green-700').addClass('bg-indigo-500 hover:bg-indigo-700');
             $('#filter-segmento-faucets-text').text('🚿 Faucets');
             $('#filter-segmento-faucets-icon').addClass('hidden');
         } else {
             $(this).addClass('active bg-green-600 hover:bg-green-700').removeClass('bg-indigo-500 hover:bg-indigo-700');
             $('#filter-segmento-faucets-text').text('🚿 Faucets ✓');
             $('#filter-segmento-faucets-icon').removeClass('hidden');
         }
         table.ajax.reload();
     });
     
     // Função para abrir detalhes do cliente em uma janela WinBox
     function openClienteDetails(clienteId, clienteNome) {
         // Usar URL completa para garantir que carregue
         const url = `https://dev.office.internut.com.br/K3/tip/vcustomer/${clienteId}/`;
         
         console.log('👤 Abrindo detalhes do Cliente:', clienteId);
         console.log('📍 URL:', url);
         
         // Bloquear scroll da página pai imediatamente
         document.body.style.overflow = 'hidden';
         document.documentElement.style.overflow = 'hidden';
         
         // Criar janela WinBox com configurações avançadas
         const winbox = new WinBox({
             title: `👤 Detalhes do Cliente: ${clienteNome}`,
             url: url,
             width: '90%',
             height: '85%',
             class: "iframe",    
             x: 'center',
             y: 'top',
             top: '5%',
             background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
             border: '3px',
             modal: true,
             minwidth: 1000,
             minheight: 700,
             maxwidth: '95%',
             maxheight: '90%',
             onclose: function() {
                 console.log(`Janela do Cliente ${clienteNome} fechada`);
                 // Reativar scroll da página pai
                 document.body.style.overflow = 'auto';
                 document.documentElement.style.overflow = 'auto';
             },
             onload: function() {
                 console.log(`✅ Conteúdo carregado para Cliente ${clienteNome}`);
             }
         });
         
         console.log(`👤 Janela WinBox criada para Cliente ${clienteNome}`);
         console.log(`📍 URL: ${url}`);
     }
 });
 </script>

<!-- WinBox.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/winbox@0.2.82/dist/winbox.bundle.min.js"></script>

<script>
// Função para abrir detalhes do cliente em WinBox
function openClienteDetails(clientePoid, clienteNome) {
    // Desabilitar scroll da página pai
    document.body.style.overflow = 'hidden';
    
    const winbox = new WinBox({
        title: `Detalhes do Cliente: ${clienteNome}`,
        url: `https://dev.office.internut.com.br/K3/tip/vcustomer/${clientePoid}/`,
        width: '90%',
        height: '90%',
        x: 'center',
        y: 'center',
        modal: true,
        class: ['modal'],
        onclose: function() {
            // Reativar scroll da página pai quando fechar
            document.body.style.overflow = 'auto';
        }
    });
}
</script>

<!-- CSS para WinBox -->
<style>
/* Estilos personalizados para WinBox */
.winbox {
    border-radius: 8px !important;
    overflow: visible !important;
    z-index: 99999 !important;
    position: fixed !important;
    display: flex !important;
    flex-direction: column !important;
}

.wb-header {
    z-index: 100000 !important;
    position: relative !important;
    height: 35px !important;
    min-height: 35px !important;
    flex-shrink: 0 !important;
    order: -1 !important;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    font-weight: 600 !important;
    cursor: move !important;
    user-select: none !important;
}

.wb-control {
    z-index: 100001 !important;
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    gap: 2px !important;
}

.wb-title {
    z-index: 100000 !important;
    position: relative !important;
}

.wb-body {
    overflow: hidden !important;
    z-index: 99998 !important;
    position: relative !important;
    margin-top: 0 !important;
    flex: 1 !important;
    min-height: 0 !important;
    isolation: isolate !important;
    background: #f8fafc !important;
    border-radius: 0 0 8px 8px !important;
    padding: 0 !important;
}

.wb-body iframe {
    z-index: 99997 !important;
    width: 100% !important;
    height: 100% !important;
    border: none !important;
    background: white !important;
    overflow: auto !important;
    display: block !important;
}

.winbox.modal {
    z-index: 99999 !important;
}
</style>

</body>
</html>