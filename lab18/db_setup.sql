-- SQL dla Laboratorium 18 - Internetowa Galeria Zdjęć
-- Baza danych: pszczolk_z18

-- 1. Tabela Użytkownicy
CREATE TABLE IF NOT EXISTS `uzytkownicy` (
  `idu` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `haslo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rola` ENUM('user', 'admin') DEFAULT 'user',
  `data_rejestracji` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idu`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela Galerie
CREATE TABLE IF NOT EXISTS `galerie` (
  `idg` int(11) NOT NULL AUTO_INCREMENT,
  `idu` int(11) NOT NULL,
  `nazwa_galerii` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `czy_komercyjna` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`idg`),
  CONSTRAINT `fk_galerie_uzytkownicy` FOREIGN KEY (`idu`) REFERENCES `uzytkownicy` (`idu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela Zdjęcia
CREATE TABLE IF NOT EXISTS `zdjecia` (
  `idz` int(11) NOT NULL AUTO_INCREMENT,
  `idg` int(11) NOT NULL,
  `idu` int(11) NOT NULL,
  `tytul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opis` text COLLATE utf8mb4_unicode_ci,
  `plik` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datagodzina` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idz`),
  CONSTRAINT `fk_zdjecia_galerie` FOREIGN KEY (`idg`) REFERENCES `galerie` (`idg`) ON DELETE CASCADE,
  CONSTRAINT `fk_zdjecia_uzytkownicy` FOREIGN KEY (`idu`) REFERENCES `uzytkownicy` (`idu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela Komentarze
CREATE TABLE IF NOT EXISTS `komentarze` (
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
CREATE TABLE IF NOT EXISTS `oceny` (
  `ido` int(11) NOT NULL AUTO_INCREMENT,
  `idz` int(11) NOT NULL,
  `idu` int(11) NOT NULL,
  `ocena` int(1) NOT NULL,
  PRIMARY KEY (`ido`),
  UNIQUE KEY `unique_ocena` (`idz`, `idu`),
  CONSTRAINT `fk_oceny_zdjecia` FOREIGN KEY (`idz`) REFERENCES `zdjecia` (`idz`) ON DELETE CASCADE,
  CONSTRAINT `fk_oceny_uzytkownicy` FOREIGN KEY (`idu`) REFERENCES `uzytkownicy` (`idu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabela Cenzura
CREATE TABLE IF NOT EXISTS `cenzura` (
  `idc` int(11) NOT NULL AUTO_INCREMENT,
  `slowo_zakazane` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zamiennik` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '***',
  PRIMARY KEY (`idc`),
  UNIQUE KEY `slowo_zakazane` (`slowo_zakazane`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEEDOWANIE DANYCH

-- Cenzura
INSERT INTO `cenzura` (`slowo_zakazane`, `zamiennik`) VALUES 
('cholera', 'motyla noga'),
('gupi', 'niemądry'),
('brzydkie_slowo', '***')
ON DUPLICATE KEY UPDATE zamiennik=VALUES(zamiennik);

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
