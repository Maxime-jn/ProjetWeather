<?php
session_start();

// Récupérer l'IP de l'utilisateur
$userIp = $_SERVER['REMOTE_ADDR'];

// Vérifier si une durée de bannissement a été fournie en paramètre la première fois
if (!isset($_SESSION['ban_duration'][$userIp])) {
    if (isset($_GET['ban_duration']) && is_numeric($_GET['ban_duration'])) {
        $_SESSION['ban_duration'][$userIp] = intval($_GET['ban_duration']);
    } else {
        $_SESSION['ban_duration'][$userIp] = 120; // Durée par défaut si non spécifiée
    }
}

// Définir la durée du bannissement à partir de la session
$ban_duration = $_SESSION['ban_duration'][$userIp];

// Vérifier si l'utilisateur a déjà un bannissement actif
if (!isset($_SESSION['ban_start'][$userIp])) {
    $_SESSION['ban_start'][$userIp] = time();
}

// Récupérer le timestamp du début du bannissement
$ban_start = $_SESSION['ban_start'][$userIp];
$time_passed = time() - $ban_start;
$timeLeft = max(0, $ban_duration - $time_passed);

// Si le bannissement est expiré, supprimer les données de session et rediriger
if ($timeLeft <= 0) {
    unset($_SESSION['ban_start'][$userIp]);
    unset($_SESSION['ban_duration'][$userIp]);
    header("Location: index.php");
    exit;
}

// Supprimer le paramètre de l'URL pour empêcher toute modification
if (isset($_GET['ban_duration'])) {
    header("Location: banni.php");
    exit;
}

// Rafraîchir automatiquement la page toutes les secondes
header("Refresh: 1");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banni</title>
    <style>


        body {
            background-color: black;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;

            
            
            color: white;
            text-align: center;
        }


    </style>
</head>
<body>
    <div class="banned-message">Vous êtes banni</div>
    <div id="timer">Temps restant : <span><?= gmdate("i:s", $timeLeft) ?></span></div>
</body>
</html>
