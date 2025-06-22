<?php
/*──────────────── Config bază de date ────────────────*/
$servername = "localhost";
$username   = "esp32_user";
$password   = "parola_sigura";
$database   = "esp32_data";

/*──────────────── Conexiune ──────────────────────────*/
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    exit("Conexiune eșuată: " . $conn->connect_error);
}

/*──────────────── Interogare: ultimele 40 citiri ─────*/
$panel_id = isset($_GET['panel_id']) ? intval($_GET['panel_id']) : 1;
$tableName = "solar_data_panel_" . intval($panel_id);

// Verifică dacă tabela există
$tableExists = false;
$check = $conn->query("SHOW TABLES LIKE '$tableName'");
if ($check && $check->num_rows > 0) {
    $tableExists = true;
}

$rows = [];
if ($tableExists) {
    $sql = "SELECT id, light_voltage, battery_voltage, predicted_battery, timestamp
            FROM `$tableName`
            ORDER BY timestamp DESC
            LIMIT 40";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
    }
    $rows = array_reverse($rows);
}
$conn->close();

header('Content-Type: application/json');
echo json_encode($rows);
?>
