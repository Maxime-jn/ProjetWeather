<?php
require '../vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Fonction pour récupérer les données météo
function getWeatherData($city) {
    $apiKey = '0cac813d8a4946e7aed141031250602';
    $apiUrl = "http://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q={$city}&days=3";
    $response = file_get_contents($apiUrl);
    return json_decode($response, true);
}

// Configuration de Monolog
$logger = new Logger('weather_logger');
$logger->pushHandler(new StreamHandler('weather.log', Logger::INFO));

if (isset($_GET['city'])) {
    $city = htmlspecialchars($_GET['city']);
    $weatherData = getWeatherData($city);

    if ($weatherData) {
        $current = $weatherData['current'];
        $forecast = $weatherData['forecast']['forecastday'];
        $temperature = $current['temp_c'];
        $condition = $current['condition']['text'];
        $windSpeed = $current['wind_kph'];
        $windDir = $current['wind_dir'];
        $humidity = $current['humidity'];
        $pressure = $current['pressure_mb'];

        // Enregistrer des informations détaillées dans le fichier log
        $logger->info("Données météorologiques récupérées pour : {$city}", [
            'Température' => $temperature,
            'Condition' => $condition
        ]);
    } else {
        $temperature = $condition = $windSpeed = $windDir = $humidity = $pressure = "N/A";
        $logger->warning("Aucune donnée météorologique trouvée pour : {$city}");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Météo - Résultats</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Conditions Météorologiques pour <?= htmlspecialchars($city) ?></h1>
        <p>Température : <?= $temperature ?> °C</p>
        <p>Condition : <?= $condition ?></p>
        <p>Vitesse du vent : <?= $windSpeed ?> km/h</p>
        <p>Direction du vent : <?= $windDir ?></p>
        <p>Humidité : <?= $humidity ?>%</p>
        <p>Pression Atmosphérique : <?= $pressure ?> hPa</p>
        
        <h2>Prévisions pour les prochains jours :</h2>
        <?php foreach ($forecast as $day) : ?>
            <p>Date : <?= $day['date'] ?></p>
            <p>Condition : <?= $day['day']['condition']['text'] ?></p>
            <p>Température Max : <?= $day['day']['maxtemp_c'] ?> °C</p>
            <p>Température Min : <?= $day['day']['mintemp_c'] ?> °C</p>
            <hr>
        <?php endforeach; ?>

        <a href="index.php">Retour</a>
    </div>
</body>
</html>
