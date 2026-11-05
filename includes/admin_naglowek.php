<?php
require_once __DIR__ . '/auth.php';
wymagaj_roli('administrator');
require __DIR__ . '/naglowek.php';

$strona = basename($_SERVER['PHP_SELF']);
$pozycje = [
    'index.php' => 'Dashboard',
    'kategorie.php' => 'Kategorie',
    'uslugi.php' => 'Usługi',
    'pracownicy.php' => 'Pracownicy',
    'rezerwacje.php' => 'Rezerwacje',
];
?>
<nav class="menu-admin">
    <?php foreach ($pozycje as $plik => $nazwa): ?>
        <a href="<?= e(BAZA_URL) ?>/admin/<?= e($plik) ?>" class="<?= $strona === $plik ? 'aktywna' : '' ?>"><?= e($nazwa) ?></a>
    <?php endforeach; ?>
</nav>