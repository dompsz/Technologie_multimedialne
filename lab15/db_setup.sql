-- SQL dla Laboratorium 15 - System CRM
-- Baza danych: pszczolk_z15

-- 1. Tabela Pracownicy
CREATE TABLE IF NOT EXISTS `pracownicy` (
  `idp` int(11) NOT NULL AUTO_INCREMENT,
  `nazwisko` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `haslo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` ENUM('pracownik', 'admin') DEFAULT 'pracownik',
  PRIMARY KEY (`idp`),
  UNIQUE KEY `nazwisko` (`nazwisko`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela Klienci
CREATE TABLE IF NOT EXISTS `klienci` (
  `idk` int(11) NOT NULL AUTO_INCREMENT,
  `nazwisko` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `haslo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idk`),
  UNIQUE KEY `nazwisko` (`nazwisko`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela Logi Pracowników
CREATE TABLE IF NOT EXISTS `logi_pracownikow` (
  `idlp` int(11) NOT NULL AUTO_INCREMENT,
  `idp` int(11) NOT NULL,
  `datagodzina` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `przegladarka` text COLLATE utf8mb4_unicode_ci,
  `system` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`idlp`),
  CONSTRAINT `fk_logi_p_pracownicy` FOREIGN KEY (`idp`) REFERENCES `pracownicy` (`idp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela Logi Klientów
CREATE TABLE IF NOT EXISTS `logi_klientow` (
  `idlk` int(11) NOT NULL AUTO_INCREMENT,
  `idk` int(11) NOT NULL,
  `datagodzina` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `przegladarka` text COLLATE utf8mb4_unicode_ci,
  `system` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`idlk`),
  CONSTRAINT `fk_logi_k_klienci` FOREIGN KEY (`idk`) REFERENCES `klienci` (`idk`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabela Zagadnienia (Kategorie)
CREATE TABLE IF NOT EXISTS `zagadnienia` (
  `idz` int(11) NOT NULL AUTO_INCREMENT,
  `nazwa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idz`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabela Posty (Zgłoszenia)
CREATE TABLE IF NOT EXISTS `posty` (
  `idpo` int(11) NOT NULL AUTO_INCREMENT,
  `idz` int(11) NOT NULL,
  `idk` int(11) NOT NULL,
  `tresc` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `datagodzina` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `priorytet` int(1) NOT NULL DEFAULT 1, -- Skala 1-5
  `stan` int(1) NOT NULL DEFAULT 0, -- 0-oczekujący, 1-w trakcie, 2-zakończony
  `ocena_pracownika` int(1) DEFAULT NULL, -- Dodatkowe: Ocena po zakończeniu (1-5)
  PRIMARY KEY (`idpo`),
  CONSTRAINT `fk_posty_zagadnienia` FOREIGN KEY (`idz`) REFERENCES `zagadnienia` (`idz`) ON DELETE CASCADE,
  CONSTRAINT `fk_posty_klienci` FOREIGN KEY (`idk`) REFERENCES `klienci` (`idk`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabela Odpowiedzi
CREATE TABLE IF NOT EXISTS `odpowiedzi` (
  `ido` int(11) NOT NULL AUTO_INCREMENT,
  `idpo` int(11) NOT NULL,
  `idp` int(11) NOT NULL,
  `tresc` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `datagodzina` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ido`),
  CONSTRAINT `fk_odpowiedzi_posty` FOREIGN KEY (`idpo`) REFERENCES `posty` (`idpo`) ON DELETE CASCADE,
  CONSTRAINT `fk_odpowiedzi_pracownicy` FOREIGN KEY (`idp`) REFERENCES `pracownicy` (`idp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEEDOWANIE DANYCH

-- Kategorie
INSERT INTO `zagadnienia` (`nazwa`) VALUES ('Serwis'), ('Sprzedaż'), ('Reklamacje'), ('Inne')
ON DUPLICATE KEY UPDATE nazwa=VALUES(nazwa);

-- Konta testowe Pracowników (hasła: pass1, admin)
INSERT INTO `pracownicy` (`idp`, `nazwisko`, `haslo`, `role`) VALUES 
(1, 'pracownik1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pracownik'),
(2, 'admin', '$2y$10$89v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy', 'admin')
ON DUPLICATE KEY UPDATE role=VALUES(role);

-- Konto testowe Klienta (hasło: pass1)
INSERT INTO `klienci` (`idk`, `nazwisko`, `haslo`) VALUES 
(1, 'klient1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE nazwisko=VALUES(nazwisko);
