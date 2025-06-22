<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php?redirect=history.php");
    exit;
}
$servername = "localhost";
$username   = "esp32_user";
$password   = "parola_sigura";
$database   = "esp32_data";
$conn = new mysqli($servername, $username, $password, $database);

// Obține lista panouri pentru select
$panels = [];
$res = $conn->query("SELECT id, name FROM solar_panels");
while ($row = $res->fetch_assoc()) {
    $panels[] = $row;
}

// Selectează panoul (tabela) dorit
$panel_id = isset($_GET['panel_id']) ? intval($_GET['panel_id']) : ($panels[0]['id'] ?? 1);
$tableName = "solar_data_panel_" . intval($panel_id);

// Verifică dacă tabela există
$tableExists = false;
$check = $conn->query("SHOW TABLES LIKE '$tableName'");
if ($check && $check->num_rows > 0) {
    $tableExists = true;
}

$labels = [];
$light = [];
$batt = [];
if ($tableExists) {
    $sql = "SELECT DATE(timestamp) as day, AVG(light_voltage) as avg_light, AVG(battery_voltage) as avg_batt
            FROM `$tableName` GROUP BY day ORDER BY day ASC";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $labels[] = $row['day'];
        $light[] = round($row['avg_light'], 2);
        $batt[] = round($row['avg_batt'], 2);
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Istoric Multi-anual Panou</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1"></script>
    <style>
        body { background: #181c24; color: #e0e0e0; font-family: Arial, Helvetica, sans-serif; text-align: center; }
        .container { width: 90%; margin: auto; padding: 30px 20px; background: #232837; border-radius: 10px; box-shadow: 0 0 20px #111; margin-top: 40px; }
        .fancy-back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 12px 32px;
            background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 2px 12px #007bff44;
            text-decoration: none;
            transition: background 0.2s, transform 0.1s, color 0.2s;
            position: relative;
            overflow: hidden;
        }
        .fancy-back-btn:hover {
            background: linear-gradient(90deg, #0056b3 0%, #007bff 100%);
            color: #fff;
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 4px 24px #00c6ff66;
        }
        .panel-select-form { margin-bottom: 25px; }
        select { padding: 6px 12px; border-radius: 6px; }
        button { padding: 6px 18px; border-radius: 6px; background: #007bff; color: #fff; border: none; font-weight: bold; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <a class="fancy-back-btn" href="admin.php">&larr; Înapoi la administrare</a>
    <h2>Istoric Multi-anual Panou <?= htmlspecialchars($panel_id) ?></h2>
    <form class="panel-select-form" method="get">
        <label for="panel_id">Alege panou:</label>
        <select name="panel_id" id="panel_id">
            <?php foreach ($panels as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id']==$panel_id?'selected':'' ?>>
                    <?= htmlspecialchars($p['name']) ?> (ID: <?= $p['id'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Afișează</button>
    </form>
    <?php if (!$tableExists): ?>
        <div style="color:#ff6b6b; font-weight:bold; margin:20px 0;">
            Nu există date pentru acest panou!
        </div>
    <?php else: ?>
    <canvas id="historyChart"></canvas>
    <?php endif; ?>
</div>
<?php if ($tableExists): ?>
<script>
const labels = <?= json_encode($labels) ?>;
const light = <?= json_encode($light) ?>;
const batt = <?= json_encode($batt) ?>;
const ctx = document.getElementById('historyChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            { label: 'Lumină (V)', data: light, borderColor: 'orange', fill: false, tension: 0.3 },
            { label: 'Baterie (V)', data: batt, borderColor: '#61dafb', fill: false, tension: 0.3 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { x: { title: { display: true, text: 'Zi' } }, y: { beginAtZero: true } }
    }
});
</script>
<?php endif; ?>
</body>
</html>
