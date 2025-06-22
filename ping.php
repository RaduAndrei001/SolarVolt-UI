<?php
header('Content-Type: application/json');
$ip = isset($_GET['ip']) ? $_GET['ip'] : '';
$panel_id = isset($_GET['panel_id']) ? intval($_GET['panel_id']) : 0;
if (!$ip || $panel_id <= 0) {
    echo json_encode(['online' => false, 'error' => 'Parametri lipsă']);
    exit;
}
// Ping cross-platform (Windows/Linux)
$os = strtoupper(substr(PHP_OS, 0, 3));
if ($os === 'WIN') {
    $cmd = "ping -n 1 -w 1000 " . escapeshellarg($ip);
} else {
    $cmd = "ping -c 1 -W 1 " . escapeshellarg($ip);
}
exec($cmd, $output, $status);
$isOnline = ($status === 0);

$updateOk = false;
if ($panel_id > 0) {
    $servername = "localhost";
    $username   = "esp32_user";
    $password   = "parola_sigura";
    $database   = "esp32_data";
    $conn = new mysqli($servername, $username, $password, $database);
    if (!$conn->connect_error) {
        // Folosește doar valorile permise de ENUM
        $newStatus = $isOnline ? 'active' : 'inactive';
        $stmt = $conn->prepare("UPDATE solar_panels SET status=?, last_update=NOW() WHERE id=?");
        $stmt->bind_param("si", $newStatus, $panel_id);
        $stmt->execute();
        $updateOk = ($stmt->affected_rows > 0);
        $stmt->close();
        $conn->close();
    }
}

echo json_encode([
    'online' => $isOnline,
    'panel_id' => $panel_id,
    'ip' => $ip,
    'update' => $updateOk
]);
?>
