<?php
require __DIR__ . '/../includes/auth.php';
wymagaj_roli('pracownik');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel pracownika - Auto Serwis</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Panel pracownika</h1>
    <p>Witaj, <?= htmlspecialchars($_SESSION['imie'], ENT_QUOTES, 'UTF-8') ?>!</p>
    <p><a href="../wylogowanie.php">Wyloguj się</a></p>
</body>
</html>