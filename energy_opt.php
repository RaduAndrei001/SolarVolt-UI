<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php?redirect=energy_opt.php");
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

// Selectează panoul dorit (default primul)
$panel_id = isset($_GET['panel_id']) ? intval($_GET['panel_id']) : ($panels[0]['id'] ?? 1);
$tableName = "solar_data_panel_" . intval($panel_id);

// Verifică dacă tabela există
$tableExists = false;
$check = $conn->query("SHOW TABLES LIKE '$tableName'");
if ($check && $check->num_rows > 0) {
    $tableExists = true;
}

$battery = 0;
if ($tableExists) {
    $sql = "SELECT battery_voltage FROM `$tableName` ORDER BY timestamp DESC LIMIT 1";
    $res = $conn->query($sql);
    $row = $res ? $res->fetch_assoc() : null;
    $battery = $row ? $row['battery_voltage'] : 0;
}
$conn->close();

$consumer_status = ($battery > 3.7) ? "PORNEȘTE consumatorii" : "OPREȘTE consumatorii";
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Optimizare Energetică</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <style>
        body { background: #181c24; color: #e0e0e0; font-family: Arial, Helvetica, sans-serif; text-align: center; }
        .container { width: 90%; margin: auto; padding: 30px 20px; background: #232837; border-radius: 10px; box-shadow: 0 0 20px #111; margin-top: 40px; }
        .status { font-size: 1.5rem; margin: 30px 0; color: #61dafb; }
        .admin-btn { padding: 12px 28px; background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%); color: #fff; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; font-weight: bold; font-size: 1.1rem; box-shadow: 0 2px 8px #007bff44; transition: background 0.2s, transform 0.1s; }
        .admin-btn:hover { background: linear-gradient(90deg, #0056b3 0%, #007bff 100%); transform: translateY(-2px) scale(1.03); }
        .panel-select-form { margin-bottom: 25px; }
        select { padding: 6px 12px; border-radius: 6px; }
        button { padding: 6px 18px; border-radius: 6px; background: #007bff; color: #fff; border: none; font-weight: bold; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <a class="admin-btn" href="admin.php">&larr; Înapoi la administrare</a>
    <h2>Optimizare Energetică</h2>
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
    <div class="status">
        Bateria panoului <?= $panel_id ?>: <b><?= number_format($battery,2) ?> V</b><br>
        <span><?= $consumer_status ?></span>
    </div>
    <?php endif; ?>
    <p>(Logica poate fi extinsă pentru a trimite comenzi automate către dispozitive IoT.)</p>
</div>
</body>
</html>
