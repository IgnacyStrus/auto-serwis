<?php
require_once DIR . '/auth.php';
$tytul = $tytul ?? 'Auto Serwis';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tytul) ?> - Auto Serwis</title>
    <link rel="stylesheet" href="<?= e(BAZA_URL) ?>/css/style.css">
</head>
<body>
<header class="naglowek">
    <a class="logo" href="<?= e(BAZA_URL) ?>/index.php">Auto Serwis</a>
    <nav>
        <?php if (czy_zalogowany()): ?>
            <a href="<?= e(adres_panelu($_SESSION['rola'])) ?>">Panel</a>
            <a href="<?= e(BAZA_URL) ?>/profil.php">Profil</a>
            <a href="<?= e(BAZA_URL) ?>/wylogowanie.php">Wyloguj (<?= e($_SESSION['imie']) ?>)</a>
        <?php else: ?>
            <a href="<?= e(BAZA_URL) ?>/logowanie.php">Logowanie</a>
            <a href="<?= e(BAZA_URL) ?>/rejestracja.php">Rejestracja</a>
        <?php endif; ?>
    </nav>
</header>
<main class="tresc">