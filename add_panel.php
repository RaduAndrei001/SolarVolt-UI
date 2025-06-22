<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}
$servername = "localhost";
$username   = "esp32_user";
$password   = "parola_sigura";
$database   = "esp32_data";
$conn = new mysqli($servername, $username, $password, $database);

$success = false;
$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id   = isset($_POST['panel_id']) && $_POST['panel_id'] !== '' ? intval($_POST['panel_id']) : null;
    $name = trim($_POST['name'] ?? '');
    $mac  = trim($_POST['mac'] ?? '');
    $ip   = trim($_POST['ip'] ?? '');

    if ($name === '' || $mac === '' || $ip === '') {
        $error = "Toate câmpurile sunt obligatorii!";
    } else {
        // Verifică dacă MAC-ul există deja (dar permite editarea dacă e pe același ID)
        $stmt_check = $conn->prepare("SELECT id FROM solar_panels WHERE mac=?");
        $stmt_check->bind_param("s", $mac);
        $stmt_check->execute();
        $stmt_check->store_result();
        $mac_exists = $stmt_check->num_rows > 0;
        $existing_id = null;
        if ($mac_exists) {
            $stmt_check->bind_result($existing_id);
            $stmt_check->fetch();
        }
        $stmt_check->close();

        if ($mac_exists && (!$id || $existing_id != $id)) {
            $error = "MAC-ul introdus există deja în baza de date!";
        } else if ($id) {
            // Update panou existent
            $stmt = $conn->prepare("UPDATE solar_panels SET name=?, mac=?, ip=?, status='active', last_update=NOW() WHERE id=?");
            if ($stmt === false) {
                $error = "Eroare prepare: " . $conn->error;
            } else {
                $stmt->bind_param("sssi", $name, $mac, $ip, $id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $success = true;
                } else {
                    $error = "Eroare la update sau ID inexistent: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            // Creează panou nou
            $stmt = $conn->prepare("INSERT INTO solar_panels (name, mac, ip, status) VALUES (?, ?, ?, 'active')");
            if ($stmt === false) {
                $error = "Eroare prepare: " . $conn->error;
            } else {
                $stmt->bind_param("sss", $name, $mac, $ip);
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $error = "Eroare la inserare: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Adaugă/Editează Panou Solar</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <style>
        body {
            background: linear-gradient(135deg, #181c24 0%, #232837 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fancy-card {
            background: rgba(35,40,55,0.98);
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            padding: 40px 30px 30px 30px;
            width: 100%;
            max-width: 420px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .fancy-card:before {
            content: '';
            position: absolute;
            top: -60px;
            left: -60px;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, #007bff55 0%, transparent 80%);
            z-index: 0;
        }
        .fancy-card h2 {
            margin-bottom: 30px;
            font-size: 2rem;
            letter-spacing: 1px;
            color: #61dafb;
            z-index: 1;
            position: relative;
        }
        .fancy-input {
            width: 90%;
            padding: 12px 15px;
            margin-bottom: 25px;
            border: none;
            border-radius: 8px;
            background: #181c24;
            color: #e0e0e0;
            font-size: 1.1rem;
            outline: none;
            box-shadow: 0 2px 8px #1114;
            transition: background 0.2s;
        }
        .fancy-input:focus {
            background: #232837;
        }
        .fancy-btn {
            width: 100%;
            padding: 12px 0;
            background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px #007bff44;
            transition: background 0.2s, transform 0.1s;
        }
        .fancy-btn:hover {
            background: linear-gradient(90deg, #0056b3 0%, #007bff 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: #61dafb;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #fff;
            text-decoration: underline;
        }
        .success-msg {
            color: #00ff99;
            margin-bottom: 18px;
            font-weight: bold;
        }
        .error-msg {
            color: #ff6b6b;
            margin-bottom: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="fancy-card">
        <h2>Adaugă/Editează Panou Solar</h2>
        <?php if ($success): ?>
            <div class="success-msg">Panoul a fost salvat cu succes!</div>
        <?php elseif ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <input class="fancy-input" type="number" name="panel_id" placeholder="ID (opțional pentru editare)">
            <input class="fancy-input" type="text" name="name" placeholder="Nume panou (ex: ESP32_XYZ)" required>
            <input class="fancy-input" type="text" name="mac" placeholder="MAC (ex: AA:BB:CC:DD:EE:FF)" required>
            <input class="fancy-input" type="text" name="ip" placeholder="IP (ex: 192.168.1.100)" required>
            <button class="fancy-btn" type="submit">Salvează</button>
        </form>
        <a class="back-link" href="admin.php">&larr; Înapoi la administrare</a>
    </div>
</body>
</html>
