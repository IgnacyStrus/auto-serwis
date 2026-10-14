# Auto Serwis

## Autorzy

Stefaniuk Gabriel
Ignacy Strus

## Opis projektu

Auto Serwis to internetowy system rezerwacji wizyt w warsztacie samochodowym. Klient będzie mógł wybrać usługę, mechanika, datę oraz wolną godzinę wizyty.

System obejmuje role klienta, pracownika oraz administratora.

## Technologie

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- Git i GitHub
- XAMPP
- phpMyAdmin

## Struktura projektu

```text
README.md
index.php
logowanie.php
wylogowanie.php
rejestracja.php
profil.php
admin/
  index.php
pracownik/
  index.php
klient/
  index.php
database/
  database.sql
  Diagram ERD.png
css/
  style.css
js/
  app.js
images/
includes/
  db.php
  auth.php
  csrf.php
  limit_logowan.php
  naglowek.php
  stopka.php
```

## Instrukcja uruchomienia

1. Zainstaluj program XAMPP.
2. Uruchom w XAMPP usługi Apache i MySQL.
3. Skopiuj projekt do folderu:

```text
   C:\xampp\htdocs\auto-serwis
```

4. Otwórz phpMyAdmin pod adresem:

```text
   http://localhost/phpmyadmin/
```

5. Zaimportuj plik:

```text
   database/database.sql
```

6. Otwórz projekt w przeglądarce pod adresem:

```text
   http://localhost/auto-serwis/
```

## Konta testowe

Wszystkie konta z pliku `database.sql` mają hasło `Test1234!`.

- Administrator: `admin@autoservis.pl`
- Pozostałe konta (pracownicy i klienci) są w tabeli `uzytkownicy`.

## Funkcje etapu 2: użytkownicy, rejestracja i logowanie

- Rejestracja klienta z walidacją po stronie serwera (wymagane pola, format e-maila, zgodność haseł, unikalny e-mail).
- Logowanie z hasłami hashowanymi przez `password_hash()` i `password_verify()`.
- Sesje z regeneracją identyfikatora po zalogowaniu, limitem czasu nieaktywności i wylogowaniem.
- Trzy role (klient, pracownik, administrator) z kontrolą dostępu po stronie PHP. Każda rola ma własny panel, a wejście na cudzy przez adres URL kończy się komunikatem "Brak dostępu".
- Profil użytkownika z edycją danych oraz zmianą hasła po podaniu obecnego.
- Zabezpieczenia: prepared statements (PDO), ochrona przed XSS (funkcja `e()`), tokeny CSRF w formularzach, limit prób logowania (5 błędnych prób, blokada na 10 minut).
- Wspólny nagłówek i stopka oraz arkusz stylów dla wszystkich stron.
