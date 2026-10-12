<?php
require __DIR__ . '/includes/auth.php';
wyloguj();
header('Location: logowanie.php');
exit;
