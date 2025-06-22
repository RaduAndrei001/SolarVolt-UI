<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php?redirect=export_csv.php");
    exit;
}

$servername = "localhost";
$username   = "esp32_user";
$password   = "parola_sigura";
$database   = "esp32_data";
$conn = new mysqli($servername, $username, $password, $database);

$panel_ids = isset($_GET['panel_ids']) ? $_GET['panel_ids'] : [];
if (!is_array($panel_ids) || count($panel_ids) == 0) {
    exit("Selectează cel puțin un panou!");
}

// Obține numele panourilor pentru fiecare id
$panel_names = [];
$in = implode(',', array_map('intval', $panel_ids));
$res = $conn->query("SELECT id, name FROM solar_panels WHERE id IN ($in)");
while ($row = $res->fetch_assoc()) {
    $panel_names[$row['id']] = $row['name'];
}

// Dacă e doar un panou, exportă CSV simplu
if (count($panel_ids) === 1) {
    $panel_id = intval($panel_ids[0]);
    $tableName = "solar_data_panel_" . $panel_id;
    $check = $conn->query("SHOW TABLES LIKE '$tableName'");
    if (!$check || $check->num_rows == 0) {
        $conn->close();
        exit("Tabelul nu există.");
    }
    $sql = "SELECT id, light_voltage, battery_voltage, timestamp FROM `$tableName` ORDER BY timestamp ASC";
    $res = $conn->query($sql);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="solar_data_panel_' . $panel_id . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Lumină [V]', 'Baterie [V]', 'Timp']);
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($out, [$row['id'], $row['light_voltage'], $row['battery_voltage'], $row['timestamp']]);
        }
    }
    fclose($out);
    $conn->close();
    exit;
}

// Dacă sunt mai multe panouri, exportă CSV cu delimitator între panouri
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="solar_data_export.csv"');
$out = fopen('php://output', 'w');

foreach ($panel_ids as $panel_id) {
    $panel_id = intval($panel_id);
    $tableName = "solar_data_panel_" . $panel_id;
    $check = $conn->query("SHOW TABLES LIKE '$tableName'");
    if (!$check || $check->num_rows == 0) continue;

    $sheetName = isset($panel_names[$panel_id]) ? $panel_names[$panel_id] . " (ID $panel_id)" : "Panel_$panel_id";
    fputcsv($out, []); // Linie goală între panouri
    fputcsv($out, [$sheetName]);
    fputcsv($out, ['ID', 'Lumină [V]', 'Baterie [V]', 'Timp']);

    $sql = "SELECT id, light_voltage, battery_voltage, timestamp FROM `$tableName` ORDER BY timestamp ASC";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($out, [$row['id'], $row['light_voltage'], $row['battery_voltage'], $row['timestamp']]);
        }
    }
}
fclose($out);
$conn->close();
exit;
?>
