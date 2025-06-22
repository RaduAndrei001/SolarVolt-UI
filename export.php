<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php?redirect=export.php");
    exit;
}
$servername = "localhost";
$username   = "esp32_user";
$password   = "parola_sigura";
$database   = "esp32_data";
$conn = new mysqli($servername, $username, $password, $database);
$panels = [];
$res = $conn->query("SELECT id, name FROM solar_panels");
while ($row = $res->fetch_assoc()) {
    $panels[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Exportă Date Panouri Solare</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <style>
        body { background: #181c24; color: #e0e0e0; font-family: Arial, Helvetica, sans-serif; text-align: center; }
        .container { width: 90%; margin: auto; padding: 30px 20px; background: #232837; border-radius: 10px; box-shadow: 0 0 20px #111; margin-top: 40px; }
        .form-row { margin-bottom: 18px; }
        .export-btn {
            padding: 12px 28px;
            background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px #007bff44;
            transition: background 0.2s, transform 0.1s;
        }
        .export-btn:hover {
            background: linear-gradient(90deg, #0056b3 0%, #007bff 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .back-btn {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 24px;
            background: #232837;
            color: #61dafb;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            border: 1px solid #61dafb;
            transition: background 0.2s, color 0.2s;
        }
        .back-btn:hover {
            background: #61dafb;
            color: #232837;
        }
        label { font-weight: bold; }
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        .checkbox-group label { font-weight: normal; }
    </style>
</head>
<body>
<div class="container">
    <h2>Exportă Date Panouri Solare (CSV)</h2>
    <form method="get" action="export_csv.php" target="_blank">
        <div class="form-row">
            <label>Panouri de exportat:</label>
            <div class="checkbox-group">
                <?php foreach ($panels as $panel): ?>
                    <label>
                        <input type="checkbox" name="panel_ids[]" value="<?= $panel['id'] ?>" checked>
                        <?= htmlspecialchars($panel['name']) ?> (ID: <?= $panel['id'] ?>)
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="export-btn" type="submit">Exportă CSV</button>
    </form>
    <a class="back-btn" href="admin.php">&larr; Înapoi la administrare</a>
</div>
</body>
</html>
