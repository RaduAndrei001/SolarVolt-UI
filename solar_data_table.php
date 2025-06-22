<?php
$servername = "localhost";
$username   = "esp32_user";
$password   = "parola_sigura";
$database   = "esp32_data";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    exit("Conexiune eșuată: " . $conn->connect_error);
}

// Obține lista panouri pentru select
$panels = [];
$res = $conn->query("SELECT id, name FROM solar_panels");
while ($row = $res->fetch_assoc()) {
    $panels[] = $row;
}

$panel_id = isset($_GET['panel_id']) ? intval($_GET['panel_id']) : ($panels[0]['id'] ?? 1);
$tableName = "solar_data_panel_" . intval($panel_id);

$sql = "SELECT id, light_voltage, battery_voltage, predicted_battery, timestamp FROM `$tableName` ORDER BY timestamp DESC LIMIT 100";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Date panou #<?php echo $panel_id; ?></title>
    <style>
        body { background: #181c24; color: #e0e0e0; font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 90%; margin: 30px auto; background: #232837; }
        th, td { border: 1px solid #444; padding: 8px 12px; text-align: center; }
        th { background: #007bff; color: #fff; }
        tr:nth-child(even) { background: #222633; }
        caption { margin-bottom: 15px; font-size: 1.2em; color: #61dafb; }
        .panel-select-form { margin: 20px auto; text-align: center; }
        select { padding: 6px 12px; border-radius: 6px; }
        button { padding: 6px 18px; border-radius: 6px; background: #007bff; color: #fff; border: none; font-weight: bold; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
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
    <table>
        <caption>Date panou #<?php echo $panel_id; ?></caption>
        <tr>
            <th>ID</th>
            <th>Lumină [V]</th>
            <th>Baterie [V]</th>
            <th>Timp</th>
        </tr>
        <?php if ($res && $res->num_rows > 0): while ($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['light_voltage']; ?></td>
            <td><?php echo $row['battery_voltage']; ?></td>
            <td><?php echo $row['timestamp']; ?></td>
        </tr>
        <?php endwhile; endif; ?>
    </table>
</body>
</html>
<?php
$conn->close();
?>
