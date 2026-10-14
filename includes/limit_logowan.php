<?php
require_once __DIR__ . '/db.php';

const MAX_PROB = 5;
const OKNO_BLOKADY_MIN = 10;

function klucz_email(string $email): string
{
    return mb_strtolower(mb_substr($email, 0, 150));
}

function czy_zablokowany(string $email, string $ip): bool
{
    $stmt = polacz_z_baza()->prepare(
        'SELECT COUNT(*) FROM proby_logowania
         WHERE email = ? AND adres_ip = ?
         AND data_proby > (NOW() - INTERVAL ' . (int) OKNO_BLOKADY_MIN . ' MINUTE)'
    );
    $stmt->execute([klucz_email($email), $ip]);

    return (int) $stmt->fetchColumn() >= MAX_PROB;
}

function zapisz_probe(string $email, string $ip): void
{
    $stmt = polacz_z_baza()->prepare('INSERT INTO proby_logowania (email, adres_ip) VALUES (?, ?)');
    $stmt->execute([klucz_email($email), $ip]);
}

function wyczysc_proby(string $email, string $ip): void
{
    $stmt = polacz_z_baza()->prepare('DELETE FROM proby_logowania WHERE email = ? AND adres_ip = ?');
    $stmt->execute([klucz_email($email), $ip]);
}