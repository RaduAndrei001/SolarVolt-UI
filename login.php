<?php
session_start();
$servername = "localhost";
$username = "esp32_user";
$password = "parola_sigura";
$database = "esp32_data";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}

$error = "";
$redirect = $_GET['redirect'] ?? 'admin.php';
if (isset($_SESSION['loggedin'])) {
    header("Location: $redirect");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST["username"];
    $pass = hash("sha256", $_POST["password"]);
    $redirect = $_POST['redirect'] ?? 'admin.php';

    $sql = "SELECT * FROM users WHERE username='$user' AND password='$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $user;
        header("Location: $redirect");
        exit;
    } else {
        $error = "❌ Utilizator sau parolă incorectă!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login Administrare</title>
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
            max-width: 400px;
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
        .error-msg {
            color: #ff6b6b;
            margin-bottom: 18px;
            font-weight: bold;
        }
        .home-btn {
            margin-top: 18px;
            padding: 12px 28px;
            background: linear-gradient(90deg, #232837 0%, #61dafb 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px #61dafb44;
            transition: background 0.2s, transform 0.1s;
            display: inline-block;
        }
        .home-btn:hover {
            background: linear-gradient(90deg, #181c24 0%, #007bff 100%);
            transform: translateY(-2px) scale(1.03);
        }
    </style>
</head>
<body>
    <div class="fancy-card">
        <h2>Autentificare Administrare</h2>
        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <input class="fancy-input" type="text" name="username" placeholder="Utilizator" required>
            <input class="fancy-input" type="password" name="password" placeholder="Parolă" required>
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <button class="fancy-btn" type="submit">Login</button>
        </form>
        <a class="home-btn" href="homepage.php">⟵ Înapoi la homepage</a>
    </div>
</body>
</html>
