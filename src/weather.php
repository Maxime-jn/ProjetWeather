<?php
require '../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Paramètres de configuration pour le système de ban IP
$banDuration = 60; // Durée du ban en secondes (1 minute)
$requestLimit = 10;  // Limite de requêtes par minute
$requestWindow = 60; // Fenêtre de temps en secondes (1 minute)
$banFile = 'bans.json'; // Fichier de stockage des bans
$ipLogFile = 'requests.json'; // Fichier de stockage des requêtes par IP

// Fonction pour vérifier si l'IP est bannie
function isBanned($ip) {
    global $banFile;
    if (file_exists($banFile)) {
        $bans = json_decode(file_get_contents($banFile), true);
        if (isset($bans[$ip])) {
            $banEndTime = $bans[$ip];
            if (time() < $banEndTime) {
                return true; // IP encore bannie
            } else {
                // Ban expiré, on le supprime
                unset($bans[$ip]);
                file_put_contents($banFile, json_encode($bans, JSON_PRETTY_PRINT));
            }
        }
    }
    return false;
}

// Fonction pour logguer une requête
function logRequest($ip) {
    global $ipLogFile;
    $requests = file_exists($ipLogFile) ? json_decode(file_get_contents($ipLogFile), true) : [];
    if (!isset($requests[$ip])) {
        $requests[$ip] = [];
    }
    // Ajouter l'horodatage de la requête
    $requests[$ip][] = time();
    file_put_contents($ipLogFile, json_encode($requests, JSON_PRETTY_PRINT));
}

// Fonction pour vérifier si l'IP a dépassé la limite de requêtes
function hasExceededLimit($ip) {
    global $ipLogFile, $requestLimit, $requestWindow;
    if (file_exists($ipLogFile)) {
        $requests = json_decode(file_get_contents($ipLogFile), true);
        if (isset($requests[$ip])) {
            // Garder uniquement les requêtes dans la fenêtre de temps spécifiée
            $requests[$ip] = array_filter($requests[$ip], function($timestamp) use ($requestWindow) {
                return $timestamp > (time() - $requestWindow);
            });
            // Si l'IP a trop de requêtes dans cette fenêtre, on retourne true
            return count($requests[$ip]) > $requestLimit;
        }
    }
    return false;
}

// Fonction pour bannir une IP
function banIp($ip) {
    global $banFile, $banDuration;
    $bans = file_exists($banFile) ? json_decode(file_get_contents($banFile), true) : [];
    $bans[$ip] = time() + $banDuration; // Ban l'IP pour la durée définie
    file_put_contents($banFile, json_encode($bans, JSON_PRETTY_PRINT));
}

// Récupération des données météo
function getWeatherData($city)
{
    $apiKey = '0cac813d8a4946e7aed141031250602';
    $apiUrl = "http://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q={$city}&days=3";
    
    try {
        $response = file_get_contents($apiUrl);
        if ($response === false) {
            throw new Exception("Échec de la récupération des données de l'API.");
        }
        return json_decode($response, true);
    } catch (Exception $e) {
        global $errorLogger;
        $errorLogger->error("Erreur API", ['Message' => $e->getMessage(), 'Ville' => $city]);
        return null;
    }
}

// Configuration des logs
$weatherLogger = new Logger('weather_logger');
$weatherLogger->pushHandler(new StreamHandler('weather.log', Logger::INFO));

$ipLogger = new Logger('ip_logger');
$ipLogger->pushHandler(new StreamHandler('ip.log', Logger::INFO));

$errorLogger = new Logger('error_logger');
$errorLogger->pushHandler(new StreamHandler('error.log', Logger::ERROR));

$performanceLogger = new Logger('performance_logger');
$performanceLogger->pushHandler(new StreamHandler('performance.log', Logger::INFO));

// Vérification de l'IP
$userIp = $_SERVER['REMOTE_ADDR'];
if ($userIp === '::1') {
    $userIp = file_get_contents('https://api.ipify.org'); // API pour récupérer l'IP publique
}

if (isset($_GET['city'])) {
    $city = htmlspecialchars($_GET['city']);
    
    // Log l'IP et les détails supplémentaires
    $ipGeoInfo = json_decode(file_get_contents("http://ip-api.com/json/{$userIp}"));
    $country = $ipGeoInfo->country ?? 'Inconnu';
    $cityGeo = $ipGeoInfo->city ?? 'Inconnu';
    $region = $ipGeoInfo->regionName ?? 'Inconnu';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu';
    $dateTime = date('Y-m-d H:i:s');
    
    // Log l'IP et les détails supplémentaires
    $ipLogger->info("Requête météo effectuée", [
        'IP' => $userIp,
        'Ville' => $city,
        'Pays' => $country,
        'Ville (Géo)' => $cityGeo,
        'Région' => $region,
        'User-Agent' => $userAgent,
        'Date-Heure' => $dateTime
    ]);
    
    // Vérifier si l'IP a dépassé la limite de requêtes
    if (hasExceededLimit($userIp)) {
        banIp($userIp); // Bannir l'IP si elle dépasse la limite
        $errorLogger->warning("IP bannie pour spam : {$userIp}");
        header("Location: banni.php?ban_duration=$banDuration");
    }

    // Logguer la requête
    logRequest($userIp);
    
    // Récupérer les données météo
    $startTime = microtime(true);
    $weatherData = getWeatherData($city);
    
    if ($weatherData && isset($weatherData['current'])) {
        $current = $weatherData['current'];
        $forecast = $weatherData['forecast']['forecastday'];
        $temperature = $current['temp_c'];
        $condition = $current['condition']['text'];
        $windSpeed = $current['wind_kph'];
        $windDir = $current['wind_dir'];
        $humidity = $current['humidity'];
        $pressure = $current['pressure_mb'];

        $weatherLogger->info("Données météorologiques récupérées pour : {$city}", [
            'Température' => $temperature,
            'Condition' => $condition,
            'Vent' => $windSpeed,
            'Humidité' => $humidity,
            'Pression' => $pressure
        ]);
        
        // Log additional details
        $weatherLogger->debug("Détails complets de la météo", [
            'Wind Direction' => $windDir,
            'Humidity' => $humidity,
            'Pressure' => $pressure
        ]);
        
    } else {
        $temperature = $condition = $windSpeed = $windDir = $humidity = $pressure = "N/A";
        $errorLogger->warning("Aucune donnée météorologique trouvée pour : {$city}");
        // Log more info about the failed request
        $errorLogger->warning("Données météo invalides pour : {$city}", ['WeatherData' => $weatherData]);
    }
    
    // Log execution time of the request
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    $performanceLogger->info("Temps d'exécution de la requête météo", [
        'Ville' => $city,
        'Temps (s)' => $executionTime
    ]);
    
    // Log additional execution info
    $performanceLogger->debug("Performance de la requête", [
        'Start Time' => $startTime,
        'End Time' => $endTime,
        'Execution Time' => $executionTime
    ]);
    
} else {
    echo "Veuillez spécifier une ville.";
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
        <header>
            <h1>Météo actuelle - <span class="city-name"><?= htmlspecialchars($city) ?></span></h1>
        </header>

        <section class="current-weather">
            <div class="weather-info">
                <img src="<?= $current['condition']['icon'] ?>" alt="Icône météo" class="weather-icon">
                <div class="weather-details">
                    <p><strong>Température :</strong> <?= $temperature ?>°C</p>
                    <p><strong>Condition :</strong> <?= $condition ?></p>
                    <p><strong>Vent :</strong> <?= $windSpeed ?> km/h (<?= $windDir ?>)</p>
                    <p><strong>Humidité :</strong> <?= $humidity ?>%</p>
                    <p><strong>Pression :</strong> <?= $pressure ?> hPa</p>
                </div>
            </div>
        </section>

        <section class="forecast">
            <h2>Prévisions sur 3 jours</h2>
            <div class="forecast-list">
                <?php foreach ($forecast as $day) : ?>
                    <div class="forecast-item">
                        <div class="date-temp">
                            <span class="date"><strong><?= $day['date'] ?></strong></span>
                            <span class="temperature"><?= $day['day']['maxtemp_c'] ?>°C / <?= $day['day']['mintemp_c'] ?>°C</span>
                        </div>
                        <div class="forecast-condition">
                            <img src="<?= $day['day']['condition']['icon'] ?>" alt="Icône météo" class="weather-icon">
                            <span><?= $day['day']['condition']['text'] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <footer>
            <a href="index.php" class="back-btn">Retour à l'accueil</a>
        </footer>
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