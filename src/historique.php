<?php
require 'vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Configuration de Monolog pour la lecture des logs
$logFile = 'weather.log';

// Fonction pour lire les logs
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
<body>
    <div class="container">
        <h1>Historique des Logs</h1>
        <?php if (!empty($logs)) : ?>
            <ul>
                <?php foreach ($logs as $log) : ?>
                    <li><?= htmlspecialchars($log) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>Aucun log disponible.</p>
        <?php endif; ?>
        <a href="index.php">Retour</a>
    </div>
</body>
</html>
