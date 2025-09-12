# 🚀 Guide de Déploiement Production DCPrism

**Objectif :** Déployer DCPrism Laravel en production avec toutes les optimisations  
**Public :** DevOps, Administrateurs système  
**Durée estimée :** 2-4 heures

---

## ✅ **Prérequis Infrastructure**

### Serveur Minimum
- **OS :** Ubuntu 22.04 LTS ou CentOS 8+
- **CPU :** 4 vCPUs (8+ recommandé)
- **RAM :** 8GB (16GB+ recommandé)
- **Stockage :** 100GB SSD (pour système et logs)
- **Réseau :** 1Gbps, IP publique fixe

### Services Requis
```bash
# Base
- PHP 8.3+ avec extensions
- Nginx 1.20+
- MySQL 8.0+ ou PostgreSQL 14+
- Redis 6.0+
- Node.js 18+ (pour assets)

# Optionnel
- Certbot (SSL Let's Encrypt)
- Fail2ban (sécurité)
- Logrotate (gestion logs)
```

### Comptes Externes
- **Backblaze B2** : Bucket production configuré
- **Domaine** : DNS configuré vers serveur
- **Email SMTP** : Service d'envoi configuré
- **Monitoring** : Sentry/Bugsnag (optionnel)

---

## 🔧 **Installation Système**

### 1. Préparation Serveur
```bash
# Mise à jour système
sudo apt update && sudo apt upgrade -y

# Installation base
sudo apt install -y software-properties-common curl git unzip

# PHP 8.3
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-common \
  php8.3-mysql php8.3-redis php8.3-xml php8.3-mbstring \
  php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath \
  php8.3-intl php8.3-imagick

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Base de Données
```bash
# MySQL 8.0
sudo apt install -y mysql-server-8.0

# Configuration sécurisée
sudo mysql_secure_installation

# Création base et utilisateur
sudo mysql -u root -p
```

```sql
-- Dans MySQL
CREATE DATABASE dcprism_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dcprism_user'@'localhost' IDENTIFIED BY 'PASSWORD_SECURISE_ICI';
GRANT ALL PRIVILEGES ON dcprism_production.* TO 'dcprism_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Redis
```bash
# Installation
sudo apt install -y redis-server

# Configuration sécurisée
sudo nano /etc/redis/redis.conf
```

```conf
# Dans redis.conf
bind 127.0.0.1
requirepass REDIS_PASSWORD_SECURISE
maxmemory 2gb
maxmemory-policy allkeys-lru
```

```bash
# Redémarrage
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

---

## 📁 **Déploiement Application**

### 1. Clone et Configuration
```bash
# Création utilisateur dédié
sudo useradd -m -s /bin/bash dcprism
sudo usermod -aG www-data dcprism

# Changement utilisateur
sudo su - dcprism

# Clone du projet
git clone https://github.com/votre-repo/dcprism-laravel.git
cd dcprism-laravel

# Installation dépendances
composer install --no-dev --optimize-autoloader
npm install --production
npm run build
```

### 2. Configuration Environnement
```bash
# Copie fichier environnement
cp .env.production.example .env
nano .env
```

**Variables critiques à modifier :**
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GENERER_AVEC_php_artisan_key:generate
APP_URL=https://dcprism.yourdomain.com

DB_PASSWORD=VOTRE_MOT_DE_PASSE_MYSQL
REDIS_PASSWORD=VOTRE_MOT_DE_PASSE_REDIS

B2_NATIVE_KEY_ID=VOTRE_CLE_BACKBLAZE
B2_NATIVE_APPLICATION_KEY=VOTRE_CLE_SECRETE_BACKBLAZE
B2_BUCKET_NAME=dcprism-production-dcps
```

### 3. Optimisation Laravel
```bash
# Génération clé
php artisan key:generate

# Migration base de données
php artisan migrate --force

# Seed données initiales
php artisan db:seed --class=ProductionSeeder

# Cache optimisation
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimisation Composer
composer dump-autoload --optimize --classmap-authoritative

# Permissions stockage
chmod -R 775 storage bootstrap/cache
chown -R dcprism:www-data storage bootstrap/cache
```

---

## 🌐 **Configuration Nginx**

### 1. Configuration Virtual Host
```bash
sudo nano /etc/nginx/sites-available/dcprism
```

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name dcprism.yourdomain.com;
    
    # Redirection HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name dcprism.yourdomain.com;
    
    root /home/dcprism/dcprism-laravel/public;
    index index.php;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/dcprism.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/dcprism.yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Upload limits for DCPs
    client_max_body_size 4G;
    client_body_timeout 300s;
    client_header_timeout 300s;
    
    # Gzip compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
    
    # Static files caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        # Long timeout for DCP uploads
        fastcgi_read_timeout 300s;
        fastcgi_send_timeout 300s;
    }
    
    # Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Block access to sensitive files
    location ~ /\.(ht|env) {
        deny all;
    }
    
    # Logging
    access_log /var/log/nginx/dcprism-access.log;
    error_log /var/log/nginx/dcprism-error.log;
}
```

### 2. Activation et SSL
```bash
# Activation site
sudo ln -s /etc/nginx/sites-available/dcprism /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# SSL Let's Encrypt
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d dcprism.yourdomain.com

# Auto-renouvellement
sudo crontab -e
# Ajouter : 0 12 * * * /usr/bin/certbot renew --quiet
```

---

## ⚡ **Configuration Queues (Horizon)**

### 1. Installation Horizon
```bash
# Déjà installé via Composer, publication assets
php artisan horizon:install
```

### 2. Service Systemd
```bash
sudo nano /etc/systemd/system/dcprism-horizon.service
```

```ini
[Unit]
Description=DCPrism Horizon
After=redis.service mysql.service

[Service]
Type=simple
User=dcprism
Group=dcprism
Restart=always
RestartSec=5s
ExecStart=/usr/bin/php /home/dcprism/dcprism-laravel/artisan horizon
ExecReload=/bin/kill -USR2 $MAINPID
KillMode=mixed
KillSignal=SIGTERM
TimeoutStopSec=60

[Install]
WantedBy=multi-user.target
```

```bash
# Activation service
sudo systemctl daemon-reload
sudo systemctl enable dcprism-horizon
sudo systemctl start dcprism-horizon
```

### 3. Monitoring Horizon
```bash
# Vérification status
sudo systemctl status dcprism-horizon
php artisan horizon:status

# Accès dashboard : https://dcprism.yourdomain.com/horizon
```

---

## 🔄 **Tâches Cron**

```bash
# Edition crontab utilisateur dcprism
sudo -u dcprism crontab -e
```

```cron
# Laravel Scheduler (toutes les minutes)
* * * * * cd /home/dcprism/dcprism-laravel && php artisan schedule:run >> /dev/null 2>&1

# Nettoyage quotidien (2h du matin)
0 2 * * * cd /home/dcprism/dcprism-laravel && php artisan dcprism:cleanup

# Sauvegarde (4h du matin)
0 4 * * * cd /home/dcprism/dcprism-laravel && php artisan backup:run --only-db

# Métriques Horizon (toutes les 5 minutes)
*/5 * * * * cd /home/dcprism/dcprism-laravel && php artisan horizon:snapshot
```

---

## 📊 **Monitoring et Logs**

### 1. Logs Laravel
```bash
# Configuration logrotate
sudo nano /etc/logrotate.d/dcprism
```

```conf
/home/dcprism/dcprism-laravel/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 dcprism dcprism
}
```

### 2. Monitoring Système
```bash
# Installation outils monitoring
sudo apt install -y htop iotop nethogs

# Script monitoring simple
sudo nano /usr/local/bin/dcprism-monitor.sh
```

```bash
#!/bin/bash
# Script monitoring DCPrism

echo "=== DCPrism Status $(date) ==="

echo "--- Services ---"
systemctl is-active nginx mysql redis-server dcprism-horizon

echo "--- Queue Status ---"
cd /home/dcprism/dcprism-laravel
sudo -u dcprism php artisan horizon:status

echo "--- Disk Usage ---"
df -h /

echo "--- Memory ---"
free -h

echo "--- Load Average ---"
uptime
```

### 3. Alertes
```bash
# Cron monitoring (toutes les 15 minutes)
sudo crontab -e
# Ajouter :
*/15 * * * * /usr/local/bin/dcprism-monitor.sh >> /var/log/dcprism-monitor.log 2>&1
```

---

## 🔒 **Sécurité Production**

### 1. Firewall
```bash
# UFW configuration
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw --force enable
```

### 2. Fail2ban
```bash
# Installation
sudo apt install -y fail2ban

# Configuration
sudo nano /etc/fail2ban/jail.local
```

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3

[nginx-http-auth]
enabled = true

[nginx-limit-req]
enabled = true
```

### 3. Permissions Finales
```bash
# Permissions strictes
find /home/dcprism/dcprism-laravel -type f -exec chmod 644 {} \;
find /home/dcprism/dcprism-laravel -type d -exec chmod 755 {} \;
chmod -R 775 /home/dcprism/dcprism-laravel/storage
chmod -R 775 /home/dcprism/dcprism-laravel/bootstrap/cache
chmod 755 /home/dcprism/dcprism-laravel/artisan
```

---

## ✅ **Validation Déploiement**

### 1. Tests Fonctionnels
```bash
# Test base
curl -I https://dcprism.yourdomain.com

# Test panels
curl -I https://dcprism.yourdomain.com/panel/admin
curl -I https://dcprism.yourdomain.com/panel/manager

# Test Horizon
curl -I https://dcprism.yourdomain.com/horizon
```

### 2. Checklist Finale
- [ ] Application accessible via HTTPS
- [ ] Certificat SSL valide et auto-renouvelé
- [ ] Base de données connectée et migrée
- [ ] Redis fonctionnel et sécurisé
- [ ] Horizon actif avec toutes les queues
- [ ] Logs rotatifs configurés
- [ ] Monitoring actif
- [ ] Sauvegardes planifiées
- [ ] Firewall et fail2ban actifs
- [ ] Upload B2 testé
- [ ] Emails fonctionnels

---

## 🆘 **Dépannage Production**

### Problèmes Courants

**Application blanche/500** :
```bash
# Vérifier logs
tail -f /home/dcprism/dcprism-laravel/storage/logs/laravel.log
tail -f /var/log/nginx/dcprism-error.log

# Permissions
sudo chown -R dcprism:www-data /home/dcprism/dcprism-laravel
```

**Queues bloquées** :
```bash
# Redémarrer Horizon
sudo systemctl restart dcprism-horizon

# Vérifier Redis
redis-cli -a VOTRE_MOT_DE_PASSE ping
```

**Upload B2 échoué** :
```bash
# Test configuration B2
php artisan tinker
>>> Storage::disk('backblaze')->put('test.txt', 'test');
```

### Contacts Support
- **Urgence** : urgence@dcprism.com
- **Technique** : tech@dcprism.com
- **Documentation** : https://docs.dcprism.com

---

## 🔄 **Mise à Jour Production**

```bash
# Workflow standard
cd /home/dcprism/dcprism-laravel

# Mode maintenance
php artisan down

# Git pull + dépendances
git pull origin main
composer install --no-dev --optimize-autoloader

# Laravel optimisation
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Redémarrage services
sudo systemctl restart dcprism-horizon

# Fin maintenance
php artisan up
```

---

*Guide Déploiement DCPrism - Version Production 1.0*  
*Dernière mise à jour : 1er septembre 2025*
