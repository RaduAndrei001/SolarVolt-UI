<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Despre Panouri Solare & Inovații</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <style>
        body {
            background: #181c24;
            color: #e0e0e0;
            font-family: Arial, Helvetica, sans-serif;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 30px 20px;
            background: #232837;
            border-radius: 10px;
            box-shadow: 0 0 20px #111;
            margin-top: 40px;
        }
        .btn-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 25px;
        }
        .admin-btn {
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
        .admin-btn:hover {
            background: linear-gradient(90deg, #0056b3 0%, #007bff 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .section {
            margin-bottom: 35px;
            text-align: left;
            background: #232837;
            border-radius: 8px;
            padding: 20px 30px;
            box-shadow: 0 0 10px #111;
        }
        h2, h3 {
            color: #61dafb;
        }
        ul {
            margin-left: 25px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="btn-row">
        <a class="admin-btn" href="login.php?redirect=admin.php">Administrare sistem &rarr;</a>
    </div>
    <div class="section">
        <h2>Istoria Panourilor Solare</h2>
        <p>
            Primele celule fotovoltaice au fost dezvoltate în anii 1800, dar abia în 1954 Bell Labs a creat primul panou solar eficient pentru uz practic.<br>
            De atunci, tehnologia a evoluat rapid, ajungând astăzi la randamente de peste 20% și costuri mult reduse.<br>
            Panourile solare sunt esențiale pentru tranziția către energie verde și reducerea emisiilor de carbon.
        </p>
    </div>
    <div class="section">
        <h3>Noi implementări și idei inovatoare pentru proiect</h3>
        <ul>
            <li><b>Integrare senzori meteo:</b> temperatură, umiditate, radiație solară, pentru corelarea performanței cu condițiile externe.</li>
            <li><b>Predicție AI:</b> folosirea algoritmilor de machine learning pentru a anticipa producția și a detecta anomalii.</li>
            <li><b>Alertare automată:</b> notificări pe email/SMS când un panou devine nefuncțional sau scade randamentul.</li>
            <li><b>Monitorizare la distanță:</b> aplicație mobilă pentru vizualizarea datelor în timp real.</li>
            <li><b>Optimizare energetică:</b> control automatizat al consumatorilor în funcție de producția solară.</li>
            <li><b>Integrare cu rețele smart grid:</b> vânzarea surplusului de energie în rețea.</li>
            <li><b>Vizualizare avansată:</b> hărți de performanță, istoric multi-anual, export date.</li>
        </ul>
        <p>
            Acest proiect poate fi extins cu oricare dintre aceste idei pentru a deveni un sistem complet de management și analiză pentru panouri solare!
        </p>
    </div>
</div>
</body>
</html>
