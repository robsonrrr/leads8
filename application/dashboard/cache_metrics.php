<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Métricas de Cache</title>
    <link href="/leads8/assets/css/tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-tachometer-alt text-blue-600 mr-3"></i>
                        Dashboard - Métricas de Cache
                    </h1>
                    <p class="text-gray-600">Monitoramento e controle do sistema de cache</p>
                    <p class="text-sm text-gray-500 mt-1">Última atualização: <?= $today ?></p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="refreshMetrics()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                        <i class="fas fa-sync-alt mr-2"></i>Atualizar
                    </button>
                    <button onclick="clearCache()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i>Limpar Cache
                    </button>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <div id="alert-container" class="mb-6"></div>

        <?php if (isset($cache_metrics['error'])): ?>
        <!-- Erro -->
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <div class="flex">
                <div class="py-1"><i class="fas fa-exclamation-triangle mr-2"></i></div>
                <div>
                    <p class="font-bold">Erro ao carregar métricas</p>
                    <p class="text-sm"><?= htmlspecialchars($cache_metrics['error']) ?></p>
                </div>
            </div>
        </div>
        <?php else: ?>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Cache de Arquivo -->
            <?php if (isset($cache_metrics['file_cache'])): ?>
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-xl shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium uppercase tracking-wide">Cache de Arquivo</p>
                        <p class="text-3xl font-bold"><?= $cache_metrics['file_cache']['size'] ?></p>
                        <p class="text-blue-100 text-sm"><?= $cache_metrics['file_cache']['files_count'] ?> arquivos</p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-file text-2xl"></i>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Cache de Memória -->
            <?php if (isset($cache_metrics['memory_cache'])): ?>
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-xl shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium uppercase tracking-wide">Cache de Memória</p>
                        <p class="text-3xl font-bold"><?= $cache_metrics['memory_cache']['status'] ?></p>
                        <p class="text-green-100 text-sm"><?= $cache_metrics['memory_cache']['info'] ?></p>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-memory text-2xl"></i>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Performance -->
            <?php if (isset($cache_metrics['performance']) && $cache_metrics['performance']['status'] === 'ok'): ?>
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-xl shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium uppercase tracking-wide">Performance</p>
                        <p class="text-3xl font-bold"><?= $cache_metrics['performance']['total_time_ms'] ?>ms</p>
                        <p class="text-purple-100 text-sm">Tempo total de teste</p>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-stopwatch text-2xl"></i>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Status Geral -->
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6 rounded-xl shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium uppercase tracking-wide">Status Geral</p>
                        <p class="text-3xl font-bold">Ativo</p>
                        <p class="text-orange-100 text-sm">Sistema funcionando</p>
                    </div>
                    <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <!-- JavaScript -->
    <script>
        function refreshMetrics() {
            showAlert('Atualizando métricas...', 'info');
            window.location.reload();
        }

        function clearCache() {
            if (confirm('Tem certeza que deseja limpar todo o cache?')) {
                showAlert('Limpando cache...', 'info');
                
                fetch('/dashboard/clear_cache', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'}
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    showAlert('Erro: ' + error.message, 'error');
                });
            }
        }

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            const colors = {
                'success': 'bg-green-100 border-green-400 text-green-700',
                'error': 'bg-red-100 border-red-400 text-red-700',
                'info': 'bg-blue-100 border-blue-400 text-blue-700'
            };
            
            alertContainer.innerHTML = `
                <div class="${colors[type]} px-4 py-3 rounded border mb-4">
                    <div class="flex justify-between">
                        <p class="font-bold">${message}</p>
                        <button onclick="this.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            setTimeout(() => {
                const alert = alertContainer.querySelector('.border');
                if (alert) alert.remove();
            }, 5000);
        }
    </script>
</body>
</html>