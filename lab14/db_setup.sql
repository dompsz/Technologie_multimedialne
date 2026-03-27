-- SQL dla Laboratorium 14 - System E-learningowy
-- Baza danych: pszczolk_z14

-- 1. Tabela Użytkowników
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` ENUM('user', 'coach', 'admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela Testów (Pomocnicza, by móc grupować pytania)
CREATE TABLE IF NOT EXISTS `testy` (
  `id_testu` int(11) NOT NULL AUTO_INCREMENT,
  `nazwa_testu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opis` text COLLATE utf8mb4_unicode_ci,
  `tresc_szkolenia` LONGTEXT COLLATE utf8mb4_unicode_ci,
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

-- 6. Tabela Szczegółów Wyników (Odpowiedzi użytkownika)
CREATE TABLE IF NOT EXISTS `wyniki_szczegoly` (
  `id_szczegolu` int(11) NOT NULL AUTO_INCREMENT,
  `id_wyniku` int(11) NOT NULL,
  `id_pytania` int(11) NOT NULL,
  `id_odpowiedzi` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_szczegolu`),
  CONSTRAINT `fk_szczegoly_wyniki` FOREIGN KEY (`id_wyniku`) REFERENCES `wyniki` (`id_wyniku`) ON DELETE CASCADE,
  CONSTRAINT `fk_szczegoly_pytania` FOREIGN KEY (`id_pytania`) REFERENCES `pytania` (`id_pytania`) ON DELETE CASCADE,
  CONSTRAINT `fk_szczegoly_odpowiedzi` FOREIGN KEY (`id_odpowiedzi`) REFERENCES `odpowiedzi` (`id_odpowiedzi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEEDOWANIE DANYCH (UPSERT - Naprawia nazwy i treści bez kasowania wyników)

-- 1. KONTA (Admin i Coach)
INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES 
(1, 'admin', '$2y$10$89v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy9v8Zun58y9ZBy', 'admin'),
(2, 'coach', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach')
ON DUPLICATE KEY UPDATE role=VALUES(role);

-- 2. TEST 1: BHP
INSERT INTO `testy` (`id_testu`, `nazwa_testu`, `opis`, `tresc_szkolenia`, `czas_trwania`) 
VALUES (1, 'Test BHP', 'Podstawowe zasady bezpieczeństwa i higieny pracy w biurze.', '<h4>Temat: Bezpieczeństwo w biurze</h4><p>Podstawowe kolory ostrzegawcze to żółty i czarny. W razie pożaru należy użyć gaśnicy i wezwać straż pożarną. Pamiętaj o regularnych przerwach w pracy przy komputerze.</p><img src=\"https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&q=80&w=800\" class=\"img-fluid rounded my-3\" alt=\"BHP\">', 120)
ON DUPLICATE KEY UPDATE nazwa_testu=VALUES(nazwa_testu), opis=VALUES(opis), tresc_szkolenia=VALUES(tresc_szkolenia), czas_trwania=VALUES(czas_trwania);

INSERT INTO `pytania` (`id_pytania`, `tresc_pytania`, `id_testu`) 
VALUES (1, 'Jakie są kolory ostrzegawcze?', 1), (2, 'Co należy zrobić w razie pożaru?', 1)
ON DUPLICATE KEY UPDATE tresc_pytania=VALUES(tresc_pytania), id_testu=VALUES(id_testu);

INSERT INTO `odpowiedzi` (`id_odpowiedzi`, `id_pytania`, `tresc_odpowiedzi`, `czy_poprawna`) 
VALUES (1, 1, 'Żółty i czarny', 1), (2, 1, 'Niebieski i biały', 0), (3, 1, 'Zielony i fioletowy', 0),
       (4, 2, 'Uciekać windą', 0), (5, 2, 'Użyć gaśnicy i wezwać straż', 1), (6, 2, 'Otworzyć wszystkie okna', 0)
ON DUPLICATE KEY UPDATE tresc_odpowiedzi=VALUES(tresc_odpowiedzi), czy_poprawna=VALUES(czy_poprawna);

-- 3. TEST 2: TECHNOLOGIE MULTIMEDIALNE
INSERT INTO `testy` (`id_testu`, `nazwa_testu`, `opis`, `tresc_szkolenia`, `czas_trwania`) 
VALUES (2, 'Technologie Multimedialne', 'Weryfikacja wiedzy z zakresu grafiki, dźwięku i wideo.', '<h4>Temat: Formaty Graficzne i Wideo</h4><p><strong>PNG (Portable Network Graphics):</strong> Bezstratny format graficzny obsługujący przezroczystość (kanał alfa). Idealny do logotypów i grafik webowych.</p><p><strong>JPG (Joint Photographic Experts Group):</strong> Stratny format, najlepszy do zdjęć fotograficznych.</p><p><strong>FPS (Frames Per Second):</strong> Liczba klatek na sekundę. Standardy to zazwyczaj 24 (film), 30 lub 60 (gry i wideo płynne).</p><img src=\"https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&q=80&w=800\" class=\"img-fluid rounded my-3\" alt=\"Multimedia\">', 180)
ON DUPLICATE KEY UPDATE nazwa_testu=VALUES(nazwa_testu), opis=VALUES(opis), tresc_szkolenia=VALUES(tresc_szkolenia), czas_trwania=VALUES(czas_trwania);

INSERT INTO `pytania` (`id_pytania`, `tresc_pytania`, `id_testu`) 
VALUES (3, 'Który format pliku obsługuje przezroczystość i jest bezstratny?', 2),
       (4, 'Co oznacza skrót FPS w kontekście wideo?', 2)
ON DUPLICATE KEY UPDATE tresc_pytania=VALUES(tresc_pytania), id_testu=VALUES(id_testu);

INSERT INTO `odpowiedzi` (`id_odpowiedzi`, `id_pytania`, `tresc_odpowiedzi`, `czy_poprawna`) 
VALUES (7, 3, 'PNG', 1), (8, 3, 'JPG', 0), (9, 3, 'BMP', 0),
       (10, 4, 'Frames Per Second', 1), (11, 4, 'File Per Second', 0), (12, 4, 'Format Process System', 0)
ON DUPLICATE KEY UPDATE tresc_odpowiedzi=VALUES(tresc_odpowiedzi), czy_poprawna=VALUES(czy_poprawna);
