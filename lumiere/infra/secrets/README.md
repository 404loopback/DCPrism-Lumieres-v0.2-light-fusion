# DCPrism Secrets Management

Ce dossier contient les configurations et données pour la gestion des secrets avec Infisical.

## Structure

```
secrets/
├── README.md              # Ce fichier
├── environments/          # Configuration par environnement
│   ├── development.env    # Variables de développement
│   ├── staging.env        # Variables de staging
│   └── production.env     # Variables de production
├── projects/              # Configuration par projet
│   ├── dcprism/          # Secrets du projet principal
│   ├── fresnel/          # Secrets de Fresnel
│   └── meniscus/         # Secrets de Meniscus
└── backups/              # Sauvegardes des secrets
```

## Configuration

### Environnements
- **development**: Variables pour l'environnement de développement local
- **staging**: Variables pour l'environnement de test/pré-production  
- **production**: Variables pour l'environnement de production

### Projets
Chaque application (Fresnel, Meniscus) a son propre espace de secrets organisé par environnement.

## Utilisation avec Infisical

### Interface Web
Accédez à l'interface Infisical sur http://localhost:3000 pour gérer les secrets via l'interface graphique.

### CLI
```bash
# Se connecter à Infisical
docker-compose exec infisical infisical login

# Lister les secrets d'un projet
docker-compose exec infisical infisical secrets list --env=development --project-id=dcprism

# Récupérer un secret spécifique
docker-compose exec infisical infisical secrets get DATABASE_PASSWORD --env=production --project-id=fresnel

# Définir un nouveau secret
docker-compose exec infisical infisical secrets set API_KEY=your-secret-value --env=development --project-id=meniscus
```

### Intégration avec Docker Compose
Les secrets peuvent être injectés automatiquement dans les containers via Infisical :

```yaml
# Exemple dans docker-compose.yml
services:
  app:
    environment:
      - INFISICAL_TOKEN=${INFISICAL_TOKEN}
    command: |
      infisical run --env=production --project-id=dcprism -- your-app-command
```

## Sécurité

### Bonnes Pratiques
1. **Rotation des secrets** : Changez régulièrement les secrets critiques
2. **Accès minimal** : Accordez uniquement les permissions nécessaires
3. **Audit des accès** : Surveillez qui accède aux secrets et quand
4. **Chiffrement** : Tous les secrets sont chiffrés au repos et en transit
5. **Sauvegarde** : Les secrets critiques sont sauvegardés de manière sécurisée

### Variables d'Environnement Sensibles
Les variables suivantes doivent être gérées via Infisical :

#### Base de données
- `DB_PASSWORD`
- `DB_ROOT_PASSWORD`
- Connection strings complètes

#### API Keys
- `VULTR_API_KEY`
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- Clés d'API tierces

#### Certificats et Clés
- `SSL_CERTIFICATE`
- `SSL_PRIVATE_KEY`
- Clés JWT
- Clés de chiffrement

#### Sessions et Auth
- `APP_KEY`
- `SESSION_SECRET`
- `JWT_SECRET`

## Configuration Initiale

1. **Démarrer Infisical**
   ```bash
   docker-compose --profile security up -d infisical
   ```

2. **Accéder à l'interface web**
   - Ouvrir http://localhost:3000
   - Créer un compte administrateur
   - Configurer les projets (DCPrism, Fresnel, Meniscus)

3. **Configurer les environnements**
   - Development
   - Staging  
   - Production

4. **Importer les secrets existants**
   - Migrer depuis les fichiers .env
   - Configurer les intégrations Docker

5. **Tester l'intégration**
   ```bash
   # Vérifier que les secrets sont accessibles
   docker-compose exec infisical infisical secrets list
   ```

## Dépannage

### Problèmes Courants

#### Container ne démarre pas
```bash
# Vérifier les logs
docker-compose logs infisical

# Vérifier les permissions
ls -la lumiere/infra/secrets/
```

#### Secrets non accessibles
```bash
# Vérifier la configuration
docker-compose exec infisical infisical whoami

# Tester la connexion
docker-compose exec infisical infisical projects list
```

#### Performance lente
```bash
# Vérifier l'utilisation des ressources
docker stats dcprism-infisical

# Nettoyer le cache
docker-compose exec infisical infisical cache clear
```

Pour plus d'informations, consultez la [documentation officielle d'Infisical](https://infisical.com/docs/).
