<?php
require '../vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logFile = 'weather.log';

function readLogs($logFile) {
    if (file_exists($logFile)) {
        return file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    } else {
        return [];
    }
}




$logs = readLogs($logFile);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Logs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="historyBody">
    <div class="container" id="historyDiv">
        <h1>Historique des Logs</h1>
        <?php if (!empty($logs)) : ?>
            <ul>
                <?php foreach ($logs as $log) : ?>
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
                    <li>
                        <strong>Date :</strong> <?= htmlspecialchars($date) ?><br>
                        <strong>Pays / Ville :</strong> <?= htmlspecialchars($country) ?><br>
                        <!-- <strong>Température :</strong> <?= htmlspecialchars($temperature) ?> °C<br>
                        <strong>Condition :</strong> <?= htmlspecialchars($condition) ?> -->
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>Aucun log disponible.</p>
        <?php endif; ?>
        
    </div>
    <a href="index.php">Retour</a>
</body>
</html>
