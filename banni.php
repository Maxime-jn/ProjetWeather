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
            font-family: "Arial", sans-serif;
            color: white;
            text-align: center;
        }

        .banned-message {
            font-size: 60px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 5px;
            animation: shake 1.5s ease-in-out infinite, flicker 0.1s infinite alternate;
            color: red;
            margin-bottom: 20px;
        }

        #timer {
            font-size: 30px;
            font-weight: bold;
            color: yellow;
        }

        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            50% { transform: translateX(10px); }
            75% { transform: translateX(-10px); }
            100% { transform: translateX(0); }
        }

        @keyframes flicker {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="banned-message">Vous êtes banni</div>
    <div id="timer">Temps restant : <span><?= gmdate("i:s", $timeLeft) ?></span></div>
</body>
</html>
