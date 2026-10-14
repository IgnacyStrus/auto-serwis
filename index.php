<?php
require_once 'includes/auth.php';

if (czy_zalogowany()) {
    header('Location: ' . adres_panelu($_SESSION['rola']));
    exit;
}

$tytul = 'Strona główna';
require 'includes/naglowek.php';
?>
    <h1>Auto Serwis</h1>

    <p>
        Zarezerwuj wizytę w warsztacie samochodowym online: wybierz usługę,
        mechanika oraz dogodny termin.
    </p>

    <p>
        <a href="logowanie.php">Zaloguj się</a> albo
        <a href="rejestracja.php">załóż konto</a>.
    </p>
<?php require 'includes/stopka.php'; ?>