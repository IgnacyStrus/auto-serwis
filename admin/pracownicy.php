<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
wymagaj_roli('administrator');

$pdo = polacz_z_baza();
$bledy = [];
$dane = ['imie' => '', 'nazwisko' => '', 'email' => '', 'telefon' => '', 'opis' => ''];

$komunikat = $_SESSION['komunikat'] ?? '';
unset($_SESSION['komunikat']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_sprawdz()) {
        $bledy[] = 'Formularz wygasł. Odśwież stronę i spróbuj ponownie.';
    } else {
        foreach ($dane as $pole => $_) {
            $dane[$pole] = trim($_POST[$pole] ?? '');
        }
        $haslo = $_POST['haslo'] ?? '';

        if ($dane['imie'] === '' || mb_strlen($dane['imie']) > 50) {
            $bledy[] = 'Podaj imię (maksymalnie 50 znaków).';
        }
        if ($dane['nazwisko'] === '' || mb_strlen($dane['nazwisko']) > 80) {
            $bledy[] = 'Podaj nazwisko (maksymalnie 80 znaków).';
        }
        if (!filter_var($dane['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($dane['email']) > 150) {
            $bledy[] = 'Podaj poprawny adres email.';
        }
        if ($dane['telefon'] !== '' && !preg_match('/^[0-9 +\-]{7,20}$/', $dane['telefon'])) {
            $bledy[] = 'Telefon może zawierać cyfry, spacje, plus i myślnik (7-20 znaków).';
        }
        if (mb_strlen($haslo) < 8) {
            $bledy[] = 'Hasło musi mieć co najmniej 8 znaków.';
        }

        if (!$bledy) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM uzytkownicy WHERE email = ?');
            $stmt->execute([$dane['email']]);
            if ($stmt->fetchColumn() > 0) {
                $bledy[] = 'Użytkownik z takim adresem email już istnieje.';
            }
        }

        if (!$bledy) {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                'INSERT INTO uzytkownicy (imie, nazwisko, email, haslo, telefon, rola, aktywny) VALUES (?, ?, ?, ?, ?, ?, 1)'
            );
            $stmt->execute([
                $dane['imie'], $dane['nazwisko'], $dane['email'],
                password_hash($haslo, PASSWORD_DEFAULT),
                $dane['telefon'] === '' ? null : $dane['telefon'],
                'pracownik',
            ]);
            $idUzytkownika = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare('INSERT INTO pracownicy (id_uzytkownika, opis, aktywny) VALUES (?, ?, 1)');
            $stmt->execute([$idUzytkownika, $dane['opis']]);
            $pdo->commit();

            $_SESSION['komunikat'] = 'Dodano pracownika.';
            header('Location: ' . BAZA_URL . '/admin/pracownicy.php');
            exit;
        }
    }
}

$pracownicy = $pdo->query(
    'SELECT p.id_pracownika, u.imie, u.nazwisko, u.email, u.telefon, p.opis, p.aktywny
     FROM pracownicy p
     JOIN uzytkownicy u ON u.id_uzytkownika = p.id_uzytkownika
     ORDER BY u.nazwisko, u.imie'
)->fetchAll(PDO::FETCH_ASSOC);

$tytul = 'Pracownicy';
require_once __DIR__ . '/../includes/admin_naglowek.php';
?>
<h1>Pracownicy</h1>

<?php if ($komunikat): ?>
    <div class="sukces"><?= e($komunikat) ?></div>
<?php endif; ?>

<?php foreach ($bledy as $blad): ?>
    <div class="blad"><?= e($blad) ?></div>
<?php endforeach; ?>

<table>
    <thead>
        <tr>
            <th>Imię i nazwisko</th>
            <th>Email</th>
            <th>Telefon</th>
            <th>Opis</th>
            <th>Aktywny</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!$pracownicy): ?>
            <tr><td colspan="5">Brak pracowników.</td></tr>
        <?php endif; ?>
        <?php foreach ($pracownicy as $p): ?>
            <tr>
                <td><?= e($p['imie'] . ' ' . $p['nazwisko']) ?></td>
                <td><?= e($p['email']) ?></td>
                <td><?= e($p['telefon']) ?></td>
                <td><?= e($p['opis']) ?></td>
                <td><?= $p['aktywny'] ? 'Tak' : 'Nie' ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Dodaj pracownika</h2>
<form method="post" class="formularz">
    <?= csrf_pole() ?>

    <label for="imie">Imię</label>
    <input type="text" id="imie" name="imie" maxlength="50" value="<?= e($dane['imie']) ?>" required>

    <label for="nazwisko">Nazwisko</label>
    <input type="text" id="nazwisko" name="nazwisko" maxlength="80" value="<?= e($dane['nazwisko']) ?>" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" maxlength="150" value="<?= e($dane['email']) ?>" required>

    <label for="telefon">Telefon</label>
    <input type="tel" id="telefon" name="telefon" maxlength="20" value="<?= e($dane['telefon']) ?>">

    <label for="haslo">Hasło (min. 8 znaków)</label>
    <input type="password" id="haslo" name="haslo" required>

    <label for="opis">Opis</label>
    <textarea id="opis" name="opis" rows="3"><?= e($dane['opis']) ?></textarea>

    <button type="submit">Dodaj</button>
</form>
<?php require_once __DIR__ . '/../includes/stopka.php'; ?>