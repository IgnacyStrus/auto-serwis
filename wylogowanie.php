
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Niedozwolona metoda żądania.');
}

if (!csrf_sprawdz()) {
    http_response_code(403);
    exit('Formularz wygasł. Odśwież stronę i spróbuj ponownie.');
}

wyloguj();

header('Location: ' . BAZA_URL . '/logowanie.php');
exit;
