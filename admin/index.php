<?php
require_once '../includes/auth.php';
wymagaj_roli('administrator');

$tytul = 'Panel admina';
require '../includes/naglowek.php';
?>
    <h1>Panel admina</h1>
    <p>Witaj, <?= e($_SESSION['imie']) ?>!</p>
<?php require '../includes/stopka.php'; ?>