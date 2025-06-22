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
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Utilizează metoda POST.");
}
$temp = filter_input(INPUT_POST, "temperature", FILTER_VALIDATE_FLOAT);
$pres = filter_input(INPUT_POST, "pressure", FILTER_VALIDATE_FLOAT);
$cond = trim($_POST["condition"] ?? "");
if ($temp === false || $pres === false || $cond === "") {
    http_response_code(400);
    exit("Parametri lipsă sau invalizi.");
}

// Elimină orice referință la AI/predictie

// Salvează și predicția în baza de date (asigură-te că ai coloana pressure în meteo_data!)
$stmt = $conn->prepare(
    "INSERT INTO meteo_data (temperature, pressure, `condition`, predicted_temperature)
     VALUES (?, ?, ?, NULL)"
);
$stmt->bind_param("dds", $temp, $pres, $cond);
if ($stmt->execute()) {
    echo "OK";
} else {
    http_response_code(500);
    echo "Eroare execuție: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
