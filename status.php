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

// Ștergere panou
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM solar_panels WHERE id=$del_id");
    header("Location: status.php");
    exit;
}

// Editare panou
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_panel = null;
if ($edit_id > 0) {
    $res = $conn->query("SELECT * FROM solar_panels WHERE id=$edit_id");
    if ($res && $res->num_rows > 0) {
        $edit_panel = $res->fetch_assoc();
    }
}

// Salvează editarea
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_panel'])) {
    $id   = intval($_POST['id']);
    $name = trim($_POST['name']);
    $mac  = trim($_POST['mac']);
    $ip   = trim($_POST['ip']);
    $status = trim($_POST['status']);
    $stmt = $conn->prepare("UPDATE solar_panels SET name=?, mac=?, ip=?, status=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $mac, $ip, $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: status.php");
    exit;
}

// Afișare panouri
$panels = [];
$res = $conn->query("SELECT id, name, status, last_update, ip, mac FROM solar_panels");
while ($row = $res->fetch_assoc()) {
    $panels[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Status Panouri Solare</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <style>
        body { background: #181c24; color: #e0e0e0; font-family: Arial, Helvetica, sans-serif; text-align: center; }
        .container { width: 80%; margin: auto; padding: 30px 20px; background: #232837; border-radius: 10px; box-shadow: 0 0 20px #111; margin-top: 40px; }
        .btn-row { display: flex; justify-content: flex-end; margin-bottom: 25px; }
        .back-btn { padding: 12px 28px; background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%); color: #fff; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; font-weight: bold; font-size: 1.1rem; box-shadow: 0 2px 8px #007bff44; transition: background 0.2s, transform 0.1s; }
        .back-btn:hover { background: linear-gradient(90deg, #0056b3 0%, #007bff 100%); transform: translateY(-2px) scale(1.03); }
        table { width: 100%; border-collapse: collapse; background: #232837; color: #e0e0e0; margin-top: 20px; }
        th,td { border: 1px solid #333; padding: 12px; text-align: center; }
        th { background: #181c24; }
        .status-ok { color: #00ff99; font-weight: bold; }
        .status-faulty, .status-offline { color: #ff6b6b; font-weight: bold; }
        .status-inactive { color: #ffb347; font-weight: bold; }
        .edit-btn, .delete-btn {
            padding: 7px 18px;
            border-radius: 6px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            margin: 0 4px;
            transition: background 0.2s;
        }
        .edit-btn { background: #61dafb; color: #232837; }
        .edit-btn:hover { background: #007bff; color: #fff; }
        .delete-btn { background: #ff6b6b; color: #fff; }
        .delete-btn:hover { background: #c82333; }
        .edit-form { background: #232837; border-radius: 10px; padding: 25px 20px; margin-bottom: 30px; box-shadow: 0 0 10px #111; width: 60%; margin-left: auto; margin-right: auto; }
        .edit-form input, .edit-form select { padding: 8px 12px; border-radius: 6px; border: none; margin-bottom: 12px; width: 90%; font-size: 1rem; }
        .edit-form label { display: block; margin-bottom: 5px; font-weight: bold; color: #61dafb; text-align: left; }
        .edit-form button { margin-top: 10px; }
    </style>
</head>
<body>
<div class="container">
    <div class="btn-row">
        <a class="back-btn" href="admin.php">&larr; Înapoi la administrare</a>
    </div>
    <h2>Status Panouri Solare</h2>

    <?php if ($edit_panel): ?>
        <form class="edit-form" method="post">
            <h3>Editează Panou</h3>
            <input type="hidden" name="id" value="<?= $edit_panel['id'] ?>">
            <label>ID</label>
            <input type="number" name="id_display" value="<?= $edit_panel['id'] ?>" disabled>
            <label>Nume</label>
            <input type="text" name="name" value="<?= htmlspecialchars($edit_panel['name']) ?>" required>
            <label>MAC</label>
            <input type="text" name="mac" value="<?= htmlspecialchars($edit_panel['mac']) ?>" required>
            <label>IP</label>
            <input type="text" name="ip" value="<?= htmlspecialchars($edit_panel['ip']) ?>" required>
            <label>Status</label>
            <select name="status" required>
                <option value="active"   <?= $edit_panel['status']=='active'?'selected':'' ?>>ACTIVE</option>
                <option value="faulty"   <?= $edit_panel['status']=='faulty'?'selected':'' ?>>FAULTY</option>
                <option value="inactive" <?= $edit_panel['status']=='inactive'?'selected':'' ?>>INACTIV</option>
            </select>
            <button class="edit-btn" type="submit" name="save_panel">Salvează</button>
            <a class="delete-btn" href="status.php">Anulează</a>
        </form>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nume</th>
                <th>MAC</th>
                <th>IP</th>
                <th>Status</th>
                <th>Ultima actualizare</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($panels as $panel): ?>
                <tr>
                    <td><?= $panel['id'] ?></td>
                    <td><?= htmlspecialchars($panel['name']) ?></td>
                    <td><?= htmlspecialchars($panel['mac']) ?></td>
                    <td><?= htmlspecialchars($panel['ip']) ?></td>
                    <td>
                        <?php
                        $last = strtotime($panel['last_update']);
                        $online = (time() - $last < 120);
                        if ($panel['status'] === 'faulty') {
                            echo '<span class="status-faulty">NEFUNCȚIONAL</span>';
                        } elseif ($panel['status'] === 'online') {
                            echo '<span class="status-ok">ONLINE</span>';
                        } elseif ($panel['status'] === 'offline') {
                            echo '<span class="status-offline">OFFLINE</span>';
                        } elseif ($panel['status'] === 'active' && $online) {
                            echo '<span class="status-ok">ONLINE (ACTIV)</span>';
                        } elseif ($panel['status'] === 'active' && !$online) {
                            echo '<span class="status-offline">OFFLINE (ACTIV)</span>';
                        } else {
                            echo '<span class="status-inactive">INACTIV</span>';
                        }
                        ?>
                    </td>
                    <td><?= $panel['last_update'] ?></td>
                    <td>
                        <a class="edit-btn" href="status.php?edit=<?= $panel['id'] ?>">Editează</a>
                        <a class="delete-btn" href="status.php?delete=<?= $panel['id'] ?>" onclick="return confirm('Sigur vrei să ștergi acest panou?');">Șterge</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
<?php if (!empty($panels)): ?>
// Ping automat pentru fiecare panou la fiecare 10 secunde
function pingPanels() {
    <?php foreach ($panels as $panel): 
        $ip = trim($panel['ip']);
        $id = intval($panel['id']);
        if ($ip) { ?>
        fetch('ping.php?ip=<?= urlencode($ip) ?>&panel_id=<?= $id ?>')
            .then(r => r.json())
            .then(obj => {
                // Poți actualiza vizual statusul aici dacă vrei
            });
    <?php } endforeach; ?>
}
setInterval(pingPanels, 10000); // la fiecare 10 secunde
window.onload = pingPanels;
<?php endif; ?>
</script>
</body>
</html>
