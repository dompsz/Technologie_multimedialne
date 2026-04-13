-- SQL dla Laboratorium 17 - Forum Dyskusyjne
-- Baza danych: pszczolk_z17

-- 1. Tabela Użytkownicy
CREATE TABLE IF NOT EXISTS `uzytkownicy` (
  `idu` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `haslo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `poziom_uprawnien` int(1) NOT NULL DEFAULT 1 COMMENT '0-gość, 1-użytkownik, 2-moderator, 3-admin',
  `data_rejestracji` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idu`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela Tematy (Kategorie główne)
CREATE TABLE IF NOT EXISTS `tematy` (
  `idt` int(11) NOT NULL AUTO_INCREMENT,
  `nazwa_tematu` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opis` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`idt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela Wątki (Posty główne i odpowiedzi)
CREATE TABLE IF NOT EXISTS `watki` (
  `idw` int(11) NOT NULL AUTO_INCREMENT,
  `idt` int(11) NOT NULL,
  `idu` int(11) NOT NULL,
  `id_rodzic` int(11) DEFAULT NULL COMMENT 'NULL dla nowego wątku, ID wątku dla odpowiedzi',
  `tytul` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tresc` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `datagodzina` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stan` int(1) NOT NULL DEFAULT 1 COMMENT '1-aktywny, 0-zablokowany/ukryty',
  PRIMARY KEY (`idw`),
  CONSTRAINT `fk_watki_tematy` FOREIGN KEY (`idt`) REFERENCES `tematy` (`idt`) ON DELETE CASCADE,
  CONSTRAINT `fk_watki_uzytkownicy` FOREIGN KEY (`idu`) REFERENCES `uzytkownicy` (`idu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela Cenzura
CREATE TABLE IF NOT EXISTS `cenzura` (
  `idc` int(11) NOT NULL AUTO_INCREMENT,
  `slowo_zakazane` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zamiennik` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '***',
  PRIMARY KEY (`idc`),
  UNIQUE KEY `slowo_zakazane` (`slowo_zakazane`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEEDOWANIE DANYCH

-- Tematy
INSERT INTO `tematy` (`nazwa_tematu`, `opis`) VALUES 
('Ogólne', 'Dyskusje na tematy ogólne i powitalne.'),
('Programowanie', 'Wszystko o kodowaniu, PHP, JS i nie tylko.'),
('Multimedia', 'Grafika, wideo i technologie multimedialne.'),
('Gry', 'Najnowsze hity i klasyki retro.')
ON DUPLICATE KEY UPDATE opis=VALUES(opis);

-- Użytkownicy (Hasła: admin, moderator, user1)
INSERT INTO `uzytkownicy` (`login`, `haslo`, `poziom_uprawnien`) VALUES 
('admin', '$2y$10$89v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy', 3),
('moderator', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 2),
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1)
ON DUPLICATE KEY UPDATE poziom_uprawnien=VALUES(poziom_uprawnien);

-- Cenzura
INSERT INTO `cenzura` (`slowo_zakazane`, `zamiennik`) VALUES 
('cholera', 'motyla noga'),
('gupi', 'niemądry'),
('brzydkie_slowo', '***')
ON DUPLICATE KEY UPDATE zamiennik=VALUES(zamiennik);
