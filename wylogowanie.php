<?php
require_once __DIR__ . '/includes/auth.php';
wyloguj();
header('Location: ' . BAZA_URL . '/logowanie.php');
exit;