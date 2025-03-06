# ProjetWeather

ProjetWeather est une application web qui permet aux utilisateurs de consulter les conditions météorologiques actuelles et les prévisions pour les trois prochains jours pour une ville donnée. L'application utilise l'API WeatherAPI pour récupérer les données météorologiques.

## Prérequis

- PHP 7.4 ou supérieur
- Composer
- Accès à Internet pour récupérer les données de l'API WeatherAPI

## Installation

1. Clonez le dépôt sur votre machine locale :

    ```bash
    git clone https://github.com/votre-utilisateur/projet-weather.git
    cd projet-weather
    ```

2. Installez les dépendances avec Composer :

    ```bash
    composer install
    ```

3. Modifier la ligne 103 dans weather.php ou vous remplacer votre API Key :

    ```php
     $apiKey = 'VOTRE_API_KEY';
    ```

## Utilisation

1. Démarrez un serveur PHP intégré pour tester l'application localement :

    ```bash
    php -S localhost:8000 -t src
    ```

2. Ouvrez votre navigateur et accédez à l'URL suivante :

    ```
    http://localhost:8000/index.php
    ```

3. Entrez le nom d'une ville dans le champ de recherche et cliquez sur "Rechercher" pour obtenir les conditions météorologiques actuelles et les prévisions.

## Fonctionnalités

- **Recherche de conditions météorologiques par ville** : Entrez le nom d'une ville pour obtenir les conditions météorologiques actuelles et les prévisions pour les trois prochains jours.
- **Affichage des données météorologiques actuelles** : Température, condition, vitesse et direction du vent, humidité, pression.
- **Affichage des prévisions météorologiques sur trois jours**.
- **Historique des requêtes météorologiques** : Consultez l'historique des requêtes effectuées.
- **Système de bannissement des utilisateurs** : Les utilisateurs qui dépassent la limite de requêtes par minute sont temporairement bannis.

## Configuration des logs

L'application utilise Monolog pour la gestion des logs. Les logs sont configurés dans le fichier `weather.php` :

```php
// Configuration des logs
$weatherLogger = new Logger('weather_logger');
$weatherLogger->pushHandler(new StreamHandler('weather.log', Logger::INFO));

$ipLogger = new Logger('ip_logger');
$ipLogger->pushHandler(new StreamHandler('ip.log', Logger::INFO));

$errorLogger = new Logger('error_logger');
$errorLogger->pushHandler(new StreamHandler('error.log', Logger::ERROR));

$performanceLogger = new Logger('performance_logger');
$performanceLogger->pushHandler(new StreamHandler('performance.log', Logger::INFO));

$alertLogger = new Logger('alert_logger');
$alertLogger->pushHandler(new StreamHandler('alert.log', Logger::ALERT));
