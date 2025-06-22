<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php?redirect=meteo.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Influențe Meteo asupra Panourilor</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { background: #181c24; color: #e0e0e0; font-family: Arial, Helvetica, sans-serif; text-align: center; }
        .container { width: 80%; margin: auto; padding: 30px 20px; background: #232837; border-radius: 10px; box-shadow: 0 0 20px #111; margin-top: 40px; }
        .btn-row { display: flex; justify-content: flex-end; margin-bottom: 25px; }
        .back-btn { padding: 12px 28px; background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%); color: #fff; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; font-weight: bold; font-size: 1.1rem; box-shadow: 0 2px 8px #007bff44; transition: background 0.2s, transform 0.1s; }
        .back-btn:hover { background: linear-gradient(90deg, #0056b3 0%, #007bff 100%); transform: translateY(-2px) scale(1.03); }
        .info { margin-bottom: 30px; font-size: 1.1rem; color: #b0e0ff; }
        .chart-container {
            background: #232837;
            border-radius: 16px;
            box-shadow: 0 0 20px #007bff44;
            padding: 20px;
            margin-bottom: 30px;
            transition: box-shadow 0.2s;
        }
        .chart-container:hover {
            box-shadow: 0 0 32px #61dafb88;
        }
        canvas {
            max-width: 100%;
            height: 420px !important;
            margin: auto;
            display: block;
            background: #181c24;
            border-radius: 12px;
            box-shadow: 0 2px 12px #61dafb22;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="btn-row">
        <a class="back-btn" href="admin.php">&larr; Înapoi la administrare</a>
    </div>
    <h2>Influențe Meteo asupra Panourilor Solare</h2>
    <div class="info">
        <p>
            Datele meteo sunt colectate de un ESP32 separat și includ: <b>temperatură</b>, <b>condiție atmosferică</b>.<br>
            Graficul de mai jos se actualizează automat.
        </p>
    </div>
    <div class="chart-container">
        <canvas id="meteoChart"></canvas>
    </div>
    <div class="info">
        <p>
            Pentru corelare avansată, poți integra și predicția AI asupra influenței meteo.
        </p>
    </div>
</div>
<script>
let meteoChart;
function createGradient(ctx, color1, color2) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, color1);
    gradient.addColorStop(1, color2);
    return gradient;
}
function fetchMeteo() {
    fetch('get_meteo.php')
        .then(r => r.json())
        .then(data => {
            let labels = data.map(r => r.timestamp);
            let temp = data.map(r => r.temperature);
            let pres = data.map(r => r.pressure);
            // Elimină predicția AI din grafic
            // let predTemp = data.map(r => r.predicted_temperature ?? null);

            if (!meteoChart) {
                const ctx = document.getElementById('meteoChart').getContext('2d');
                const gradTemp = createGradient(ctx, "#ffb347", "#232837");
                const gradPres = createGradient(ctx, "#61dafb", "#232837");
                meteoChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Temperatură (°C)',
                                data: temp,
                                borderColor: gradTemp,
                                backgroundColor: gradTemp,
                                pointBackgroundColor: "#ffb347",
                                fill: false,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 7
                            },
                            {
                                label: 'Presiune (hPa)',
                                data: pres,
                                borderColor: gradPres,
                                backgroundColor: gradPres,
                                pointBackgroundColor: "#61dafb",
                                fill: false,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 7,
                                yAxisID: 'y2'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top', labels: { color: '#fff', font: { weight: 'bold' } } },
                            tooltip: {
                                backgroundColor: '#232837',
                                borderColor: '#61dafb',
                                borderWidth: 1,
                                titleColor: '#61dafb',
                                bodyColor: '#fff'
                            }
                        },
                        animation: {
                            duration: 900,
                            easing: 'easeOutQuart'
                        },
                        scales: {
                            y: {
                                title: { display: true, text: 'Temperatură (°C)', color: '#ffb347', font: { weight: 'bold' } },
                                beginAtZero: true,
                                ticks: { color: '#ffb347' },
                                grid: { color: '#333' }
                            },
                            y2: {
                                position: 'right',
                                title: { display: true, text: 'Presiune (hPa)', color: '#61dafb', font: { weight: 'bold' } },
                                beginAtZero: false,
                                ticks: { color: '#61dafb' },
                                grid: { drawOnChartArea: false }
                            },
                            x: {
                                ticks: { color: '#b0e0ff' },
                                grid: { color: '#333' }
                            }
                        }
                    }
                });
            } else {
                meteoChart.data.labels = labels;
                meteoChart.data.datasets[0].data = temp;
                meteoChart.data.datasets[1].data = pres;
                meteoChart.update();
            }
        });
}
window.onload = function() {
    fetchMeteo();
    setInterval(fetchMeteo, 5000);
};
</script>
</body>
</html>
