<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
wymagaj_roli('administrator');

$pdo = polacz_z_baza();
$id = (int) ($_GET['id'] ?? 0);
$edycja = $id > 0;

$dane = [
    'nazwa' => '',
    'opis' => '',
    'id_kategorii' => '',
    'czas_trwania_minuty' => '',
    'cena' => '',
];

if ($edycja) {
    $stmt = $pdo->prepare('SELECT nazwa, opis, id_kategorii, czas_trwania_minuty, cena FROM uslugi WHERE id_uslugi = ?');
    $stmt->execute([$id]);
    $znaleziona = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$znaleziona) {
        $_SESSION['komunikat'] = 'Nie znaleziono usługi.';
        header('Location: ' . BAZA_URL . '/admin/uslugi.php');
        exit;
    }
    $dane = array_map('strval', $znaleziona);
}

$kategorie = $pdo->query('SELECT id_kategorii, nazwa, aktywna FROM kategorie_uslug ORDER BY nazwa')
    ->fetchAll(PDO::FETCH_ASSOC);

$bledy = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_sprawdz()) {
        $bledy[] = 'Formularz wygasł. Odśwież stronę i spróbuj ponownie.';
    } else {
        $dane['nazwa'] = trim($_POST['nazwa'] ?? '');
        $dane['opis'] = trim($_POST['opis'] ?? '');
        $dane['id_kategorii'] = trim($_POST['id_kategorii'] ?? '');
        $dane['czas_trwania_minuty'] = trim($_POST['czas_trwania_minuty'] ?? '');
        $dane['cena'] = trim(str_replace(',', '.', $_POST['cena'] ?? ''));

        if ($dane['nazwa'] === '') {
            $bledy[] = 'Podaj nazwę usługi.';
        } elseif (mb_strlen($dane['nazwa']) > 150) {
            $bledy[] = 'Nazwa może mieć maksymalnie 150 znaków.';
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM kategorie_uslug WHERE id_kategorii = ?');
        $stmt->execute([(int) $dane['id_kategorii']]);
        if ($stmt->fetchColumn() == 0) {
            $bledy[] = 'Wybierz kategorię.';
        }

        if (!ctype_digit($dane['czas_trwania_minuty']) || (int) $dane['czas_trwania_minuty'] < 1 || (int) $dane['czas_trwania_minuty'] > 1440) {
            $bledy[] = 'Czas trwania musi być liczbą całkowitą od 1 do 1440 minut.';
        }

        if (!is_numeric($dane['cena']) || (float) $dane['cena'] < 0 || (float) $dane['cena'] > 99999999) {
            $bledy[] = 'Cena musi być liczbą nieujemną.';
        }

        if (!$bledy) {
            if ($edycja) {
                $stmt = $pdo->prepare(
                    'UPDATE uslugi SET nazwa = ?, opis = ?, id_kategorii = ?, czas_trwania_minuty = ?, cena = ? WHERE id_uslugi = ?'
                );
                $stmt->execute([
                    $dane['nazwa'], $dane['opis'], (int) $dane['id_kategorii'],
                    (int) $dane['czas_trwania_minuty'], $dane['cena'], $id,
                ]);
                $_SESSION['komunikat'] = 'Zapisano zmiany w usłudze.';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO uslugi (nazwa, opis, id_kategorii, czas_trwania_minuty, cena, aktywna) VALUES (?, ?, ?, ?, ?, 1)'
                );
                $stmt->execute([
                    $dane['nazwa'], $dane['opis'], (int) $dane['id_kategorii'],
                    (int) $dane['czas_trwania_minuty'], $dane['cena'],
                ]);
                $_SESSION['komunikat'] = 'Dodano usługę.';
            }
            header('Location: ' . BAZA_URL . '/admin/uslugi.php');
            exit;
        }
    }
}

$tytul = $edycja ? 'Edycja usługi' : 'Nowa usługa';
require_once __DIR__ . '/../includes/admin_naglowek.php';
?>
<h1><?= $edycja ? 'Edycja usługi' : 'Nowa usługa' ?></h1>

<?php foreach ($bledy as $blad): ?>
    <div class="blad"><?= e($blad) ?></div>
<?php endforeach; ?>

<form method="post" class="formularz">
    <?= csrf_pole() ?>

    <label for="nazwa">Nazwa</label>
    <input type="text" id="nazwa" name="nazwa" maxlength="150" value="<?= e($dane['nazwa']) ?>" required>

    <label for="id_kategorii">Kategoria</label>
    <select id="id_kategorii" name="id_kategorii" required>
        <option value="">-- wybierz --</option>
        <?php foreach ($kategorie as $k): ?>
            <option value="<?= (int) $k['id_kategorii'] ?>" <?= (string) $k['id_kategorii'] === (string) $dane['id_kategorii'] ? 'selected' : '' ?>>
                <?= e($k['nazwa']) ?><?= $k['aktywna'] ? '' : ' (nieaktywna)' ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="czas_trwania_minuty">Czas trwania (minuty)</label>
    <input type="number" id="czas_trwania_minuty" name="czas_trwania_minuty" min="1" max="1440" value="<?= e($dane['czas_trwania_minuty']) ?>" required>

    <label for="cena">Cena (zł)</label>
    <input type="text" id="cena" name="cena" value="<?= e($dane['cena']) ?>" required>

    <label for="opis">Opis</label>
    <textarea id="opis" name="opis" rows="3"><?= e($dane['opis']) ?></textarea>

    <button type="submit"><?= $edycja ? 'Zapisz' : 'Dodaj' ?></button>
    <a href="<?= e(BAZA_URL) ?>/admin/uslugi.php">Anuluj</a>
</form>
<?php require_once __DIR__ . '/../includes/stopka.php'; ?>