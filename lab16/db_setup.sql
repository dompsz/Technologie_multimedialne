-- SQL dla Laboratorium 16 - System CMS
-- Baza danych: pszczolk_z16

-- 1. Tabela Użytkownicy (Administratorzy i Redaktorzy)
CREATE TABLE IF NOT EXISTS `uzytkownicy` (
  `idu` int(11) NOT NULL AUTO_INCREMENT,
  `nazwa_uzytkownika` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `haslo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rola` ENUM('redaktor', 'admin') DEFAULT 'redaktor',
  `data_utworzenia` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idu`),
  UNIQUE KEY `nazwa_uzytkownika` (`nazwa_uzytkownika`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela Kategorie
CREATE TABLE IF NOT EXISTS `kategorie` (
  `idk` int(11) NOT NULL AUTO_INCREMENT,
  `nazwa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idk`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela Podstrony (Artykuły)
CREATE TABLE IF NOT EXISTS `podstrony` (
  `idp` int(11) NOT NULL AUTO_INCREMENT,
  `tytul` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tresc` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idk` int(11) DEFAULT NULL,
  `idu` int(11) NOT NULL,
  `data_publikacji` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aktualizacji` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` ENUM('szkic', 'opublikowany') DEFAULT 'szkic',
  PRIMARY KEY (`idp`),
  UNIQUE KEY `slug` (`slug`),
  CONSTRAINT `fk_podstrony_kategorie` FOREIGN KEY (`idk`) REFERENCES `kategorie` (`idk`) ON DELETE SET NULL,
  CONSTRAINT `fk_podstrony_uzytkownicy` FOREIGN KEY (`idu`) REFERENCES `uzytkownicy` (`idu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela Logi Logowania
CREATE TABLE IF NOT EXISTS `logi_logowania` (
  `idl` int(11) NOT NULL AUTO_INCREMENT,
  `login_attempted` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idu` int(11) DEFAULT NULL,
  `datagodzina` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `przegladarka` text COLLATE utf8mb4_unicode_ci,
  `system` text COLLATE utf8mb4_unicode_ci,
  `stan` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1-sukces, 0-porażka',
  PRIMARY KEY (`idl`),
  CONSTRAINT `fk_logi_uzytkownicy` FOREIGN KEY (`idu`) REFERENCES `uzytkownicy` (`idu`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabela Słownik Bota
CREATE TABLE IF NOT EXISTS `slownik_bota` (
  `ids` int(11) NOT NULL AUTO_INCREMENT,
  `pytanie_klucz` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `odpowiedz` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ids`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabela Logi Bota
CREATE TABLE IF NOT EXISTS `logi_bota` (
  `idl` int(11) NOT NULL AUTO_INCREMENT,
  `zapytanie_uzytkownika` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_godzina` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `czy_znaleziono_odp` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`idl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEEDOWANIE DANYCH

-- Kategorie
INSERT INTO `kategorie` (`nazwa`, `slug`) VALUES 
('Technologie', 'technologie'),
('Multimedia', 'multimedia'),
('Web Development', 'web-development')
ON DUPLICATE KEY UPDATE nazwa=VALUES(nazwa);

-- Słownik Bota
INSERT INTO `slownik_bota` (`pytanie_klucz`, `odpowiedz`) VALUES 
('cześć, witaj, hej', 'Witaj! Jestem inteligentnym botem CMS. O co chciałbyś zapytać?'),
('kontakt, telefon, mail', 'Możesz się z nami skontaktować pod numerem +48 123 456 789 lub mailowo: kontakt@przyklad.pl'),
('godziny, otwarcia', 'Pracujemy od poniedziałku do piątku w godzinach 8:00 - 16:00.')
ON DUPLICATE KEY UPDATE odpowiedz=VALUES(odpowiedz);

-- Konta testowe (hasła: admin, redaktor)
-- Hasła standardowe: admin -> admin, redaktor -> redaktor
INSERT INTO `uzytkownicy` (`idu`, `nazwa_uzytkownika`, `haslo`, `rola`) VALUES 
(1, 'admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'admin'),
(2, 'redaktor1', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'redaktor')
ON DUPLICATE KEY UPDATE rola=VALUES(rola);

-- Przykładowa podstrona
INSERT INTO `podstrony` (`tytul`, `tresc`, `slug`, `idk`, `idu`, `status`) VALUES 
('Witaj w naszym CMS', '<p>To jest pierwsza strona stworzona w naszym systemie CMS.</p>', 'witaj-w-naszym-cms', 1, 1, 'opublikowany')
ON DUPLICATE KEY UPDATE tytul=VALUES(tytul);
