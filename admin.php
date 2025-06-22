<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php?redirect=admin.php");
    exit;
}

/* ─────────── Conexiune BD ─────────── */
$servername = "localhost";
$username   = "esp32_user";
$password   = "parola_sigura";
$database   = "esp32_data";

$panels = [];
$db_error = null;

try {
    $conn = @new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception("Conexiune eșuată: " . $conn->connect_error);
    }

    // Citim lista panouri
    $res = @$conn->query("SELECT id, name, status, ip FROM solar_panels");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $panels[] = $row;
        }
    }
    $conn->close();
} catch (Exception $e) {
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Administrare ESP32 Data Logger</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            text-align: center;
            background: #181c24;
            color: #e0e0e0;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            background: #232837;
            border-radius: 10px;
            box-shadow: 0 0 20px #111;
            position: relative;
        }
        .btn-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
        }
        .add-panel-btn, .status-btn, .logout, .meteo-btn, .about-btn, .export-btn, .history-btn, .energy-btn {
            padding: 12px 28px;
            background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px #007bff44;
            transition: background 0.2s, transform 0.1s;
        }
        .add-panel-btn:hover, .status-btn:hover, .logout:hover, .meteo-btn:hover, .about-btn:hover, .export-btn:hover, .history-btn:hover, .energy-btn:hover {
            background: linear-gradient(90deg, #0056b3 0%, #007bff 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .charts-row {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            margin-bottom: 30px;
        }
        .chart-container {
            flex: 1;
            min-width: 0;
            background: #232837;
            padding: 20px 10px 10px 10px;
            border-radius: 16px;
            box-shadow: 0 0 20px #111;
            margin: 0 10px;
            transition: box-shadow 0.2s;
        }
        .chart-container:hover {
            box-shadow: 0 0 32px #007bff88;
        }
        canvas {
            max-width: 100%;
            height: 420px !important;
            margin: auto;
            display: block;
            background: #181c24;
            border-radius: 12px;
            box-shadow: 0 2px 12px #007bff22;
        }
        #data-table {
            display: none;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #232837;
            color: #e0e0e0;
        }
        th,td {
            border: 1px solid #333;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #181c24;
        }
        .toggle-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .toggle-btn:hover { background: #0056b3; }
        .panel-select {
            margin-bottom: 10px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            margin-bottom: 6px;
        }
        .export-btn, .history-btn, .energy-btn {
            padding: 12px 28px;
            background: linear-gradient(90deg, #232837 0%, #61dafb 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px #61dafb44;
            transition: background 0.2s, transform 0.1s;
            margin-left: 10px;
        }
        .export-btn:hover, .history-btn:hover, .energy-btn:hover {
            background: linear-gradient(90deg, #181c24 0%, #007bff 100%);
            transform: translateY(-2px) scale(1.03);
        }
    </style>
</head>
<body>
<div class="container">
    <?php if ($db_error): ?>
        <div style="color:#ff6b6b; font-weight:bold; margin:20px 0;">
            Eroare conexiune sau interogare bază de date:<br>
            <?= htmlspecialchars($db_error) ?>
        </div>
    <?php endif; ?>
    <div class="btn-row">
        <a class="add-panel-btn" href="add_panel.php">+ Adaugă Panou</a>
        <a class="status-btn" href="status.php">Status Panouri</a>
        <a class="meteo-btn" href="meteo.php">Influențe Meteo</a>
        <a class="history-btn" href="history.php">Istoric Multi-anual</a>
        <a class="export-btn" href="export.php">Export CSV</a>
        <a class="energy-btn" href="energy_opt.php">Optimizare Energetică</a>
        <a class="about-btn" href="homepage.php">Despre Panouri & Idei</a>
        <a class="logout" href="logout.php">Logout</a>
    </div>
    <h2>Status Panouri Solare</h2>
    <ul>
        <?php foreach ($panels as $panel): ?>
            <li>
                <?= htmlspecialchars($panel['name']) ?> (ID: <?= $panel['id'] ?>) -
                <?php
                    if ($panel['status'] === 'faulty') {
                        echo '<span style="color:red;font-weight:bold;">NEFUNCȚIONAL</span>';
                    } elseif ($panel['status'] === 'active') {
                        echo '<span style="color:#00ff99;font-weight:bold;">ACTIV</span>';
                    } elseif ($panel['status'] === 'inactive') {
                        echo '<span style="color:#ffb347;font-weight:bold;">INACTIV</span>';
                    } else {
                        echo '<span style="color:#ccc;">Status necunoscut</span>';
                    }
                ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if (in_array('faulty', array_column($panels, 'status'))): ?>
        <div style="color:red;font-weight:bold;">Atenție: Unul sau mai multe panouri sunt NEFUNCȚIONALE!</div>
    <?php endif; ?>

    <div class="charts-row">
        <div class="chart-container" style="height:520px; min-width:520px;">
            <div class="panel-select">
                <label for="panelSelect1">Panou pentru Grafic 1:</label>
                <select id="panelSelect1">
                    <?php foreach ($panels as $panel): ?>
                        <option value="<?= $panel['id'] ?>" data-ip="<?= htmlspecialchars($panel['ip']) ?>">
                            <?= htmlspecialchars($panel['name']) ?> (ID: <?= $panel['id'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <canvas id="voltageChart1" width="900" height="500"></canvas>
        </div>
        <div class="chart-container" style="height:520px; min-width:520px;">
            <div class="panel-select">
                <label for="panelSelect2">Panou pentru Grafic 2:</label>
                <select id="panelSelect2">
                    <?php foreach ($panels as $panel): ?>
                        <option value="<?= $panel['id'] ?>" data-ip="<?= htmlspecialchars($panel['ip']) ?>">
                            <?= htmlspecialchars($panel['name']) ?> (ID: <?= $panel['id'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <canvas id="voltageChart2" width="900" height="500"></canvas>
        </div>
    </div>
    <button onclick="window.open('solar_data_table.php?panel_id=' + selectedPanel1, '_blank')">Afișează tabel</button>
    <div id="panel-status" style="margin:10px 0; font-weight:bold; color:#fff;"></div>
</div>
<script>
function createGradient(ctx, color1, color2) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 500);
    gradient.addColorStop(0, color1);
    gradient.addColorStop(1, color2);
    return gradient;
}

let chart1, chart2;
let selectedPanel1 = "";
let selectedPanel2 = "";
let selectedPanelIp1 = "";
let selectedPanelIp2 = "";

function fetchData(panelId, chart) {
    fetch('get_data.php?panel_id=' + panelId)
        .then(r => r.json())
        .then(data => {
            let lightData   = data.map(r => r.light_voltage);
            let batteryData = data.map(r => r.battery_voltage);
            // Elimină predicția AI din grafice
            let labels      = data.map((_, i) => i + 1);

            if (chart) {
                chart.data.labels              = labels;
                chart.data.datasets[0].data    = lightData;
                chart.data.datasets[1].data    = batteryData;
                // Elimină datasetul pentru predicție AI
                chart.data.datasets.length = 2;
                chart.update();
            }
        })
        .catch(err => console.error('Eroare fetch:', err));
}

function updatePanelStatus(ip) {
    const statusDiv = document.getElementById('panel-status');
    if (!ip) {
        statusDiv.textContent = "Status: necunoscut";
        statusDiv.style.color = "#ccc";
        return;
    }
    fetch('ping.php?ip=' + encodeURIComponent(ip) + '&panel_id=' + selectedPanel1)
        .then(r => r.json())
        .then(obj => {
            // Statusul este actualizat în baza de date de ping.php, dar îl afișăm și aici
            if (obj.online) {
                statusDiv.textContent = "Status: ACTIV (ONLINE)";
                statusDiv.style.color = "#00ff00";
            } else {
                statusDiv.textContent = "Status: INACTIV (OFFLINE)";
                statusDiv.style.color = "#ff3333";
            }
        })
        .catch(() => {
            statusDiv.textContent = "Status: necunoscut";
            statusDiv.style.color = "#ccc";
        });
}

function onPanelSelected1() {
    const select = document.getElementById('panelSelect1');
    selectedPanel1 = select.value;
    selectedPanelIp1 = select.options[select.selectedIndex].getAttribute('data-ip');
    updatePanelStatus(selectedPanelIp1);
    fetchData(selectedPanel1, chart1);
}
function onPanelSelected2() {
    const select = document.getElementById('panelSelect2');
    selectedPanel2 = select.value;
    selectedPanelIp2 = select.options[select.selectedIndex].getAttribute('data-ip');
    fetchData(selectedPanel2, chart2);
}

window.onload = function () {
    const ctx1 = document.getElementById('voltageChart1').getContext('2d');
    const ctx2 = document.getElementById('voltageChart2').getContext('2d');
    const gradBlue = createGradient(ctx1, "#61dafb", "#232837");
    const gradGreen = createGradient(ctx1, "#00ff99", "#232837");
    const gradOrange = createGradient(ctx1, "#ffb347", "#232837");

    chart1 = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Lumină (V)',
                    data: [],
                    borderColor: gradBlue,
                    backgroundColor: gradBlue,
                    pointBackgroundColor: "#61dafb",
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 7
                },
                {
                    label: 'Baterie (V)',
                    data: [],
                    borderColor: gradGreen,
                    backgroundColor: gradGreen,
                    pointBackgroundColor: "#00ff99",
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 7
                }
            ]
        },
        options: {
            responsive: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { color: '#fff', font: { weight: 'bold' } } },
                tooltip: {
                    backgroundColor: '#232837',
                    borderColor: '#61dafb',
                    borderWidth: 1,
                    titleColor: '#61dafb',
                    bodyColor: '#fff',
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.formattedValue + ' V';
                        }
                    }
                }
            },
            animation: {
                duration: 900,
                easing: 'easeOutQuart'
            },
            scales: {
                x: {
                    title: { display: true, text: 'Index citire', color: '#61dafb', font: { weight: 'bold' } },
                    ticks: { color: '#b0e0ff' },
                    grid: { color: '#333' }
                },
                y: {
                    title: { display: true, text: 'Tensiune (V)', color: '#61dafb', font: { weight: 'bold' } },
                    beginAtZero: true,
                    ticks: { color: '#b0e0ff' },
                    grid: { color: '#333' }
                }
            }
        }
    });

    chart2 = new Chart(ctx2, {
        type: 'line',
        data: JSON.parse(JSON.stringify(chart1.data)),
        options: JSON.parse(JSON.stringify(chart1.options))
    });

    const panelSelect1 = document.getElementById('panelSelect1');
    const panelSelect2 = document.getElementById('panelSelect2');
    selectedPanel1 = panelSelect1.value;
    selectedPanelIp1 = panelSelect1.options[panelSelect1.selectedIndex].getAttribute('data-ip');
    selectedPanel2 = panelSelect2.value;
    selectedPanelIp2 = panelSelect2.options[panelSelect2.selectedIndex].getAttribute('data-ip');

    panelSelect1.addEventListener('change', onPanelSelected1);
    panelSelect2.addEventListener('change', onPanelSelected2);

    // Inițializare
    fetchData(selectedPanel1, chart1);
    fetchData(selectedPanel2, chart2);
    updatePanelStatus(selectedPanelIp1);

    setInterval(() => {
        fetchData(selectedPanel1, chart1);
        fetchData(selectedPanel2, chart2);
        updatePanelStatus(selectedPanelIp1);
    }, 10000);
};
</script>
</body>
</html>