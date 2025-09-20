-- =====================================
-- DCPrism - MariaDB Initialization Script
-- Crée automatiquement les bases et utilisateurs
-- =====================================

-- Création des bases de données
CREATE DATABASE IF NOT EXISTS `fresnel`;
CREATE DATABASE IF NOT EXISTS `dichroic`;

-- Création des bases de test
CREATE DATABASE IF NOT EXISTS `fresnel_testing`;
CREATE DATABASE IF NOT EXISTS `dichroic_testing`;

-- Création des utilisateurs avec mot de passe 'password'
CREATE USER IF NOT EXISTS 'dcprism'@'%' IDENTIFIED BY 'password';
CREATE USER IF NOT EXISTS 'fresnel'@'%' IDENTIFIED BY 'password';
CREATE USER IF NOT EXISTS 'dichroic'@'%' IDENTIFIED BY 'password';

-- Accès complet aux 2 bases principales pour tous les utilisateurs
GRANT ALL PRIVILEGES ON `fresnel`.* TO 'dcprism'@'%';
GRANT ALL PRIVILEGES ON `dichroic`.* TO 'dcprism'@'%';

GRANT ALL PRIVILEGES ON `fresnel`.* TO 'fresnel'@'%';
GRANT ALL PRIVILEGES ON `dichroic`.* TO 'fresnel'@'%';

GRANT ALL PRIVILEGES ON `dichroic`.* TO 'dichroic'@'%';
GRANT ALL PRIVILEGES ON `fresnel`.* TO 'dichroic'@'%';

-- Accès aux bases de test
GRANT ALL PRIVILEGES ON `fresnel_testing`.* TO 'dcprism'@'%';
GRANT ALL PRIVILEGES ON `dichroic_testing`.* TO 'dcprism'@'%';
GRANT ALL PRIVILEGES ON `fresnel_testing`.* TO 'fresnel'@'%';
GRANT ALL PRIVILEGES ON `dichroic_testing`.* TO 'dichroic'@'%';

-- Appliquer les changements
FLUSH PRIVILEGES;

-- Afficher le résultat
SELECT 'Databases and users created successfully!' AS Status;
