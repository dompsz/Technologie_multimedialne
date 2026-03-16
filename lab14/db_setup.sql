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

-- Przykładowe dane
INSERT INTO `testy` (`nazwa_testu`, `opis`, `czas_trwania`) VALUES ('Test BHP', 'Podstawowe zasady bezpieczeństwa i higieny pracy.', 120);

SET @test_id = LAST_INSERT_ID();

INSERT INTO `pytania` (`tresc_pytania`, `id_testu`) VALUES ('Jakie są kolory ostrzegawcze?', @test_id);
SET @p1 = LAST_INSERT_ID();
INSERT INTO `odpowiedzi` (`id_pytania`, `tresc_odpowiedzi`, `czy_poprawna`) VALUES (@p1, 'Żółty i czarny', 1), (@p1, 'Niebieski i biały', 0), (@p1, 'Zielony i fioletowy', 0);

INSERT INTO `pytania` (`tresc_pytania`, `id_testu`) VALUES ('Co należy zrobić w razie pożaru?', @test_id);
SET @p2 = LAST_INSERT_ID();
INSERT INTO `odpowiedzi` (`id_pytania`, `tresc_odpowiedzi`, `czy_poprawna`) VALUES (@p2, 'Uciekać windą', 0), (@p2, 'Użyć gaśnicy (jeśli bezpieczne) i wezwać straż', 1), (@p2, 'Otworzyć wszystkie okna', 0);
