<?php
/*******************************************************
 *  Config baza de date — pune valorile reale in .env
 *******************************************************/
$servername = "localhost";
$username   = "esp32_user";
$password   = "parola_sigura";
$database   = "esp32_data";

/*******************************************************
 *  Conexiune + verificare
 *******************************************************/
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    exit("Conexiune eșuată: " . $conn->connect_error);
}

/*******************************************************
 *  Acceptăm NUMAI POST; citim datele panourilor
 *******************************************************/
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);               // Method Not Allowed
    exit("Utilizează metoda POST.");
}

$panel_id = filter_input(INPUT_POST, "panel_id", FILTER_VALIDATE_INT);
$light   = filter_input(INPUT_POST, "voltage1", FILTER_VALIDATE_FLOAT);
$battery = filter_input(INPUT_POST, "voltage2", FILTER_VALIDATE_FLOAT);
$faulty  = filter_input(INPUT_POST, "faulty", FILTER_VALIDATE_INT);

if ($panel_id === false || $light === false || $battery === false) {
    http_response_code(400);
    exit("Parametri lipsă sau invalizi.");
}

/*******************************************************
 *  Verifică dacă panoul există
 *******************************************************/
$panel_check = $conn->prepare("SELECT id FROM solar_panels WHERE id=?");
$panel_check->bind_param("i", $panel_id);
$panel_check->execute();
$panel_check->store_result();
if ($panel_check->num_rows === 0) {
    // DEBUG: loghează ce ID nu există (folosește cale absolută)
    $debugFile = __DIR__ . DIRECTORY_SEPARATOR . "debug_insert.txt";
    $msg = date('Y-m-d H:i:s') . " - ID inexistent: $panel_id\n";
    if (@file_put_contents($debugFile, $msg, FILE_APPEND | LOCK_EX) === false) {
        error_log("Nu pot scrie in debug_insert.txt! Permisiuni sau cale greșită.");
    }
    http_response_code(404);
    $panel_check->close();
    $conn->close();
    exit;
}
$panel_check->close();

/*******************************************************
 *  INSERT date (fără AI/predictie)
 *******************************************************/

// Creează tabelul dedicat dacă nu există
$tableName = "solar_data_panel_" . intval($panel_id);
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

// Inserează doar în tabelul dedicat panoului (NU mai folosi solar_data)
$insertSql = "INSERT INTO `$tableName` (light_voltage, battery_voltage, predicted_battery) VALUES (?, ?, NULL)";
$stmt2 = $conn->prepare($insertSql);
if ($stmt2) {
    $stmt2->bind_param("dd", $light, $battery);
    if (!$stmt2->execute()) {
        $debugFile = __DIR__ . DIRECTORY_SEPARATOR . "debug_panel_table.txt";
        $msg = date('Y-m-d H:i:s') . " - Eroare insert $tableName: " . $stmt2->error . "\n";
        @file_put_contents($debugFile, $msg, FILE_APPEND | LOCK_EX);
        http_response_code(500);
        echo "Eroare execuție: " . $stmt2->error;
        $stmt2->close();
        $conn->close();
        exit;
    }
    $stmt2->close();
} else {
    $debugFile = __DIR__ . DIRECTORY_SEPARATOR . "debug_panel_table.txt";
    $msg = date('Y-m-d H:i:s') . " - Eroare prepare $tableName: " . $conn->error . "\n";
    @file_put_contents($debugFile, $msg, FILE_APPEND | LOCK_EX);
    http_response_code(500);
    echo "Eroare prepare: " . $conn->error;
    $conn->close();
    exit;
}

/*******************************************************
 *  Suprascrie statusul cu 'faulty' dacă ESP32 trimite faulty=1
 *******************************************************/
if ($faulty === 1) {
    $status = 'faulty';
    $update = $conn->prepare("UPDATE solar_panels SET status=?, last_update=NOW() WHERE id=?");
    $update->bind_param("si", $status, $panel_id);
    $update->execute();
    $update->close();

    // Alertă email dacă panoul devine faulty
    $to = "admin@exemplu.ro";
    $subject = "Avertizare: Panou solar #$panel_id nefuncțional";
    $message = "Panoul solar cu ID $panel_id a devenit NEFUNCȚIONAL la ".date('Y-m-d H:i:s');
    @mail($to, $subject, $message);
}

/*******************************************************
 *  Dacă serviciul AI detectează anomalie, trimite alertă
 *******************************************************/
if ($ai_result && isset($ai_result['anomaly']) && $ai_result['anomaly'] === true) {
    $to = "admin@exemplu.ro";
    $subject = "AI: Anomalie detectată panou $panel_id";
    $message = "AI a detectat o anomalie la panoul $panel_id: Lumină=$light V, Baterie=$battery V la ".date('Y-m-d H:i:s');
    @mail($to, $subject, $message);
}

/*******************************************************
 *  Detecție anomalii simple și alertă (fallback)
 *******************************************************/
if ($light > 5.0 || $battery > 5.0 || $light < 0 || $battery < 0) {
    $to = "admin@exemplu.ro";
    $subject = "Anomalie detectată panou $panel_id";
    $message = "Valori anormale detectate: Lumină=$light V, Baterie=$battery V la ".date('Y-m-d H:i:s');
    @mail($to, $subject, $message);
}

echo "OK";
$conn->close();
?>
