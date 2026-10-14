<?php
require_once __DIR__ . '/../includes/auth.php';
wymagaj_roli('pracownik');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel administratora - Auto Serwis</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Panel pracownika</h1>
    <p>Witaj, <?= e($_SESSION['imie']) ?>!</p>
    <p><a href="../wylogowanie.php">Wyloguj się</a></p>
</body>
</html>