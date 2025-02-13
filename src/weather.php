<?php
require '../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function getWeatherData($city)
{
    $apiKey = '0cac813d8a4946e7aed141031250602';
    $apiUrl = "http://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q={$city}&days=3";
    $response = file_get_contents($apiUrl);
    return json_decode($response, true);
}

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
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Météo - <?= htmlspecialchars($city) ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            color: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            transition: background 0.5s ease-in-out;
            background-size: cover;
            background-position: center center;
            background-attachment: fixed;
        }

        .background-blur {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: 100%;
            background-position: center center;
            filter: blur(8px) brightness(50%);
            z-index: -1;
            transition: all 0.5s ease-in-out;
        }

        .container {
            background: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            max-width: 450px;
            width: 90%;
            position: relative;
            z-index: 1;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 15px;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.5);
        }

        .weather-icon {
            width: 120px;
            height: 120px;
            object-fit: contain;
        }

        .forecast {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 20px;
        }

        .forecast-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 14px 28px;
            text-decoration: none;
            color: white;
            background: #FF9800;
            border-radius: 8px;
            font-weight: bold;
            text-transform: uppercase;
            transition: background 0.3s ease;
        }

        a:hover {
            background: #F57C00;
        }
    </style>
</head>

<body>
    <div class="background-blur"></div>
    <div class="container">
        <h1>Météo à <?= htmlspecialchars($city) ?></h1>
        <img src="<?= $current['condition']['icon'] ?>" alt="Icône météo" class="weather-icon">
        <p><strong>Température :</strong> <?= $temperature ?>°C</p>
        <p><strong>Condition :</strong> <?= $condition ?></p>
        <p><strong>Vent :</strong> <?= $windSpeed ?> km/h (<?= $windDir ?>)</p>
        <p><strong>Humidité :</strong> <?= $humidity ?>%</p>
        <p><strong>Pression :</strong> <?= $pressure ?> hPa</p>

        <h2>Prévisions</h2>
        <div class="forecast">
            <?php foreach ($forecast as $day) : ?>
                <div class="forecast-item">
                    <span><strong><?= $day['date'] ?></strong></span>
                    <img src="<?= $day['day']['condition']['icon'] ?>" alt="Icône météo" class="weather-icon">
                    <span><?= $day['day']['condition']['text'] ?></span>
                    <span><strong><?= $day['day']['maxtemp_c'] ?>°C</strong> / <?= $day['day']['mintemp_c'] ?>°C</span>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="index.php">Retour</a>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let conditionText = "<?= strtolower($condition) ?>";
            let background = document.querySelector(".background-blur");

            if (conditionText.includes("sunny") || conditionText.includes("clear")) {
                background.style.backgroundImage = "url('img/sunny.jpg')";
            } else if (conditionText.includes("cloud")) {
                background.style.backgroundImage = "url('img/cloudy.jpg')";
            } else if (conditionText.includes("rain")) {
                background.style.backgroundImage = "url('img/rainy.jpg')";
            } else if (conditionText.includes("storm")) {
                background.style.backgroundImage = "url('img/storm.jpg')";
            } else if (conditionText.includes("snow")) {
                background.style.backgroundImage = "url('img/snow.jpg')";
            } else {
                background.style.backgroundImage = "url('img/default.jpg')";
            }
        });
    </script>
</body>

</html>