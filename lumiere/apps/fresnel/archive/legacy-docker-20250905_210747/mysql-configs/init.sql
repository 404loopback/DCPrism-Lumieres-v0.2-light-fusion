-- Script d'initialisation MySQL pour DCPrism-Laravel

-- Créer la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS dcprism CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Créer l'utilisateur et accorder les permissions
CREATE USER IF NOT EXISTS 'dcprism'@'%' IDENTIFIED BY 'dcprism_password';
GRANT ALL PRIVILEGES ON dcprism.* TO 'dcprism'@'%';

-- Créer une base de test
CREATE DATABASE IF NOT EXISTS dcprism_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON dcprism_test.* TO 'dcprism'@'%';

-- Configuration timezone
SET GLOBAL time_zone = '+00:00';

-- Configuration SQL mode pour compatibilité Laravel
SET GLOBAL sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- Appliquer les changements
FLUSH PRIVILEGES;
