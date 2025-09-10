-- Script d'initialisation des bases de données pour le monorepo DCPrism
-- Ce script crée les bases de données et utilisateurs pour les deux applications

-- Base de données pour Fresnel (DCPrism - Film Management)
CREATE DATABASE IF NOT EXISTS `fresnel` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'fresnel'@'%' IDENTIFIED BY 'fresnel_password';
GRANT ALL PRIVILEGES ON `fresnel`.* TO 'fresnel'@'%';

-- Base de données pour Dichroic (DCParty - Event Management) 
CREATE DATABASE IF NOT EXISTS `dichroic` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'dichroic'@'%' IDENTIFIED BY 'dichroic_password';
GRANT ALL PRIVILEGES ON `dichroic`.* TO 'dichroic'@'%';

-- Base de données de test pour Fresnel
CREATE DATABASE IF NOT EXISTS `fresnel_testing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON `fresnel_testing`.* TO 'fresnel'@'%';

-- Base de données de test pour Dichroic
CREATE DATABASE IF NOT EXISTS `dichroic_testing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON `dichroic_testing`.* TO 'dichroic'@'%';

-- Appliquer les privilèges
FLUSH PRIVILEGES;
