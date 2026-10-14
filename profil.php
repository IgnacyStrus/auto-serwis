<?php
require_once 'includes/auth.php';
require_once 'includes/csrf.php';

wymagaj_logowania();

$pdo = polacz_z_baza();
$id = (int) $_SESSION['id_uzytkownika'];

$stmt = $pdo->prepare('SELECT imie, nazwisko, email, telefon, haslo FROM uzytkownicy WHERE id_uzytkownika = ?');
$stmt->execute([$id]);
$uzytkownik = $stmt->fetch();

if (!$uzytkownik) {
    wyloguj();
    header('Location: ' . BAZA_URL . '/logowanie.php');
    exit;
}

$dane = [
    'imie' => $uzytkownik['imie'],
    'nazwisko' => $uzytkownik['nazwisko'],
    'email' => $uzytkownik['email'],
    'telefon' => $uzytkownik['telefon'],
];

$blad_ogolny = '';
$bledy_profil = [];
$bledy_haslo = [];
$sukces_profil = '';
$sukces_haslo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $akcja = is_string($_POST['akcja'] ?? null) ? $_POST['akcja'] : '';

    if (!csrf_sprawdz()) {
        $blad_ogolny = 'Formularz wygasł. Odśwież stronę i spróbuj ponownie.';
    } elseif ($akcja === 'profil') {
        foreach (array_keys($dane) as $pole) {
            $dane[$pole] = is_string($_POST[$pole] ?? null) ? trim($_POST[$pole]) : '';
        }

        if (!preg_match('/^\p{L}+([ \'-]\p{L}+)*$/u', $dane['imie']) || mb_strlen($dane['imie']) > 50) {
            $bledy_profil[] = 'Podaj imię (same litery, maksymalnie 50 znaków).';
        }
        if (!preg_match('/^\p{L}+([ \'-]\p{L}+)*$/u', $dane['nazwisko']) || mb_strlen($dane['nazwisko']) > 80) {
            $bledy_profil[] = 'Podaj nazwisko (same litery, maksymalnie 80 znaków).';
        }
        if (!filter_var($dane['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($dane['email']) > 150) {
            $bledy_profil[] = 'Podaj poprawny adres e-mail.';
        }
        if (!preg_match('/^\+?[0-9 -]{7,20}$/', $dane['telefon'])) {
            $bledy_profil[] = 'Podaj poprawny numer telefonu (tylko cyfry, opcjonalnie + na początku, 7-20 znaków).';
        }

        if (!$bledy_profil) {
            $stmt = $pdo->prepare('SELECT 1 FROM uzytkownicy WHERE email = ? AND id_uzytkownika <> ?');
            $stmt->execute([$dane['email'], $id]);

            if ($stmt->fetch()) {
                $bledy_profil[] = 'Ten adres e-mail jest już używany przez inne konto.';
            } else {
                try {
                    $stmt = $pdo->prepare(
                        'UPDATE uzytkownicy SET imie = ?, nazwisko = ?, email = ?, telefon = ? WHERE id_uzytkownika = ?'
                    );
                    $stmt->execute([$dane['imie'], $dane['nazwisko'], $dane['email'], $dane['telefon'], $id]);

                    $_SESSION['imie'] = $dane['imie'];
                    $sukces_profil = 'Dane zostały zapisane.';
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $bledy_profil[] = $e->getCode() === '23000'
                        ? 'Ten adres e-mail jest już używany przez inne konto.'
                        : 'Nie udało się zapisać danych. Spróbuj ponownie później.';
                }
            }
        }
    } elseif ($akcja === 'haslo') {
        $stare = is_string($_POST['stare_haslo'] ?? null) ? $_POST['stare_haslo'] : '';
        $nowe = is_string($_POST['nowe_haslo'] ?? null) ? $_POST['nowe_haslo'] : '';
        $nowe2 = is_string($_POST['nowe_haslo2'] ?? null) ? $_POST['nowe_haslo2'] : '';

        if ($stare === '' || !password_verify($stare, $uzytkownik['haslo'])) {
            $bledy_haslo[] = 'Obecne hasło jest nieprawidłowe.';
        }
        if (strlen($nowe) < 8 || strlen($nowe) > 72) {
            $bledy_haslo[] = 'Nowe hasło musi mieć od 8 do 72 znaków.';
        }
        if ($nowe !== $nowe2) {
            $bledy_haslo[] = 'Nowe hasła nie są takie same.';
        }
        if ($nowe !== '' && $nowe === $stare) {
            $bledy_haslo[] = 'Nowe hasło musi być inne niż obecne.';
        }

        if (!$bledy_haslo) {
            try {
                $stmt = $pdo->prepare('UPDATE uzytkownicy SET haslo = ? WHERE id_uzytkownika = ?');
                $stmt->execute([password_hash($nowe, PASSWORD_DEFAULT), $id]);

                session_regenerate_id(true);
                $sukces_haslo = 'Hasło zostało zmienione.';
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $bledy_haslo[] = 'Nie udało się zmienić hasła. Spróbuj ponownie później.';
            }
        }
    }
}

$tytul = 'Profil';
require 'includes/naglowek.php';
?>
    <h1>Mój profil</h1>

    <?php if ($blad_ogolny !== ''): ?>
        <p class="blad"><?= e($blad_ogolny) ?></p>
    <?php endif; ?>

    <h2>Dane osobowe</h2>

    <?php foreach ($bledy_profil as $blad): ?>
        <p class="blad"><?= e($blad) ?></p>
    <?php endforeach; ?>
    <?php if ($sukces_profil !== ''): ?>
        <p class="sukces"><?= e($sukces_profil) ?></p>
    <?php endif; ?>

    <form method="post" action="profil.php">
        <?= csrf_pole() ?>
        <input type="hidden" name="akcja" value="profil">

        <label for="imie">Imię</label>
        <input type="text" id="imie" name="imie" value="<?= e($dane['imie']) ?>" maxlength="50" required>

        <label for="nazwisko">Nazwisko</label>
        <input type="text" id="nazwisko" name="nazwisko" value="<?= e($dane['nazwisko']) ?>" maxlength="80" required>

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= e($dane['email']) ?>" maxlength="150" required>

        <label for="telefon">Telefon</label>
        <input type="tel" id="telefon" name="telefon" value="<?= e($dane['telefon']) ?>" maxlength="20" pattern="\+?[0-9 \-]{7,20}" title="Tylko cyfry, opcjonalnie + na początku" required>

        <button type="submit">Zapisz zmiany</button>
    </form>

    <h2>Zmiana hasła</h2>

    <?php foreach ($bledy_haslo as $blad): ?>
        <p class="blad"><?= e($blad) ?></p>
    <?php endforeach; ?>
    <?php if ($sukces_haslo !== ''): ?>
        <p class="sukces"><?= e($sukces_haslo) ?></p>
    <?php endif; ?>

    <form method="post" action="profil.php">
        <?= csrf_pole() ?>
        <input type="hidden" name="akcja" value="haslo">

        <label for="stare_haslo">Obecne hasło</label>
        <input type="password" id="stare_haslo" name="stare_haslo" required>

        <label for="nowe_haslo">Nowe hasło (min. 8 znaków)</label>
        <input type="password" id="nowe_haslo" name="nowe_haslo" minlength="8" maxlength="72" required>

        <label for="nowe_haslo2">Powtórz nowe hasło</label>
        <input type="password" id="nowe_haslo2" name="nowe_haslo2" minlength="8" maxlength="72" required>

        <button type="submit">Zmień hasło</button>
    </form>

    <p><a href="<?= e(adres_panelu($_SESSION['rola'])) ?>">Wróć do panelu</a></p>
<?php require 'includes/stopka.php'; ?>