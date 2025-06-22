<?php
$servername = "localhost";
$username   = "esp32_user";
$password   = "parola_sigura";
$database   = "esp32_data";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    exit("Conexiune eșuată: " . $conn->connect_error);
}
$sql = "SELECT temperature, pressure, `condition`, predicted_temperature, timestamp FROM meteo_data ORDER BY timestamp DESC LIMIT 40";
$res = $conn->query($sql);
$rows = [];
if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
}
$rows = array_reverse($rows);
$conn->close();
header('Content-Type: application/json');
echo json_encode($rows);
?>
