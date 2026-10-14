<?php
require_once '../includes/auth.php';
wymagaj_roli('pracownik');

$tytul = 'Panel pracownika';
require '../includes/naglowek.php';
?>
    <h1>Panel pracownika</h1>
    <p>Witaj, <?= e($_SESSION['imie']) ?>!</p>
<?php require '../includes/stopka.php'; ?>