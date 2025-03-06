<?php
require '../vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logFile = 'weather.log';

function readLogs($logFile) {
    return file_exists($logFile) ? file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
}

$logs = readLogs($logFile);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Logs</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .log-container {
            max-width: 80vw;
            max-height: 80vh;
            overflow-y: auto;
            padding: 20px;
        }
        .log-entry {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container" id="historyDiv">
        <h1>Historique des Logs</h1>
        <div class="log-container">
            <?php if (!empty($logs)) : ?>
                <?php foreach ($logs as $log) : ?>
                    <div class="log-entry">
                        <?php
                            $date = substr($log, 1, 19);
                            $startCountry = strpos($log, 'Données météorologiques récupérées pour : ') + strlen('Données météorologiques récupérées pour : ');
                            $endCountry = strpos($log, ' {"Température":', $startCountry);
                            $country = trim(substr($log, $startCountry, $endCountry - $startCountry));
                            
                            $startJson = strpos($log, '{"Température":');
                            $json_data = substr($log, $startJson);
                            $data = json_decode($json_data, true);
                            
                            $temperature = $data['Température'] ?? 'N/A';
                            $condition = $data['Condition'] ?? 'N/A';
                        ?>
                        <p><strong>Date :</strong> <?= htmlspecialchars($date) ?></p>
                        <p><strong>Pays / Ville :</strong> <?= htmlspecialchars($country) ?></p>
                        <!-- <p><strong>Température :</strong> <?= htmlspecialchars($temperature) ?> °C</p>
                        <p><strong>Condition :</strong> <?= htmlspecialchars($condition) ?></p> -->
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Aucun log disponible.</p>
            <?php endif; ?>
        </div>
        <a href="index.php">Retour</a>
    </div>
</body>
</html>