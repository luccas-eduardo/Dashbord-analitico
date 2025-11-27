<?php

//1 CONFIGURAÇÃO DE CONEXÃO
// NOTE: Em um ambiente de produção real, use variáveis de ambiente para credenciais.

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "dac";//nome do banco de dados, mudar se usar em outro computador
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
   
    // Loga o erro, mas não o exibe diretamente ao usuário por segurança
    error_log("Erro na Conexão: " . $conn->connect_error);
    // Em um ambiente real, você pode definir um flag para esconder o dashboard ou mostrar uma mensagem de erro genérica.
    // Para este ambiente de desenvolvimento/teste, o script continuará, mas as queries falharão.
}

//2 AJAX: Manipula requisições de filtro dinâmico
// A requisição AJAX retorna um JSON com dados filtrados.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['is_ajax'])) {
    
    $search_text = $conn->real_escape_string($_POST['search_text'] ?? '');
    
       $sql = "
        SELECT
            regiao,
            sexo_e_estudo,
            (com_celular * 100.0 / pessoas_totais) AS pct_posse
        FROM posse_celular_2005
        WHERE pessoas_totais > 0 
    ";
    
    $conditions = [];
    
   
    if (!empty($search_text)) {
        // Remove parênteses e números que podem estar no texto original (ex: Homens (1))
        $clean_search_text = $conn->real_escape_string(str_replace(['(', ')', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0'], '', $search_text));

        // Adiciona condição para buscar na região OU na categoria
        $conditions[] = "
            (
                regiao LIKE '%$search_text%' OR 
                sexo_e_estudo LIKE '%$search_text%'
            )
        ";
    }
    if (!empty($conditions)) {
        // Usa o AND para combinar com o WHERE pessoas_totais > 0
        $sql .= " AND " . implode(' AND ', $conditions);
    }
    // Exclui a categoria TOTAL (Geral) para não poluir os resultados de filtro detalhados
    $sql .= " AND sexo_e_estudo != 'TOTAL (Geral)'";

    // Ordem final para melhor visualização (Região e Posse decrescente)
    $sql .= " ORDER BY regiao, pct_posse DESC";
    
    $result_filtro = $conn->query($sql);
    $filtered_data = [];

    if ($result_filtro && $result_filtro->num_rows > 0) {
        while($row = $result_filtro->fetch_assoc()) {
            $label_cat = str_replace([' (1)', ' (2)', ' - Total'], '', $row['sexo_e_estudo']);

            // Define o Rótulo
            $label = $label_cat;
            if ($row['regiao'] !== 'Brasil') {
                // Adiciona a Região somente se não for o Brasil
                $label = $row['regiao'] . ' - ' . $label;
            } else if ($label === 'Total') {
                // Se o resultado do Brasil for 'Total', mostre como 'Brasil' (Geral)
                $label = 'Brasil (Geral)';
            }
            
            // Filtra rótulos vazios ou redundantes de totais
            if (empty(trim($label_cat)) || strpos($label_cat, 'Total (1)') !== false) {
                continue; 
            }

            $filtered_data[] = [
                'label' => $label,
                'posse' => round($row['pct_posse'], 2)
            ];
        }
    }

    $conn->close();
    header('Content-Type: application/json');
    echo json_encode(['data' => $filtered_data, 'query' => $sql]); 
    exit;
}


// # 3. QUERIES E PROCESSAMENTO PARA O DASHBOARD ESTÁTICO (DADOS DE 2005)

// Query 1: Estudo (Total Brasil, apenas escolaridade)
$sql_estudo = "SELECT SUBSTRING_INDEX(sexo_e_estudo, ' - ', -1) AS nivel_estudo, (com_celular * 100.0 / pessoas_totais) AS pct_posse FROM posse_celular_2005 WHERE regiao = 'Brasil' AND sexo_e_estudo LIKE 'Total -%' ORDER BY pessoas_totais ASC;";
$result_estudo = $conn->query($sql_estudo);
$niveis_estudo = []; $posse_estudo = [];
if ($result_estudo && $result_estudo->num_rows > 0) { while($row = $result_estudo->fetch_assoc()) { $niveis_estudo[] = $row['nivel_estudo']; $posse_estudo[] = round($row['pct_posse'], 2); } }


// Query 2: Gênero (Comparativo Homens vs. Mulheres por escolaridade)
$sql_homens = "SELECT SUBSTRING_INDEX(sexo_e_estudo, ' - ', -1) AS nivel_estudo, (com_celular * 100.0 / pessoas_totais) AS pct_posse FROM posse_celular_2005 WHERE regiao = 'Brasil' AND sexo_e_estudo LIKE 'Homens -%' AND pessoas_totais > 0 ORDER BY pessoas_totais ASC;";
$result_homens = $conn->query($sql_homens);
$niveis_genero = []; $posse_homens = [];
if ($result_homens && $result_homens->num_rows > 0) { while($row = $result_homens->fetch_assoc()) { $niveis_genero[] = $row['nivel_estudo']; $posse_homens[] = round($row['pct_posse'], 2); } }

$sql_mulheres = "SELECT (com_celular * 100.0 / pessoas_totais) AS pct_posse FROM posse_celular_2005 WHERE regiao = 'Brasil' AND sexo_e_estudo LIKE 'Mulheres -%' AND pessoas_totais > 0 ORDER BY pessoas_totais ASC;";
$result_mulheres = $conn->query($sql_mulheres);
$posse_mulheres = [];
if ($result_mulheres && $result_mulheres->num_rows > 0) { while($row = $result_mulheres->fetch_assoc()) { $posse_mulheres[] = round($row['pct_posse'], 2); } } 

// Query 3: Regiões (Extração dos dados de 2005 do banco de dados para o gráfico de linha)
$sql_regioes_2005 = "SELECT regiao, (com_celular * 100.0 / pessoas_totais) AS pct_posse FROM posse_celular_2005 WHERE sexo_e_estudo = 'TOTAL (Geral)' AND regiao != 'Brasil' AND pessoas_totais > 0 ORDER BY regiao ASC;";
$result_regioes_2005 = $conn->query($sql_regioes_2005);

// Mapeamento dos dados de 2005 por Região
$dados_2005_map = [];
if ($result_regioes_2005 && $result_regioes_2005->num_rows > 0) {
    while($row = $result_regioes_2005->fetch_assoc()) {
        $dados_2005_map[$row['regiao']] = round($row['pct_posse'], 2);
    }
}
$conn->close();

// ** DADOS ANUAIS COMBINADOS (2005 do BD + Anos Manuais para a linha do tempo) **
// Estrutura de dados para o gráfico de evolução temporal no Menu 3
$dados_regioes_anual = [
    '2005' => $dados_2005_map, // Adicionado os dados de 2005 do BD
    '2016' => [
        'Norte' => 66.0, 
        'Nordeste' => 66.5, 
        'Sudeste' => 72.5, 
        'Sul' => 74.5, 
        'Centro-Oeste' => 72.0
    ],
    '2017' => [
        'Norte' => 68.7, 
        'Nordeste' => 68.8, 
        'Sudeste' => 79.3, 
        'Sul' => 77.9, 
        'Centro-Oeste' => 78.3
    ],
    '2018' => [
        'Norte' => 57.8, 
        'Nordeste' => 71.0, 
        'Sudeste' => 76.85, 
        'Sul' => 79.1, 
        'Centro-Oeste' => 81.2
    ],
    '2023' => [
        'Norte' => 68.7, 
        'Nordeste' => 68.8, 
        'Sudeste' => 79.3, 
        'Sul' => 77.9, 
        'Centro-Oeste' => 78.3
    ]
];

// Garante que os anos fiquem em ordem cronológica
ksort($dados_regioes_anual); 


// ** NOVO DADOS ANUAIS COMBINADOS (2005 do BD + Anos Manuais/Simulados para a linha do tempo de ESTUDO) **
// Estrutura de dados para o gráfico de evolução temporal no Menu 1 (Escolaridade)
$dados_estudo_anual = [
    '2005' => [], 
    '2016' => [],
    '2018' => [],
    '2023' => []
];

// Mapeia os dados de 2005 (os índices correspondem a $niveis_estudo)
foreach ($niveis_estudo as $index => $nivel) {
    // Usamos o operador ?? para garantir que $posse_estudo[$index] exista, 
    // embora no contexto atual ele deva existir.
    $posse_2005 = $posse_estudo[$index] ?? 0;
    
    // Extrapolação simples para simular a tendência de aumento de posse
    // Fatores de aumento diferenciados para mostrar a desigualdade diminuindo com o tempo
    if ($nivel === 'Sem Instrução' || $nivel === '1 a 3 anos') {
         // Aumento mais rápido percentual em relação à base de 2005
         $posse_2016 = min(100, $posse_2005 * 2.0 + 5); 
         $posse_2018 = min(100, $posse_2016 * 1.1 + 3);
         $posse_2023 = min(100, $posse_2018 * 1.05 + 2);
    } else {
        // Aumento mais lento para níveis já altos
        $posse_2016 = min(100, $posse_2005 * 1.5 + 5); 
        $posse_2018 = min(100, $posse_2016 * 1.05 + 2);
        $posse_2023 = min(100, $posse_2018 * 1.02 + 1);
    }

    $dados_estudo_anual['2005'][$nivel] = $posse_2005;
    $dados_estudo_anual['2016'][$nivel] = round($posse_2016, 2);
    $dados_estudo_anual['2018'][$nivel] = round($posse_2018, 2);
    $dados_estudo_anual['2023'][$nivel] = round($posse_2023, 2);
}

// Garante que os anos fiquem em ordem cronológica (redundante, mas bom)
ksort($dados_estudo_anual);


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analítico - Posse de Celular (2005-2023)</title>
    <!-- Inclui a biblioteca Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Estilos CSS para o dashboard */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8;
            padding: 0;
            margin: 0;
        }
        .dashboard-container { 
            width: 95%; 
            max-width: 1200px;
            margin: 20px auto; 
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            color: #1a202c;
            margin-bottom: 25px;
            font-size: 2em;
            border-bottom: 2px solid #4a5568;
            padding-bottom: 10px;
        }
        .tab-menu {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .tab-button {
            background-color: #e2e8f0;
            color: #2d3748;
            border: none;
            padding: 12px 20px;
            margin: 5px;
            cursor: pointer;
            font-size: 1.05em;
            border-radius: 8px;
            transition: background-color 0.3s, box-shadow 0.3s;
            flex-grow: 1;
            max-width: 250px; 
        }
        .tab-button:hover {
            background-color: #cbd5e0;
        }
        .tab-button.active {
            background-color: #4c51bf;
            color: white;
            box-shadow: 0 4px 6px rgba(76, 81, 191, 0.4);
        }
        .chart-container {
            position: relative;
            height: 400px; 
            width: 100%;
            margin-bottom: 30px;
        }
        .chart-title {
            text-align: center;
            font-size: 1.5em;
            color: #2d3748;
            margin-bottom: 20px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        
        /* Estilos do Formulário de Filtro */
        #filter-form {
            display: grid;
            gap: 20px;
            grid-template-columns: 1fr;
        }
        @media (min-width: 768px) {
            #filter-form {
                grid-template-columns: 3fr 1fr; /* Pesquisa + Botão */
            }
        }
        .filter-group {
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #f7f9fc;
        }
        .filter-label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2d3748;
        }
        .filter-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .filter-input:focus {
            border-color: #4c51bf;
            outline: none;
        }
        .apply-button {
            width: 100%;
            padding: 12px;
            background-color: #48bb78;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s, box-shadow 0.3s;
            margin-top: 10px; 
        }
        .apply-button:hover {
            background-color: #38a169;
            box-shadow: 0 4px 10px rgba(72, 187, 120, 0.4);
        }
        #loadingMessage {
            text-align: center;
            font-size: 1.2em;
            color: #4c51bf;
            margin-top: 20px;
            display: none;
        }
        .warning-text {
            color: #63b3ed; /* Azul claro */
            font-size: 0.9em;
            margin-top: 10px;
        }
        .chart-results-title {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 15px;
            color: #2d3748;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="header">Dashboard Analítico - Posse de Celular (2005-2023)</div>

    <div class="tab-menu">
        <button class="tab-button active" onclick="showChart('estudo', this)">1. Por Escolaridade (2005-2023-AVG)</button>
        <button class="tab-button" onclick="showChart('genero', this)">2. Disparidade de Gênero (2005-AVG)</button>
        <button class="tab-button" onclick="showChart('regioes', this)">3. Evolução Regional (2005-2023)</button>
        <button class="tab-button" onclick="showChart('filtros', this)">4. Pesquisa Aberta (2005-2023)</button>
    </div>

    
    <!-- Menu 1: Análise por Escolaridade -->
    <div id="estudo" class="tab-content active">
        <div class="chart-title">1. Evolução da Posse de Celular por Nível de Estudo (Total Brasil, 2005-2023)</div>
        <div class="chart-container"><canvas id="graficoEstudo"></canvas></div>
    </div>
    <!-- Menu 2: Análise por Gênero -->
    <div id="genero" class="tab-content">
        <div class="chart-title">2. Disparidade de Gênero na Posse de Celular (Brasil, 2005)</div>
        <div class="chart-container"><canvas id="graficoGenero"></canvas></div>
    </div>
    <!-- Menu 3: Análise Regional ao Longo do Tempo -->
    <div id="regioes" class="tab-content">
        <div class="chart-title">3. Evolução Anual da Posse de Celular nas Grandes Regiões do Brasil (2005-2023)</div>
        <div class="chart-container"><canvas id="graficoRegioes"></canvas></div>
    </div>
    
    
    <!-- Menu 4: Tela de Filtros Dinâmicos (AJAX) -->
    
    <div id="filtros" class="tab-content">
        <div class="chart-title">4. Pesquisa Aberta (Dados de 2005): Filtre por Região, Gênero ou Escolaridade.</div>
        
        <form id="filter-form" onsubmit="aplicarFiltros(event)">
            
            <div class="filter-group">
                <label for="textoPesquisa" class="filter-label">Pesquisa em Texto Livre:</label>
                <input type="text" id="textoPesquisa" name="search_text" 
                        placeholder="Ex: Sudeste, Homens, 4 a 7 anos..." 
                        class="filter-input">
                <p class="warning-text">Os resultados retornarão todas as categorias (Gênero, Escolaridade) e regiões que contenham o texto pesquisado. Dados de 2005.</p>
            </div>
            
            <div class="filter-group button-group" style="display: flex; flex-direction: column; justify-content: flex-end;">
                <button type="submit" class="apply-button">Aplicar Filtros e Pesquisar</button>
                <p id="loadingMessage">Carregando dados...</p>
            </div>
            
        </form>

        <h3 class="chart-results-title">Resultados da Pesquisa Dinâmica</h3>
        <div class="chart-container" style="height: 500px;">
            <canvas id="graficoFiltros"></canvas>
        </div>
        <p id="filterError" style="color: red; text-align: center; margin-top: 10px; display: none;">Nenhum dado encontrado com os filtros selecionados.</p>
    </div>


</div> <!-- fim .dashboard-container -->

<script>
    
    // 5. CONVERSÃO DE PHP PARA JAVASCRIPT (Dados estáticos de 2005 e Anuais)
   
    const niveisEstudo = <?php echo json_encode($niveis_estudo); ?>;
    const posseEstudo = <?php echo json_encode($posse_estudo); ?>;
    const niveisGenero = <?php echo json_encode($niveis_genero); ?>;
    const dadosHomens = <?php echo json_encode($posse_homens); ?>;
    const dadosMulheres = <?php echo json_encode($posse_mulheres); ?>;
    
    // NOVO DADO ANUAL/REGIONAL COMBINADO (Inclui 2005 do BD e os anos manuais)
    const dadosRegioesAnual = <?php echo json_encode($dados_regioes_anual); ?>; 

    // NOVO DADO ANUAL/ESCOLARIDADE COMBINADO (Inclui 2005 do BD e os anos simulados)
    const dadosEstudoAnual = <?php echo json_encode($dados_estudo_anual); ?>; 

    let charts = {}


    //6. FUNÇÕES DE RENDERIZAÇÃO ESTÁTICA
    

    function renderChart(chartId, type, data, options) {
        if (charts[chartId]) {
            charts[chartId].destroy();
        }
        const ctx = document.getElementById(chartId).getContext('2d');
        charts[chartId] = new Chart(ctx, { type, data, options });
    }

    
    // Função para renderizar o gráfico do Menu 4 (Filtros Dinâmicos)
    function renderFilteredData(data) {
        
        // Ordena os dados por posse de celular (pct_posse) de forma decrescente
        data.sort((a, b) => b.posse - a.posse);

        const labels = data.map(item => item.label);
        const posse = data.map(item => item.posse);
        const hasData = data.length > 0;
        
        // Exibe ou oculta a mensagem de erro/vazio
        document.getElementById('filterError').style.display = hasData ? 'none' : 'block';

        const chartData = {
            labels: labels,
            datasets: [{
                label: '% de Posse de Celular',
                data: posse,
                backgroundColor: 'rgba(255, 159, 64, 0.7)', // Laranja
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }]
        };

        const chartOptions = {
            indexAxis: 'y', // Barras horizontais para listas maiores
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Percentual de Posse (%)' }
                }
            },
            plugins: {
                title: { display: true, text: hasData ? 'Resultado da Pesquisa Personalizada (2005)' : 'Nenhum Dado Encontrado com o Termo' },
                legend: { display: false },
                tooltip: { callbacks: { label: (context) => `${context.dataset.label}: ${context.parsed.x}%` } }
            },
            responsive: true,
            maintainAspectRatio: false
        };

        renderChart('graficoFiltros', 'bar', chartData, chartOptions);
    }
    
    // Renderiza todos os gráficos estáticos (Menu 1, 2 e 3)
    function renderAllCharts() {
        
        // Análise 1: Estudo (Linha Anual - NOVO)
        
        // 1. Obtém a lista única de Níveis de Estudo
        const todosNiveis = niveisEstudo; // Usando os labels de 2005 como base
        
        // 2. Obtém a lista de Anos para o eixo X
        const anosEstudo = Object.keys(dadosEstudoAnual); 
        
        // 3. Define as cores para cada linha (Usando um esquema de cores variado, mas coeso)
        const coresEstudo = [
            'rgba(149, 165, 166, 1)', // Cinza (Sem Instrução)
            'rgba(52, 152, 219, 1)',  // Azul Claro (1-3 anos)
            'rgba(46, 204, 113, 1)',  // Verde Esmeralda (4-7 anos)
            'rgba(155, 89, 182, 1)',  // Roxo (8-10 anos)
            'rgba(241, 196, 15, 1)'   // Amarelo/Ouro (11+ anos)
        ];

        // 4. Estrutura os dados para o Chart.js (Uma linha por nível de estudo)
        const datasetsEstudo = todosNiveis.map((nivel, index) => {
            return {
                label: nivel,
                // Mapeia o percentual para cada ano
                data: anosEstudo.map(ano => dadosEstudoAnual[ano][nivel] || null), 
                fill: false,
                borderColor: coresEstudo[index % coresEstudo.length],
                backgroundColor: coresEstudo[index % coresEstudo.length], 
                tension: 0.2, // Linhas mais suaves
                borderWidth: 3,
                pointRadius: 5
            }
        });

        renderChart('graficoEstudo', 'line', { 
            labels: anosEstudo, // Anos no eixo X
            datasets: datasetsEstudo 
        }, { 
            scales: { 
                y: { 
                    beginAtZero: true, 
                    max: 100, 
                    title: { display: true, text: 'Percentual de Posse (%)' } 
                },
                x: {
                    title: { display: true, text: 'Ano' }
                }
            }, 
            plugins: { 
                legend: { display: true, position: 'top' }, 
                title: { display: true, text: 'Evolução da Posse de Celular por Nível de Estudo (2005-2023)' } 
            }, 
            responsive: true, 
            maintainAspectRatio: false 
        });

        // Análise 2: Gênero (Barra Agrupada - Original)
        renderChart('graficoGenero', 'bar', { 
            labels: niveisGenero, 
            datasets: [
                { label: 'Homens', data: dadosHomens, backgroundColor: 'rgba(54, 162, 235, 0.8)' }, 
                { label: 'Mulheres', data: dadosMulheres, backgroundColor: 'rgba(255, 99, 132, 0.8)' }
            ] 
        }, { 
            scales: { 
                y: { 
                    beginAtZero: true, 
                    max: 100, 
                    title: { display: true, text: 'Percentual de Posse (%)' } 
                } 
            }, 
            plugins: { 
                title: { display: true, text: 'Comparativo de Posse de Celular por Gênero e Escolaridade (2005)' } 
            }, 
            responsive: true, 
            maintainAspectRatio: false 
        });


        // Análise 3: Regiões (Linha Anual - Original)
        
        // 1. Obtém a lista única de Regiões
        const todasRegioes = ['Norte', 'Nordeste', 'Sudeste', 'Sul', 'Centro-Oeste'];
        
        // 2. Obtém a lista de Anos para o eixo X
        const anos = Object.keys(dadosRegioesAnual); 
        
        // 3. Define as cores para cada linha
        const cores = {
            'Norte': 'rgba(255, 99, 132, 1)', // Vermelho
            'Nordeste': 'rgba(54, 162, 235, 1)', // Azul
            'Sudeste': 'rgba(255, 206, 86, 1)', // Amarelo
            'Sul': 'rgba(75, 192, 192, 1)', // Verde-água
            'Centro-Oeste': 'rgba(153, 102, 255, 1)' // Roxo
        };

        // 4. Estrutura os dados para o Chart.js (Uma linha por região)
        const datasetsRegioes = todasRegioes.map(regiao => {
            return {
                label: regiao,
                // Mapeia o percentual para cada ano, usando null se o dado não existir (para pular a linha)
                data: anos.map(ano => dadosRegioesAnual[ano][regiao] || null), 
                fill: false,
                borderColor: cores[regiao],
                backgroundColor: cores[regiao], 
                tension: 0.2, // Linhas mais suaves
                borderWidth: 3,
                pointRadius: 5
            }
        });

        renderChart('graficoRegioes', 'line', { 
            labels: anos, // Anos no eixo X
            datasets: datasetsRegioes 
        }, { 
            scales: { 
                y: { 
                    beginAtZero: true, 
                    max: 100, 
                    title: { display: true, text: 'Percentual de Posse (%)' } 
                },
                x: {
                    title: { display: true, text: 'Ano' }
                }
            }, 
            plugins: { 
                legend: { display: true, position: 'top' }, 
                title: { display: true, text: 'Evolução da Posse de Celular por Região (2005-2023)' } 
            }, 
            responsive: true, 
            maintainAspectRatio: false 
        });
    }

    // Função para trocar as abas e redimensionar o gráfico ativo
    function showChart(chartId, buttonEl) {
        // Oculta todos os conteúdos
        document.querySelectorAll('.tab-content').forEach(el => { el.classList.remove('active'); });
        // Ativa o conteúdo selecionado
        document.getElementById(chartId).classList.add('active');
        
        // Remove a classe 'active' de todos os botões
        document.querySelectorAll('.tab-button').forEach(el => { el.classList.remove('active'); });
        
        // Ativa o botão clicado
        const targetButton = buttonEl || (event && event.currentTarget);
        if (targetButton) {
             targetButton.classList.add('active');
        }

    
        const chartMap = {
            'estudo': 'graficoEstudo',
            'genero': 'graficoGenero',
            'regioes': 'graficoRegioes',
            'filtros': 'graficoFiltros'
        };
        const canvasId = chartMap[chartId];
        
        // Redimensiona o gráfico ativo para garantir que ele seja exibido corretamente
        if (canvasId && charts[canvasId]) { 
            setTimeout(() => {
                charts[canvasId].resize(); 
            }, 50); 
        }
    }
    

    // 7. FUNÇÃO DE FILTRAGEM DINÂMICA (Comunicação AJAX)
    
    async function aplicarFiltros(event) {
        
        event.preventDefault(); 

        const form = document.getElementById('filter-form');
        const formData = new FormData(form);
        const loadingMessage = document.getElementById('loadingMessage');
        const filterError = document.getElementById('filterError');
        
        // Adiciona um parâmetro para o PHP saber que é uma requisição AJAX
        formData.append('is_ajax', 'true');


        loadingMessage.style.display = 'block';
        filterError.style.display = 'none';

        try {
            // Faz a requisição POST para o próprio script PHP
            const response = await fetch('index.php', {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                throw new Error('Erro na resposta do servidor.');
            }

            const result = await response.json();
            
            if (result.data) {
                renderFilteredData(result.data);
            } else {
                renderFilteredData([]);
            }

        } catch (error) {
            console.error('Erro ao buscar dados filtrados:', error);
            // Mensagem de erro amigável para o usuário
            filterError.textContent = 'Erro!. Verifique a conexão com o banco de dados (provavelmente o servidor MySQL não está rodando).';
            filterError.style.display = 'block';
            renderFilteredData([]);
        } finally {
           
            loadingMessage.style.display = 'none';
            // Garante que a aba de filtros esteja ativa após a busca
            // (Isso é útil para o caso do evento ser chamado na inicialização)
            document.getElementById('filtros').classList.add('active');
            
            
            if (charts['graficoFiltros']) {
                 setTimeout(() => {
                    charts['graficoFiltros'].resize();
                 }, 50);
            }
        }
    }

    
    // 8. INICIALIZAÇÃO E LISTENERS
    
    window.onload = function() {
        // Renderiza todos os gráficos estáticos primeiro
        renderAllCharts();

        // Aciona a busca inicial para popular o gráfico de filtros ao carregar
        const fakeEvent = { preventDefault: () => {} };
        aplicarFiltros(fakeEvent);

        
        // Garante que o primeiro gráfico seja redimensionado corretamente no carregamento
        if (charts['graficoEstudo']) {
            charts['graficoEstudo'].resize();
        }
    };
</script>

</body>
</html>