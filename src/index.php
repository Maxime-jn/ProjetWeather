<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Météo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Conditions Météorologiques</h1>
        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>
        <form method="GET" action="weather.php">
            <input type="text" name="city" placeholder="Entrez une ville" required>
            <input type="hidden" name="type" value="texts">
            <button type="submit">Rechercher</button>
        </form>
        <a href="historique.php">Voir l'historique</a>
    </div>
</body>
</html>
