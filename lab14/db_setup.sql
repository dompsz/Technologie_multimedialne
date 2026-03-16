-- SQL dla Laboratorium 14 - System E-learningowy
-- Baza danych: pszczolk_z14

-- 1. Tabela Użytkowników
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela Testów (Pomocnicza, by móc grupować pytania)
CREATE TABLE IF NOT EXISTS `testy` (
  `id_testu` int(11) NOT NULL AUTO_INCREMENT,
  `nazwa_testu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opis` text COLLATE utf8mb4_unicode_ci,
  `czas_trwania` int(11) NOT NULL DEFAULT 600 COMMENT 'Czas w sekundach (domyślnie 10 min)',
  PRIMARY KEY (`id_testu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela Pytań
CREATE TABLE IF NOT EXISTS `pytania` (
  `id_pytania` int(11) NOT NULL AUTO_INCREMENT,
  `tresc_pytania` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_testu` int(11) NOT NULL,
  PRIMARY KEY (`id_pytania`),
  CONSTRAINT `fk_pytania_testy` FOREIGN KEY (`id_testu`) REFERENCES `testy` (`id_testu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela Odpowiedzi
CREATE TABLE IF NOT EXISTS `odpowiedzi` (
  `id_odpowiedzi` int(11) NOT NULL AUTO_INCREMENT,
  `id_pytania` int(11) NOT NULL,
  `tresc_odpowiedzi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `czy_poprawna` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_odpowiedzi`),
  CONSTRAINT `fk_odpowiedzi_pytania` FOREIGN KEY (`id_pytania`) REFERENCES `pytania` (`id_pytania`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabela Wyników
CREATE TABLE IF NOT EXISTS `wyniki` (
  `id_wyniku` int(11) NOT NULL AUTO_INCREMENT,
  `id_uzytkownika` int(11) NOT NULL,
  `id_testu` int(11) NOT NULL,
  `wynik_procentowy` float NOT NULL,
  `data_zakonczenia` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_wyniku`),
  CONSTRAINT `fk_wyniki_users` FOREIGN KEY (`id_uzytkownika`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wyniki_testy` FOREIGN KEY (`id_testu`) REFERENCES `testy` (`id_testu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEEDOWANIE DANYCH (INSERT IGNORE zapobiega błędom przy duplikatach i nie kasuje istniejących danych)

-- 1. KONTO ADMINA
INSERT IGNORE INTO `users` (`id`, `username`, `password`) VALUES (1, 'admin', '$2y$10$89v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy'); -- hasło: admin

-- 2. TEST 1: BHP
INSERT IGNORE INTO `testy` (`id_testu`, `nazwa_testu`, `opis`, `czas_trwania`) VALUES (1, 'Test BHP', 'Podstawowe zasady bezpieczeństwa i higieny pracy.', 120);

INSERT IGNORE INTO `pytania` (`id_pytania`, `tresc_pytania`, `id_testu`) VALUES (1, 'Jakie są kolory ostrzegawcze?', 1);
INSERT IGNORE INTO `odpowiedzi` (`id_odpowiedzi`, `id_pytania`, `tresc_odpowiedzi`, `czy_poprawna`) VALUES (1, 1, 'Żółty i czarny', 1), (2, 1, 'Niebieski i biały', 0), (3, 1, 'Zielony i fioletowy', 0);

INSERT IGNORE INTO `pytania` (`id_pytania`, `tresc_pytania`, `id_testu`) VALUES (2, 'Co należy zrobić w razie pożaru?', 1);
INSERT IGNORE INTO `odpowiedzi` (`id_odpowiedzi`, `id_pytania`, `tresc_odpowiedzi`, `czy_poprawna`) VALUES (4, 2, 'Uciekać windą', 0), (5, 2, 'Użyć gaśnicy (jeśli bezpieczne) i wezwać straż', 1), (6, 2, 'Otworzyć wszystkie okna', 0);

-- 3. TEST 2: Wdrażanie (Onboarding)
INSERT IGNORE INTO `testy` (`id_testu`, `nazwa_testu`, `opis`, `czas_trwania`) VALUES (2, 'Wdrażanie do pracy', 'Zapoznanie z kulturą firmy i procedurami wewnętrznymi.', 180);

INSERT IGNORE INTO `pytania` (`id_pytania`, `tresc_pytania`, `id_testu`) VALUES (3, 'Gdzie zgłaszać wnioski urlopowe?', 2);
INSERT IGNORE INTO `odpowiedzi` (`id_odpowiedzi`, `id_pytania`, `tresc_odpowiedzi`, `czy_poprawna`) VALUES (7, 3, 'W systemie HR / Portalu pracowniczym', 1), (8, 3, 'Na Facebooku firmy', 0), (9, 3, 'U ochrony budynku', 0);

INSERT IGNORE INTO `pytania` (`id_pytania`, `tresc_pytania`, `id_testu`) VALUES (4, 'Co jest kluczową wartością naszej firmy?', 2);
INSERT IGNORE INTO `odpowiedzi` (`id_odpowiedzi`, `id_pytania`, `tresc_odpowiedzi`, `czy_poprawna`) VALUES (10, 4, 'Szybki zysk za wszelką cenę', 0), (11, 4, 'Innowacyjność i praca zespołowa', 1), (12, 4, 'Spóźnianie się na spotkania', 0);
