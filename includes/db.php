<?php
ini_set('display_errors', '0');

const DB_HOST = 'localhost';
const DB_NAZWA = 'auto_serwis';
const DB_UZYTKOWNIK = 'root';
const DB_HASLO = '';

function loguj_blad(string $tekst): void
{
    $katalog = __DIR__ . '/../logs';
    if (!is_dir($katalog)) {
        mkdir($katalog, 0775, true);
    }
    error_log('[' . date('Y-m-d H:i:s') . '] ' . $tekst . PHP_EOL, 3, $katalog . '/blad.log');
}

function polacz_z_baza(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAZWA . ';charset=utf8mb4',
            DB_UZYTKOWNIK,
            DB_HASLO,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        loguj_blad('Połączenie z bazą: ' . $e->getMessage());
        http_response_code(500);
        exit('Wystąpił błąd serwera. Spróbuj ponownie później.');
    }

    return $pdo;
}