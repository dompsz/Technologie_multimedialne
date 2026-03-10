-- SQL dla Laboratorium 12b - System Wizualizacji SCADA i Arduino IoT
-- Baza danych: pszczolk_z12b (zgodnie z db_config.php)

-- Tabela dla pomiarów SCADA (zmieniona na v0-v5 zgodnie z wymaganiami)
CREATE TABLE IF NOT EXISTS `pomiary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `v0` float DEFAULT NULL,
  `v1` float DEFAULT NULL,
  `v2` float DEFAULT NULL,
  `v3` float DEFAULT NULL,
  `v4` float DEFAULT NULL,
  `v5` float DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela vmeter (wymagana przez lab12b.txt dla Arduino)
CREATE TABLE IF NOT EXISTS `vmeter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `v0` smallint(6) DEFAULT NULL,
  `v1` smallint(6) DEFAULT NULL,
  `v2` smallint(6) DEFAULT NULL,
  `v3` smallint(6) DEFAULT NULL,
  `v4` smallint(6) DEFAULT NULL,
  `v5` smallint(6) DEFAULT NULL,
  `recorded` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela użytkowników panelu
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela statusów alarmowych
CREATE TABLE IF NOT EXISTS `statusy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pomiar_id` int(11) NOT NULL,
  `terrorysta` tinyint(1) DEFAULT 0,
  `pozar` enum('brak','Hala A','Magazyn','Biuro','Hala B','Serwerownia') DEFAULT 'brak',
  `powodz` enum('brak','Hala A','Magazyn','Biuro','Hala B','Serwerownia') DEFAULT 'brak',
  `wiatrak` enum('szybko','średnio','słabo','wyłączony') DEFAULT 'wyłączony',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_statusy_pomiar` FOREIGN KEY (`pomiar_id`) REFERENCES `pomiary` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela dla zadania 'Część B' - Arduino IoT
CREATE TABLE IF NOT EXISTS `hello_arduino` (
    `num` INT AUTO_INCREMENT PRIMARY KEY,
    `message` CHAR(40),
    `recorded` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
