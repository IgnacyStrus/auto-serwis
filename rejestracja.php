<?php
require_once 'includes/auth.php';
require_once 'includes/csrf.php';

if (czy_zalogowany()) {
    header('Location: ' . adres_panelu($_SESSION['rola']));
    exit;
}

$bledy = [];
$dane = ['imie' => '', 'nazwisko' => '', 'email' => '', 'telefon' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($dane) as $pole) {
        $dane[$pole] = is_string($_POST[$pole] ?? null) ? trim($_POST[$pole]) : '';
    }
    $haslo = is_string($_POST['haslo'] ?? null) ? $_POST['haslo'] : '';
    $haslo2 = is_string($_POST['haslo2'] ?? null) ? $_POST['haslo2'] : '';

    if (!csrf_sprawdz()) {
        $bledy[] = 'Formularz wygasł. Odśwież stronę i spróbuj ponownie.';
    }
    if (!preg_match('/^\p{L}+([ \'-]\p{L}+)*$/u', $dane['imie']) || mb_strlen($dane['imie']) > 50) {
        $bledy[] = 'Podaj imię (same litery, maksymalnie 50 znaków).';
    }
    if (!preg_match('/^\p{L}+([ \'-]\p{L}+)*$/u', $dane['nazwisko']) || mb_strlen($dane['nazwisko']) > 80) {
        $bledy[] = 'Podaj nazwisko (same litery, maksymalnie 80 znaków).';
    }
    if (!filter_var($dane['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($dane['email']) > 150) {
        $bledy[] = 'Podaj poprawny adres e-mail.';
    }
    if (!preg_match('/^\+?[0-9 -]{7,20}$/', $dane['telefon'])) {
        $bledy[] = 'Podaj poprawny numer telefonu (tylko cyfry, opcjonalnie + na początku, 7-20 znaków).';
    }
    if (strlen($haslo) < 8 || strlen($haslo) > 72) {
        $bledy[] = 'Hasło musi mieć od 8 do 72 znaków.';
    }
    if ($haslo !== $haslo2) {
        $bledy[] = 'Hasła nie są takie same.';
    }

    if (!$bledy) {
        $pdo = polacz_z_baza();
        $stmt = $pdo->prepare('SELECT 1 FROM uzytkownicy WHERE email = ?');
        $stmt->execute([$dane['email']]);

        if ($stmt->fetch()) {
            $bledy[] = 'Konto z tym adresem e-mail już istnieje.';
        } else {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO uzytkownicy (imie, nazwisko, email, haslo, telefon, rola, aktywny)
                     VALUES (?, ?, ?, ?, ?, \'klient\', 1)'
                );
                $stmt->execute([
                    $dane['imie'],
                    $dane['nazwisko'],
                    $dane['email'],
                    password_hash($haslo, PASSWORD_DEFAULT),
                    $dane['telefon'],
                ]);

                zaloguj([
                    'id_uzytkownika' => (int) $pdo->lastInsertId(),
                    'imie' => $dane['imie'],
                    'rola' => 'klient',
                ]);
                header('Location: ' . adres_panelu('klient'));
                exit;
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $bledy[] = $e->getCode() === '23000'
                    ? 'Konto z tym adresem e-mail już istnieje.'
                    : 'Nie udało się utworzyć konta. Spróbuj ponownie później.';
            }
        }
    }
}

$tytul = 'Rejestracja';
require 'includes/naglowek.php';
?>
    <h1>Rejestracja</h1>

    <?php foreach ($bledy as $blad): ?>
        <p class="blad"><?= e($blad) ?></p>
    <?php endforeach; ?>

    <form method="post" action="rejestracja.php">
        <?= csrf_pole() ?>

        <label for="imie">Imię</label>
        <input type="text" id="imie" name="imie" value="<?= e($dane['imie']) ?>" maxlength="50" required>

        <label for="nazwisko">Nazwisko</label>
        <input type="text" id="nazwisko" name="nazwisko" value="<?= e($dane['nazwisko']) ?>" maxlength="80" required>

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= e($dane['email']) ?>" maxlength="150" required>

        <label for="telefon">Telefon</label>
        <input type="tel" id="telefon" name="telefon" value="<?= e($dane['telefon']) ?>" maxlength="20" pattern="\+?[0-9 \-]{7,20}" title="Tylko cyfry, opcjonalnie + na początku" required>

        <label for="haslo">Hasło (min. 8 znaków)</label>
        <input type="password" id="haslo" name="haslo" minlength="8" maxlength="72" required>

        <label for="haslo2">Powtórz hasło</label>
        <input type="password" id="haslo2" name="haslo2" minlength="8" maxlength="72" required>

        <button type="submit">Zarejestruj się</button>
    </form>

    <p>Masz już konto? <a href="logowanie.php">Zaloguj się</a></p>
<?php require 'includes/stopka.php'; ?>