<?php
require_once __DIR__ . '/../includes/auth.php';
wymagaj_roli('administrator');

$pdo = polacz_z_baza();

$rezerwacje = $pdo->query(
    'SELECT r.id_rezerwacji, r.data_rezerwacji, r.godzina_rozpoczecia, r.godzina_zakonczenia, r.status,
            CONCAT(k.imie, " ", k.nazwisko) AS klient,
            CONCAT(up.imie, " ", up.nazwisko) AS pracownik,
            us.nazwa AS usluga
     FROM rezerwacje r
     JOIN uzytkownicy k ON k.id_uzytkownika = r.id_klienta
     JOIN pracownicy p ON p.id_pracownika = r.id_pracownika
     JOIN uzytkownicy up ON up.id_uzytkownika = p.id_uzytkownika
     JOIN uslugi us ON us.id_uslugi = r.id_uslugi
     ORDER BY r.data_rezerwacji DESC, r.godzina_rozpoczecia DESC'
)->fetchAll(PDO::FETCH_ASSOC);

$tytul = 'Rezerwacje';
require_once __DIR__ . '/../includes/admin_naglowek.php';
?>
<h1>Rezerwacje</h1>

<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>Godzina</th>
            <th>Klient</th>
            <th>Pracownik</th>
            <th>Usługa</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!$rezerwacje): ?>
            <tr><td colspan="6">Brak rezerwacji.</td></tr>
        <?php endif; ?>
        <?php foreach ($rezerwacje as $r): ?>
            <tr>
                <td><?= e($r['data_rezerwacji']) ?></td>
                <td><?= e(substr($r['godzina_rozpoczecia'], 0, 5)) ?> - <?= e(substr($r['godzina_zakonczenia'], 0, 5)) ?></td>
                <td><?= e($r['klient']) ?></td>
                <td><?= e($r['pracownik']) ?></td>
                <td><?= e($r['usluga']) ?></td>
                <td><?= e($r['status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../includes/stopka.php'; ?>