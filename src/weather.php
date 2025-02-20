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


$keyType = filter_input(INPUT_GET, "type", FILTER_SANITIZE_FULL_SPECIAL_CHARS); // peut etre possiblement définit a "texts", soit a "icons"
if ($keyType === false || $keyType === null) {
    
}
$keysArray = [
    "texts" => [
        "temperature" => "<strong>Température : </strong>",
        "windSpeed" => "<strong>Vitesse du vent : </strong>",
        "windDir" => "<strong>Direction du vent : </strong>",
        "humidity" => "<strong>Humidité : </strong>",
        "pressure" => "<strong>Pression : </strong>",  
    ],
    "icons" => [
        "temperature" => "<img src='./img/icons/tempChaude.png' alt='température'>",
        "windSpeed" => "<img src='./img/icons/windForce.png' alt=''>",
        "windDir" => "<img src='./img/icons/windDirection.png' alt=''>",
        "humidity" => "<img src='./img/icons/humidite.png' alt=''>",
        "pressure" => "<img src='./img/icons/pression.png' alt=''>",
    ]
];

function changeKeyType($keyType) {
    if ($keyType == "texts") {
        return "icons";
    }
    else {
        return "texts";
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
            color: white;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #333;
            background-size: cover;
            background-position: center center;
        }

        .background-blur {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            filter: blur(10px) brightness(40%);
            z-index: -1;
        }

        .container {
            background: rgba(0, 0, 0, 0.75);
            padding: 40px;
            border-radius: 20px;
            max-width: 800px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
        }

        header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
        }

        .city-name {
            color: #FF9800;
        }

        .current-weather {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        .weather-info {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 30px;
        }

        .weather-icon {
            width: 120px;
            height: 120px;
            object-fit: contain;
        }

        .weather-details p {
            font-size: 18px;
            margin: 5px 0;
        }

        .forecast {
            margin-top: 20px;
        }

        .forecast h2 {
            font-size: 26px;
            margin-bottom: 20px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }

        .forecast-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .forecast-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }

        .forecast-item .date-temp {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .forecast-item .forecast-condition {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .forecast-item img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .back-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 15px 30px;
            background-color: #FF5722;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            text-transform: uppercase;
            transition: background-color 0.3s, transform 0.3s;
        }

        .back-btn:hover {
            background-color: #FF3D00;
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div class="background-blur"></div>
    <div class="container">
        <h1>Météo à <?= htmlspecialchars($city) ?></h1>
        <img src="<?= $current['condition']['icon'] ?>" alt="Icône météo" class="weather-icon">
        <p><strong>Condition de la méteo :</strong><span id="separator"></span> <?= $condition ?></p>
        <p><strong><?= $keysArray[$keyType]['temperature'] ?></strong> <span id="separator"></span> <?= $temperature ?>°C</p>
        <p><?= $keysArray[$keyType]['windSpeed'] ?> <span id="separator"></span> <?= $windSpeed ?> km/h</p>
        <p><?= $keysArray[$keyType]['windDir'] ?> <span id="separator"></span> <?= $windDir ?></p>
        <p><?= $keysArray[$keyType]['humidity'] ?> <span id="separator"></span> <?= $humidity ?>%</p>
        <p><?= $keysArray[$keyType]['pressure'] ?> <span id="separator"></span> <?= $pressure ?> hPa</p>

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
        <?php
            if ($keyType == "texts") { ?>
                <a href="weather.php?city=<?= $city ?>&type=icons">Changer en mode Icone</a>
            <?php } else { ?>
                <a href="weather.php?city=<?= $city ?>&type=texts">Changer en mode Textuel</a>
            <?php }

        ?>
        
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