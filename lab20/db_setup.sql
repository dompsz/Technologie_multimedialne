-- SQL dla Laboratorium 20 - Portal Ogłoszeniowy + GIS
-- Baza danych: pszczolk_z20

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tabela Użytkownicy
DROP TABLE IF EXISTS `uzytkownicy`;
CREATE TABLE `uzytkownicy` (
  `idu` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `haslo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rola` ENUM('zalogowany', 'moderator', 'admin') DEFAULT 'zalogowany',
  `data_rejestracji` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idu`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela Kategorie Ogłoszeń
DROP TABLE IF EXISTS `kategorie_ogloszen`;
CREATE TABLE `kategorie_ogloszen` (
  `idko` int(11) NOT NULL AUTO_INCREMENT,
  `nazwa_kategorii` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idko`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela Ogłoszenia
DROP TABLE IF EXISTS `ogloszenia`;
CREATE TABLE `ogloszenia` (
  `ido` int(11) NOT NULL AUTO_INCREMENT,
  `idko` int(11) NOT NULL,
  `idu` int(11) NOT NULL,
  `kod_pocztowy` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tytul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tresc` text COLLATE utf8mb4_unicode_ci,
  `cena` decimal(10,2) DEFAULT NULL,
  `lokalizacja_tekst` varchar(255) COLLATE utf8mb4_unicode_ci,
  `lat` double DEFAULT NULL,
  `lng` double DEFAULT NULL,
  `datagodzina` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ido`),
  CONSTRAINT `fk_ogloszenia_kategorie` FOREIGN KEY (`idko`) REFERENCES `kategorie_ogloszen` (`idko`) ON DELETE CASCADE,
  CONSTRAINT `fk_ogloszenia_uzytkownicy` FOREIGN KEY (`idu`) REFERENCES `uzytkownicy` (`idu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela Logi Ogłoszeń
DROP TABLE IF EXISTS `logi_ogloszen`;
CREATE TABLE `logi_ogloszen` (
  `idlo` int(11) NOT NULL AUTO_INCREMENT,
  `ido` int(11) DEFAULT NULL,
  `idu` int(11) DEFAULT NULL,
  `akcja` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datagodzina` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idlo`),
  CONSTRAINT `fk_logi_ogloszenia` FOREIGN KEY (`ido`) REFERENCES `ogloszenia` (`ido`) ON DELETE SET NULL,
  CONSTRAINT `fk_logi_uzytkownicy` FOREIGN KEY (`idu`) REFERENCES `uzytkownicy` (`idu`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabela Zdjęcia Ogłoszeń
DROP TABLE IF EXISTS `ogloszenia_zdjecia`;
CREATE TABLE `ogloszenia_zdjecia` (
  `idz` int(11) NOT NULL AUTO_INCREMENT,
  `ido` int(11) NOT NULL,
  `plik` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idz`),
  CONSTRAINT `fk_zdjecia_ogloszenia` FOREIGN KEY (`ido`) REFERENCES `ogloszenia` (`ido`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- SEEDOWANIE DANYCH

-- Użytkownicy (hasło: admin)
INSERT INTO `uzytkownicy` (`idu`, `login`, `haslo`, `rola`) VALUES 
(1, 'admin', '$2y$10$89v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy', 'admin')
ON DUPLICATE KEY UPDATE rola=VALUES(rola);

-- Kategorie
INSERT INTO `kategorie_ogloszen` (`idko`, `nazwa_kategorii`) VALUES 
(1, 'Nieruchomości'),
(2, 'Motoryzacja'),
(3, 'Praca'),
(4, 'Usługi')
ON DUPLICATE KEY UPDATE nazwa_kategorii=VALUES(nazwa_kategorii);

-- Przykładowe ogłoszenie
INSERT INTO `ogloszenia` (`idko`, `idu`, `tytul`, `tresc`, `cena`, `lokalizacja_tekst`, `lat`, `lng`) VALUES
(1, 1, 'Mieszkanie w centrum', 'Piękne mieszkanie 2-pokojowe.', 350000.00, 'Warszawa, Al. Jerozolimskie', 52.2297, 21.0122);
