<?php
$tytul = 'Dashboard';
require_once __DIR__ . '/../includes/admin_naglowek.php';
?>
<h1>Panel administratora</h1>
<p>Witaj, <?= e($_SESSION['imie']) ?>.</p>
<?php require_once __DIR__ . '/../includes/stopka.php'; ?>