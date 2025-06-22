<?php
function getNextPanelName($conn) {
    $res = $conn->query("SELECT name FROM solar_panels WHERE name LIKE 'ESP32_%' ORDER BY id ASC");
    $used = [];
    while ($row = $res->fetch_assoc()) {
        $used[] = $row['name'];
    }
    $nextLetter = 'A';
    for ($i = 0; $i < 26; $i++) {
        $try = "ESP32_" . chr(65 + $i);
        if (!in_array($try, $used)) {
            $nextLetter = chr(65 + $i);
            break;
        }
    }
    return "ESP32_" . $nextLetter;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Utilizează metoda POST.";
    exit;
}

$servername = "localhost";
$username   = "esp32_user";
$password   = "parola_sigura";
$database   = "esp32_data";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    exit("Conexiune eșuată: " . $conn->connect_error);
}

$mac  = isset($_POST['mac']) ? trim($_POST['mac']) : '';
$ip   = isset($_POST['ip']) ? trim($_POST['ip']) : '';

if ($mac === '') {
    http_response_code(400);
    echo "MAC lipsă";
    $conn->close();
    exit;
}

$stmt = $conn->prepare("SELECT id, name FROM solar_panels WHERE mac=? LIMIT 1");
$stmt->bind_param("s", $mac);
$stmt->execute();
$stmt->bind_result($id, $db_name);
if ($stmt->fetch()) {
    $stmt->close();
    if ($ip !== '') {
        $stmt2 = $conn->prepare("UPDATE solar_panels SET ip=?, last_update=NOW(), status='active' WHERE id=?");
        $stmt2->bind_param("si", $ip, $id);
        $stmt2->execute();
        $stmt2->close();
    }
    // Asigură-te că tabelul dedicat există și pentru panouri existente
    $tableName = "solar_data_panel_" . $id;
    $createTableSql = "
        CREATE TABLE IF NOT EXISTS `$tableName` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            light_voltage FLOAT NOT NULL,
            battery_voltage FLOAT NOT NULL,
            predicted_battery FLOAT NULL,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $conn->query($createTableSql);

    echo $id . "|" . $db_name;
    $conn->close();
    exit;
}
$stmt->close();

$newName = getNextPanelName($conn);
$stmt = $conn->prepare("INSERT INTO solar_panels (name, mac, ip, status) VALUES (?, ?, ?, 'active')");
if ($stmt === false) {
    http_response_code(500);
    echo "Eroare prepare: " . $conn->error;
    $conn->close();
    exit;
}
$stmt->bind_param("sss", $newName, $mac, $ip);
if ($stmt->execute()) {
    $newId = $stmt->insert_id;

    // Creează tabel dedicat pentru panou nou
    $tableName = "solar_data_panel_" . $newId;
    $createTableSql = "
        CREATE TABLE IF NOT EXISTS `$tableName` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            light_voltage FLOAT NOT NULL,
            battery_voltage FLOAT NOT NULL,
            predicted_battery FLOAT NULL,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $conn->query($createTableSql);

    echo $newId . "|" . $newName;
} else {
    http_response_code(500);
    if ($conn->errno == 1062) {
        echo "MAC duplicat";
    } else {
        echo "Eroare la inserare panou: " . $stmt->error;
    }
}
$stmt->close();
$conn->close();
?>