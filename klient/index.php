<?php
require_once '../includes/auth.php';
wymagaj_roli('klient');

$tytul = 'Panel klienta';
require '../includes/naglowek.php';
?>
    <h1>Panel klienta</h1>
    <p>Witaj, <?= e($_SESSION['imie']) ?>!</p>
<?php require '../includes/stopka.php'; ?>