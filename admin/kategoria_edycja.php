<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
wymagaj_roli('administrator');

$pdo = polacz_z_baza();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT id_kategorii, nazwa, opis FROM kategorie_uslug WHERE id_kategorii = ?');
$stmt->execute([$id]);
$kategoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kategoria) {
    $_SESSION['komunikat'] = 'Nie znaleziono kategorii.';
    header('Location: ' . BAZA_URL . '/admin/kategorie.php');
    exit;
}

$bledy = [];
$nazwa = $kategoria['nazwa'];
$opis = (string) $kategoria['opis'];

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
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM kategorie_uslug WHERE nazwa = ? AND id_kategorii <> ?');
            $stmt->execute([$nazwa, $id]);
            if ($stmt->fetchColumn() > 0) {
                $bledy[] = 'Kategoria o takiej nazwie już istnieje.';
            }
        }

        if (!$bledy) {
            $stmt = $pdo->prepare('UPDATE kategorie_uslug SET nazwa = ?, opis = ? WHERE id_kategorii = ?');
            $stmt->execute([$nazwa, $opis, $id]);
            $_SESSION['komunikat'] = 'Zapisano zmiany w kategorii.';
            header('Location: ' . BAZA_URL . '/admin/kategorie.php');
            exit;
        }
    }
}

$tytul = 'Edycja kategorii';
require_once __DIR__ . '/../includes/admin_naglowek.php';
?>
<h1>Edycja kategorii</h1>

<?php foreach ($bledy as $blad): ?>
    <div class="blad"><?= e($blad) ?></div>
<?php endforeach; ?>

<form method="post" class="formularz">
    <?= csrf_pole() ?>

    <label for="nazwa">Nazwa</label>
    <input type="text" id="nazwa" name="nazwa" maxlength="100" value="<?= e($nazwa) ?>" required>

    <label for="opis">Opis</label>
    <textarea id="opis" name="opis" rows="3"><?= e($opis) ?></textarea>

    <button type="submit">Zapisz</button>
    <a href="<?= e(BAZA_URL) ?>/admin/kategorie.php">Anuluj</a>
</form>
<?php require_once __DIR__ . '/../includes/stopka.php'; ?>