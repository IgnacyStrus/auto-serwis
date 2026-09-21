CREATE DATABASE IF NOT EXISTS auto_serwis
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE auto_serwis;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS historia_statusow_rezerwacji;
DROP TABLE IF EXISTS rezerwacje;
DROP TABLE IF EXISTS dni_wolne_pracownikow;
DROP TABLE IF EXISTS przerwy_pracownikow;
DROP TABLE IF EXISTS dostepnosc_pracownikow;
DROP TABLE IF EXISTS uslugi_pracownikow;
DROP TABLE IF EXISTS pracownicy;
DROP TABLE IF EXISTS uslugi;
DROP TABLE IF EXISTS kategorie_uslug;
DROP TABLE IF EXISTS uzytkownicy;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE uzytkownicy (
    id_uzytkownika INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    imie VARCHAR(50) NOT NULL,
    nazwisko VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    haslo VARCHAR(255) NOT NULL,
    telefon VARCHAR(20) NOT NULL,
    rola ENUM('klient', 'pracownik', 'administrator') NOT NULL DEFAULT 'klient',
    aktywny TINYINT(1) NOT NULL DEFAULT 1,
    data_utworzenia TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE kategorie_uslug (
    id_kategorii INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nazwa VARCHAR(100) NOT NULL,
    opis TEXT,
    aktywna TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE uslugi (
    id_uslugi INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_kategorii INT UNSIGNED NOT NULL,
    nazwa VARCHAR(150) NOT NULL,
    opis TEXT,
    czas_trwania_minuty SMALLINT UNSIGNED NOT NULL,
    cena DECIMAL(10,2) NOT NULL,
    aktywna TINYINT(1) NOT NULL DEFAULT 1,

    CONSTRAINT fk_uslugi_kategorie
        FOREIGN KEY (id_kategorii)
        REFERENCES kategorie_uslug(id_kategorii)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE pracownicy (
    id_pracownika INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_uzytkownika INT UNSIGNED NOT NULL UNIQUE,
    opis TEXT,
    aktywny TINYINT(1) NOT NULL DEFAULT 1,

    CONSTRAINT fk_pracownicy_uzytkownicy
        FOREIGN KEY (id_uzytkownika)
        REFERENCES uzytkownicy(id_uzytkownika)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE uslugi_pracownikow (
    id_pracownika INT UNSIGNED NOT NULL,
    id_uslugi INT UNSIGNED NOT NULL,

    PRIMARY KEY (id_pracownika, id_uslugi),

    CONSTRAINT fk_uslugi_pracownikow_pracownicy
        FOREIGN KEY (id_pracownika)
        REFERENCES pracownicy(id_pracownika)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_uslugi_pracownikow_uslugi
        FOREIGN KEY (id_uslugi)
        REFERENCES uslugi(id_uslugi)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE dostepnosc_pracownikow (
    id_dostepnosci INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pracownika INT UNSIGNED NOT NULL,
    dzien_tygodnia ENUM(
        'poniedzialek',
        'wtorek',
        'sroda',
        'czwartek',
        'piatek',
        'sobota',
        'niedziela'
    ) NOT NULL,
    godzina_rozpoczecia TIME NOT NULL,
    godzina_zakonczenia TIME NOT NULL,

    CONSTRAINT fk_dostepnosc_pracownicy
        FOREIGN KEY (id_pracownika)
        REFERENCES pracownicy(id_pracownika)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT chk_godziny_pracy
        CHECK (godzina_rozpoczecia < godzina_zakonczenia)
) ENGINE=InnoDB;
CREATE TABLE rezerwacje (
    id_rezerwacji INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_klienta INT UNSIGNED NOT NULL,
    id_pracownika INT UNSIGNED NOT NULL,
    id_uslugi INT UNSIGNED NOT NULL,
    data_rezerwacji DATE NOT NULL,
    godzina_rozpoczecia TIME NOT NULL,
    godzina_zakonczenia TIME NOT NULL,
    status ENUM(
        'oczekujaca',
        'potwierdzona',
        'zrealizowana',
        'anulowana'
    ) NOT NULL DEFAULT 'oczekujaca',
    komentarz TEXT,
    data_utworzenia TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_rezerwacje_pracownik_data (id_pracownika, data_rezerwacji),
    INDEX idx_rezerwacje_klient (id_klienta),
    INDEX idx_rezerwacje_status (status),

    CONSTRAINT fk_rezerwacje_klient
        FOREIGN KEY (id_klienta)
        REFERENCES uzytkownicy(id_uzytkownika)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_rezerwacje_pracownik
        FOREIGN KEY (id_pracownika)
        REFERENCES pracownicy(id_pracownika)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_rezerwacje_usluga
        FOREIGN KEY (id_uslugi)
        REFERENCES uslugi(id_uslugi)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_godziny_rezerwacji
        CHECK (godzina_rozpoczecia < godzina_zakonczenia)
) ENGINE=InnoDB;
CREATE TABLE historia_statusow_rezerwacji (
    id_historii INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_rezerwacji INT UNSIGNED NOT NULL,
    stary_status ENUM(
        'oczekujaca',
        'potwierdzona',
        'zrealizowana',
        'anulowana'
    ),
    nowy_status ENUM(
        'oczekujaca',
        'potwierdzona',
        'zrealizowana',
        'anulowana'
    ) NOT NULL,
    id_uzytkownika INT UNSIGNED NOT NULL,
    komentarz TEXT,
    data_zmiany TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_historia_rezerwacja
        FOREIGN KEY (id_rezerwacji)
        REFERENCES rezerwacje(id_rezerwacji)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_historia_uzytkownik
        FOREIGN KEY (id_uzytkownika)
        REFERENCES uzytkownicy(id_uzytkownika)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE TABLE dni_wolne_pracownikow (
    id_dnia_wolnego INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pracownika INT UNSIGNED NOT NULL,
    data_od DATE NOT NULL,
    data_do DATE NOT NULL,
    powod VARCHAR(255),
    data_dodania TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_dni_wolne_pracownik_data (id_pracownika, data_od),

    CONSTRAINT fk_dni_wolne_pracownicy
        FOREIGN KEY (id_pracownika)
        REFERENCES pracownicy(id_pracownika)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT chk_daty_dni_wolne
        CHECK (data_od <= data_do)
) ENGINE=InnoDB;
CREATE TABLE przerwy_pracownikow (
    id_przerwy INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pracownika INT UNSIGNED NOT NULL,
    dzien_tygodnia ENUM(
        'poniedzialek',
        'wtorek',
        'sroda',
        'czwartek',
        'piatek',
        'sobota',
        'niedziela'
    ) NOT NULL,
    godzina_rozpoczecia TIME NOT NULL,
    godzina_zakonczenia TIME NOT NULL,
    opis VARCHAR(255),

    UNIQUE (
        id_pracownika,
        dzien_tygodnia,
        godzina_rozpoczecia,
        godzina_zakonczenia
    ),

    CONSTRAINT fk_przerwy_pracownicy
        FOREIGN KEY (id_pracownika)
        REFERENCES pracownicy(id_pracownika)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT chk_godziny_przerwy
        CHECK (godzina_rozpoczecia < godzina_zakonczenia)
) ENGINE=InnoDB;
INSERT INTO kategorie_uslug (nazwa, opis, aktywna) VALUES
('Mechanika', 'Naprawy mechaniczne samochodow.', 1),
('Opony i kola', 'Wymiana opon oraz geometria kol.', 1),
('Diagnostyka', 'Diagnostyka komputerowa pojazdow.', 1),
('Klimatyzacja', 'Serwis i naprawa klimatyzacji.', 1);

INSERT INTO uzytkownicy (
    imie,
    nazwisko,
    email,
    haslo,
    telefon,
    rola,
    aktywny
) VALUES
(
    'Anna',
    'Nowak',
    'admin@autoservis.pl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
    '500600700',
    'administrator',
    1
),
(
    'Jan',
    'Kowalski',
    'jan.kowalski@autoservis.pl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
    '501601701',
    'pracownik',
    1
),
(
    'Piotr',
    'Wozniak',
    'piotr.wozniak@autoservis.pl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
    '502602702',
    'pracownik',
    1
),
(
    'Tomasz',
    'Zielinski',
    'tomasz.zielinski@autoservis.pl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
    '503603703',
    'pracownik',
    1
),
(
    'Marta',
    'Wisniewska',
    'marta.wisniewska@email.pl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
    '504604704',
    'klient',
    1
),
(
    'Adam',
    'Dabrowski',
    'adam.dabrowski@email.pl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
    '505605705',
    'klient',
    1
),
(
    'Karolina',
    'Maj',
    'karolina.maj@email.pl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
    '506606706',
    'klient',
    1
),
(
    'Michal',
    'Krawczyk',
    'michal.krawczyk@email.pl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
    '507607707',
    'klient',
    1
);
