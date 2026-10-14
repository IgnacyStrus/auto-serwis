<?php
require_once __DIR__ . '/db.php';

const BAZA_URL = '/auto-serwis';
const CZAS_SESJI = 1800;

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('AUTOSERWIS');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => BAZA_URL,
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (isset($_SESSION['ostatnia_aktywnosc']) && time() - $_SESSION['ostatnia_aktywnosc'] > CZAS_SESJI) {
    $_SESSION = [];
    session_regenerate_id(true);
}
if (isset($_SESSION['id_uzytkownika'])) {
    $_SESSION['ostatnia_aktywnosc'] = time();
}

function e(?string $tekst): string
{
    return htmlspecialchars((string) $tekst, ENT_QUOTES, 'UTF-8');
}

function czy_zalogowany(): bool
{
    return isset($_SESSION['id_uzytkownika'], $_SESSION['rola']);
}

function zaloguj(array $uzytkownik): void
{
    session_regenerate_id(true);
    $_SESSION['id_uzytkownika'] = (int) $uzytkownik['id_uzytkownika'];
    $_SESSION['imie'] = $uzytkownik['imie'];
    $_SESSION['rola'] = $uzytkownik['rola'];
    $_SESSION['ostatnia_aktywnosc'] = time();
}

function wyloguj(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function adres_panelu(string $rola): string
{
    return BAZA_URL . '/' . $rola . '/index.php';
}

function wymagaj_logowania(): void
{
    if (!czy_zalogowany()) {
        header('Location: ' . BAZA_URL . '/logowanie.php');
        exit;
    }

    $stmt = polacz_z_baza()->prepare('SELECT rola, aktywny FROM uzytkownicy WHERE id_uzytkownika = ?');
    $stmt->execute([$_SESSION['id_uzytkownika']]);
    $uzytkownik = $stmt->fetch();

    if (!$uzytkownik || !$uzytkownik['aktywny']) {
        wyloguj();
        header('Location: ' . BAZA_URL . '/logowanie.php');
        exit;
    }
    $_SESSION['rola'] = $uzytkownik['rola'];
}

function wymagaj_roli(string ...$dozwolone_role): void
{
    wymagaj_logowania();

    if (!in_array($_SESSION['rola'], $dozwolone_role, true)) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="pl"><head><meta charset="UTF-8"><title>Brak dostępu</title></head><body>';
        echo '<h1>Brak dostępu</h1><p>Nie masz uprawnień do tej strony.</p>';
        echo '<p><a href="' . e(adres_panelu($_SESSION['rola'])) . '">Wróć do swojego panelu</a></p>';
        echo '</body></html>';
        exit;
    }
}