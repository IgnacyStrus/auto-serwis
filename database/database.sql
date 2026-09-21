CREATE DATABASE IF NOT EXISTS auto_serwis
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE auto_serwis;

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
