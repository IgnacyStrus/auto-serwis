<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

if (czy_zalogowany()) {
    header('Location: ' . adres_panelu($_SESSION['rola']));
    exit;
}

$blad = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $haslo = $_POST['haslo'] ?? '';

    if ($email === '' || $haslo === '') {
        $blad = 'Podaj e-mail i hasło.';
    } else {
        $stmt = polacz_z_baza()->prepare(
            'SELECT id_uzytkownika, imie, haslo, rola, aktywny FROM uzytkownicy WHERE email = ?'
        );
        $stmt->execute([$email]);
        $uzytkownik = $stmt->fetch();

        if ($uzytkownik && $uzytkownik['aktywny'] && password_verify($haslo, $uzytkownik['haslo'])) {
            zaloguj($uzytkownik);
            header('Location: ' . adres_panelu($uzytkownik['rola']));
            exit;
        }

        $blad = 'Nieprawidłowy e-mail lub hasło.';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logowanie - Auto Serwis</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Logowanie</h1>

    <?php if ($blad !== ''): ?>
        <p class="blad"><?= htmlspecialchars($blad, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="logowanie.php">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>

        <label for="haslo">Hasło</label>
        <input type="password" id="haslo" name="haslo" required>

        <button type="submit">Zaloguj się</button>
    </form>

    <p>Nie masz konta? <a href="rejestracja.php">Zarejestruj się</a></p>
</body>
</html>
