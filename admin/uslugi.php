<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
wymagaj_roli('administrator');

$pdo = polacz_z_baza();
$bledy = [];

$komunikat = $_SESSION['komunikat'] ?? '';
unset($_SESSION['komunikat']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $akcja = $_POST['akcja'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if (!csrf_sprawdz()) {
        $bledy[] = 'Formularz wygasł. Odśwież stronę i spróbuj ponownie.';
    } elseif ($akcja === 'przelacz') {
        $stmt = $pdo->prepare('UPDATE uslugi SET aktywna = 1 - aktywna WHERE id_uslugi = ?');
        $stmt->execute([$id]);
        $_SESSION['komunikat'] = 'Zmieniono status usługi.';
        header('Location: ' . BAZA_URL . '/admin/uslugi.php');
        exit;
    } elseif ($akcja === 'usun') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM rezerwacje WHERE id_uslugi = ?');
        $stmt->execute([$id]);

        if ($stmt->fetchColumn() > 0) {
            $bledy[] = 'Nie można usunąć usługi, która ma rezerwacje. Możesz ją dezaktywować.';
        } else {
            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM uslugi_pracownikow WHERE id_uslugi = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM uslugi WHERE id_uslugi = ?')->execute([$id]);
            $pdo->commit();
            $_SESSION['komunikat'] = 'Usunięto usługę.';
            header('Location: ' . BAZA_URL . '/admin/uslugi.php');
            exit;
        }
    }
}

$uslugi = $pdo->query(
    'SELECT u.id_uslugi, u.nazwa, u.czas_trwania_minuty, u.cena, u.aktywna, k.nazwa AS kategoria
     FROM uslugi u
     LEFT JOIN kategorie_uslug k ON k.id_kategorii = u.id_kategorii
     ORDER BY k.nazwa, u.nazwa'
)->fetchAll(PDO::FETCH_ASSOC);

$tytul = 'Usługi';
require_once __DIR__ . '/../includes/admin_naglowek.php';
?>
<h1>Usługi</h1>

<?php if ($komunikat): ?>
    <div class="sukces"><?= e($komunikat) ?></div>
<?php endif; ?>

<?php foreach ($bledy as $blad): ?>
    <div class="blad"><?= e($blad) ?></div>
<?php endforeach; ?>

<p><a href="<?= e(BAZA_URL) ?>/admin/usluga_form.php">+ Dodaj usługę</a></p>

<table>
    <thead>
        <tr>
            <th>Nazwa</th>
            <th>Kategoria</th>
            <th>Czas</th>
            <th>Cena</th>
            <th>Aktywna</th>
            <th>Akcje</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!$uslugi): ?>
            <tr><td colspan="6">Brak usług.</td></tr>
        <?php endif; ?>
        <?php foreach ($uslugi as $u): ?>
            <tr>
                <td><?= e($u['nazwa']) ?></td>
                <td><?= e($u['kategoria'] ?? '-') ?></td>
                <td><?= (int) $u['czas_trwania_minuty'] ?> min</td>
                <td><?= e(number_format((float) $u['cena'], 2, ',', ' ')) ?> zł</td>
                <td><?= $u['aktywna'] ? 'Tak' : 'Nie' ?></td>
                <td>
                    <a href="<?= e(BAZA_URL) ?>/admin/usluga_form.php?id=<?= (int) $u['id_uslugi'] ?>">Edytuj</a>

                    <form method="post">
                        <?= csrf_pole() ?>
                        <input type="hidden" name="akcja" value="przelacz">
                        <input type="hidden" name="id" value="<?= (int) $u['id_uslugi'] ?>">
                        <button type="submit"><?= $u['aktywna'] ? 'Dezaktywuj' : 'Aktywuj' ?></button>
                    </form>

                    <form method="post" onsubmit="return confirm('Na pewno usunąć tę usługę?');">
                        <?= csrf_pole() ?>
                        <input type="hidden" name="akcja" value="usun">
                        <input type="hidden" name="id" value="<?= (int) $u['id_uslugi'] ?>">
                        <button type="submit" class="przycisk-usun">Usuń</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../includes/stopka.php'; ?>