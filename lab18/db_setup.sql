-- SQL dla Laboratorium 18 - Internetowa Galeria Zdjęć
-- Baza danych: pszczolk_z18

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tabela Użytkownicy
DROP TABLE IF EXISTS `uzytkownicy`;
CREATE TABLE `uzytkownicy` (
  `idu` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `haslo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rola` ENUM('user', 'admin') DEFAULT 'user',
  `data_rejestracji` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idu`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela Galerie
DROP TABLE IF EXISTS `galerie`;
CREATE TABLE `galerie` (
  `idg` int(11) NOT NULL AUTO_INCREMENT,
  `idu` int(11) NOT NULL,
  `nazwa_galerii` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `czy_komercyjna` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`idg`),
  CONSTRAINT `fk_galerie_uzytkownicy` FOREIGN KEY (`idu`) REFERENCES `uzytkownicy` (`idu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela Zdjęcia
DROP TABLE IF EXISTS `zdjecia`;
CREATE TABLE `zdjecia` (
  `idz` int(11) NOT NULL AUTO_INCREMENT,
  `idg` int(11) NOT NULL,
  `idu` int(11) NOT NULL,
  `tytul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opis` text COLLATE utf8mb4_unicode_ci,
  `plik` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filtr` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `datagodzina` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idz`),
  CONSTRAINT `fk_zdjecia_galerie` FOREIGN KEY (`idg`) REFERENCES `galerie` (`idg`) ON DELETE CASCADE,
  CONSTRAINT `fk_zdjecia_uzytkownicy` FOREIGN KEY (`idu`) REFERENCES `uzytkownicy` (`idu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela Komentarze
DROP TABLE IF EXISTS `komentarze`;
CREATE TABLE `komentarze` (
  `idk` int(11) NOT NULL AUTO_INCREMENT,
  `idz` int(11) NOT NULL,
  `idu` int(11) NOT NULL,
  `tresc` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `datagodzina` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idk`),
  CONSTRAINT `fk_komentarze_zdjecia` FOREIGN KEY (`idz`) REFERENCES `zdjecia` (`idz`) ON DELETE CASCADE,
  CONSTRAINT `fk_komentarze_uzytkownicy` FOREIGN KEY (`idu`) REFERENCES `uzytkownicy` (`idu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabela Oceny
DROP TABLE IF EXISTS `oceny`;
CREATE TABLE `oceny` (
  `ido` int(11) NOT NULL AUTO_INCREMENT,
  `idz` int(11) NOT NULL,
  `idu` int(11) NOT NULL,
  `ocena` int(1) NOT NULL,
  PRIMARY KEY (`ido`),
  UNIQUE KEY `unique_ocena` (`idz`, `idu`),
  CONSTRAINT `fk_oceny_zdjecia` FOREIGN KEY (`idz`) REFERENCES `zdjecia` (`idz`) ON DELETE CASCADE,
  CONSTRAINT `fk_oceny_uzytkownicy` FOREIGN KEY (`idu`) REFERENCES `uzytkownicy` (`idu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- SEEDOWANIE DANYCH

-- Użytkownicy (hasło: admin)
INSERT INTO `uzytkownicy` (`idu`, `login`, `haslo`, `rola`) VALUES 
(1, 'admin', '$2y$10$89v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy', 'admin')
ON DUPLICATE KEY UPDATE rola=VALUES(rola);

-- Galerie
INSERT INTO `galerie` (`idg`, `idu`, `nazwa_galerii`, `czy_komercyjna`) VALUES 
(1, 1, 'Portrety', 0),
(2, 1, 'Krajobrazy', 0),
(3, 1, 'Sesje Komercyjne', 1)
ON DUPLICATE KEY UPDATE nazwa_galerii=VALUES(nazwa_galerii);
