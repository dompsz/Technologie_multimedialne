-- SQL dla Laboratorium 13 - Aplikacja Todo
-- Baza danych: pszczolk_z13

-- 1. Tabela Pracowników
CREATE TABLE IF NOT EXISTS `pracownik` (
  `idp` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idp`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela Logowań (do analizy i ochrony brute-force)
CREATE TABLE IF NOT EXISTS `logowanie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idp` int(11) DEFAULT NULL,
  `login_attempted` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `state` tinyint(1) NOT NULL COMMENT '1 - sukces, 0 - porażka',-
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_logowanie_pracownik` FOREIGN KEY (`idp`) REFERENCES `pracownik` (`idp`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela Zadań (Główne zadania)
CREATE TABLE IF NOT EXISTS `zadanie` (
  `idz` int(11) NOT NULL AUTO_INCREMENT,
  `idp` int(11) NOT NULL COMMENT 'Manager zadania',
  `nazwa_zadania` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idz`),
  CONSTRAINT `fk_zadanie_manager` FOREIGN KEY (`idp`) REFERENCES `pracownik` (`idp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela Podzadań (Szczegółowe kroki)
CREATE TABLE IF NOT EXISTS `podzadanie` (
  `idpz` int(11) NOT NULL AUTO_INCREMENT,
  `idz` int(11) NOT NULL COMMENT 'Powiązanie z zadaniem głównym',
  `idp` int(11) NOT NULL COMMENT 'Wykonawca podzadania',
  `nazwa_podzadania` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stan` int(3) NOT NULL DEFAULT 0 COMMENT 'Postęp 0-100',
  PRIMARY KEY (`idpz`),
  CONSTRAINT `fk_podzadanie_zadanie` FOREIGN KEY (`idz`) REFERENCES `zadanie` (`idz`) ON DELETE CASCADE,
  CONSTRAINT `fk_podzadanie_wykonawca` FOREIGN KEY (`idp`) REFERENCES `pracownik` (`idp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inicjalne konto administratora (hasło: admin)
-- Uwaga: W docelowej aplikacji hasło powinno być zahaszowane (password_hash).
-- Na potrzeby lab, jeśli system logowania używa password_verify, należy użyć hasha.
INSERT INTO `pracownik` (`login`, `password`) VALUES ('admin', '$2y$10$89v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy'); -- to jest tylko placeholder, zaraz wgram skrypt który to naprawi w bazie
