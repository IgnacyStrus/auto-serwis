<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
wymagaj_roli('administrator');

$pdo = polacz_z_baza();
$bledy = [];
$nazwa = '';
$opis = '';

$komunikat = $_SESSION['komunikat'] ?? '';
unset($_SESSION['komunikat']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_sprawdz()) {
        $bledy[] = 'Formularz wygasł. Odśwież stronę i spróbuj ponownie.';
    } else {
        $nazwa = trim($_POST['nazwa'] ?? '');
        $opis = trim($_POST['opis'] ?? '');

        if ($nazwa === '') {
            $bledy[] = 'Podaj nazwę kategorii.';
        } elseif (mb_strlen($nazwa) > 100) {
            $bledy[] = 'Nazwa może mieć maksymalnie 100 znaków.';
        }

        if (!$bledy) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM kategorie_uslug WHERE nazwa = ?');
            $stmt->execute([$nazwa]);
            if ($stmt->fetchColumn() > 0) {
                $bledy[] = 'Kategoria o takiej nazwie już istnieje.';
            }
        }

        if (!$bledy) {
            $stmt = $pdo->prepare('INSERT INTO kategorie_uslug (nazwa, opis, aktywna) VALUES (?, ?, 1)');
            $stmt->execute([$nazwa, $opis]);
            $_SESSION['komunikat'] = 'Dodano kategorię.';
            header('Location: ' . BAZA_URL . '/admin/kategorie.php');
            exit;
        }
    }
}

$kategorie = $pdo->query('SELECT id_kategorii, nazwa, opis, aktywna FROM kategorie_uslug ORDER BY nazwa')
    ->fetchAll(PDO::FETCH_ASSOC);

$tytul = 'Kategorie';
require_once __DIR__ . '/../includes/admin_naglowek.php';
?>
<h1>Kategorie usług</h1>

<?php if ($komunikat): ?>
    <div class="sukces"><?= e($komunikat) ?></div>
<?php endif; ?>

<?php foreach ($bledy as $blad): ?>
    <div class="blad"><?= e($blad) ?></div>
<?php endforeach; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nazwa</th>
            <th>Opis</th>
            <th>Aktywna</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!$kategorie): ?>
            <tr><td colspan="4">Brak kategorii.</td></tr>
        <?php endif; ?>
        <?php foreach ($kategorie as $k): ?>
            <tr>
                <td><?= (int) $k['id_kategorii'] ?></td>
                <td><?= e($k['nazwa']) ?></td>
                <td><?= e($k['opis']) ?></td>
                <td><?= $k['aktywna'] ? 'Tak' : 'Nie' ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Dodaj kategorię</h2>
<form method="post" class="formularz">
    <?= csrf_pole() ?>
    <label for="nazwa">Nazwa</label>
    <input type="text" id="nazwa" name="nazwa" maxlength="100" value="<?= e($nazwa) ?>" required>

    <label for="opis">Opis</label>
    <textarea id="opis" name="opis" rows="3"><?= e($opis) ?></textarea>

    <button type="submit">Dodaj</button>
</form>
<?php require_once __DIR__ . '/../includes/stopka.php'; ?>