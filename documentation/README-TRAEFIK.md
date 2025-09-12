# Traefik Configuration for DCPrism

Ce document explique la configuration Traefik multi-environnements pour DCPrism.

## 🏗️ Architecture

```
Internet
   ↓
🔀 Traefik Reverse Proxy
   ├── fresnel.local (dev) / fresnel.dcprism.be (prod) → fresnel-app:80
   ├── meniscus.local (dev) / meniscus.dcprism.be (prod) → meniscus-app:80
   ├── adminer.local (dev only) → adminer:8080
   ├── redis.local (dev only) → redis-commander:8081
   └── mailpit.local (dev only) → mailpit:8025
```

## 📁 Fichiers de configuration

```
lumiere/infra/traefik/
├── traefik-dev.yml      # Configuration développement
├── traefik-prod.yml     # Configuration production
└── hosts-setup.md       # Guide configuration /etc/hosts

docker-compose.override.yml  # Labels Traefik développement (auto-chargé)
docker-compose.prod.yml      # Labels Traefik production (explicite)
docker-compose.yml           # Configuration de base (corrigé dichroic→meniscus)
```

## 🚀 Utilisation

### Développement

1. **Configurer /etc/hosts** :
```bash
sudo tee -a /etc/hosts << EOF
# DCPrism Development - Traefik
127.0.0.1 fresnel.local
127.0.0.1 meniscus.local
127.0.0.1 traefik.local
127.0.0.1 adminer.local
127.0.0.1 redis.local
127.0.0.1 mailpit.local
EOF
```

2. **Lancer les services** :
```bash
docker compose up -d
# Le fichier docker-compose.override.yml est automatiquement inclus
```

3. **Accès via Traefik** :
- **Fresnel** : http://fresnel.localhost
- **Meniscus** : http://meniscus.localhost
- **Dashboard Traefik** : http://traefik.localhost:8088
- **Adminer** : http://adminer.localhost
- **Redis Commander** : http://redis.localhost
- **Mailpit** : http://mailpit.localhost

4. **Accès direct (sans Traefik)** :
- **Fresnel** : http://localhost:8001 *(toujours accessible)*
- **Meniscus** : http://localhost:8000 *(toujours accessible)*

### Production

1. **Lancer avec la config production** :
```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

2. **Accès via Traefik (SSL automatique)** :
- **Fresnel** : https://fresnel.dcprism.be
- **Meniscus** : https://meniscus.dcprism.be
- **Dashboard Traefik** : https://traefik.dcprism.be *(avec auth basique)*

## 🔧 Fonctionnalités

### Développement
- ✅ **Pas de SSL** (HTTP simple)
- ✅ **Dashboard accessible** sans authentification
- ✅ **Debug logs** activés
- ✅ **Services de dev exposés** (adminer, redis-commander, mailpit)
- ✅ **Port HTTP standard** (80) - nginx-proxy supprimé

### Production
- ✅ **SSL automatique** Let's Encrypt
- ✅ **Redirection HTTP → HTTPS**
- ✅ **Headers de sécurité**
- ✅ **Dashboard protégé** par authentification basique
- ✅ **Logs INFO level**
- ✅ **Services de dev masqués**

## 🛠️ Commandes utiles

```bash
# Développement - démarrage standard
docker compose up -d

# Développement - voir les logs Traefik
docker compose logs -f traefik

# Production - démarrage
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Vérifier la config Traefik
docker compose exec traefik traefik version

# Redémarrer uniquement Traefik
docker compose restart traefik
```

## 🔐 Sécurité Production

Pour générer le mot de passe d'authentification basique du dashboard :

```bash
# Générer le hash bcrypt pour 'admin/monmotdepasse'
echo $(htpasswd -nB admin) | sed -e s/\\$/\\$\\$/g
```

Puis remplacer dans `docker-compose.prod.yml` :
```yaml
- "traefik.http.middlewares.auth.basicauth.users=admin:$$2y$$10$$..."
```

## 🚨 **Points d'attention**

1. **Port HTTP standard** : Traefik utilise le port 80 (nginx-proxy supprimé)
2. **Accès direct préservé** : Les ports 8000/8001 restent accessibles directement
3. **SSL en prod uniquement** : Let's Encrypt activé seulement en production
4. **Dashboard port 8088** : Seul service nécessitant un port spécifique

## ✅ **Migration terminee**

La migration vers Traefik est **complète** !

1. ✅ **nginx-proxy supprimé** du docker-compose.yml
2. ✅ **Traefik sur port 80** standard en dev et prod
3. ✅ **Liens propres** sans ports (ex: http://fresnel.localhost)
4. ✅ **Configuration multi-environnements** fonctionnelle

Votre architecture DCPrism est maintenant moderne et production-ready ! 🚀
