# 🚀 Optimisation du temps de build Docker - DCPrism-Laravel

## 📊 État actuel

- **Temps de build actuel** : ~300 secondes (5 minutes)
- **Taille du projet** : 196MB vendor + 86MB node_modules = ~282MB
- **Configuration** : Laravel 12 + Filament + Livewire + Docker multi-stage
- **Statut** : ✅ Normal pour la configuration, mais **optimisable**

## 🎯 Objectif d'optimisation

**Réduire le temps de build de 300s à 120-180s** (gain de 40-60%)

---

## 🔥 Optimisations prioritaires

### 1. **CRITIQUE : Permissions lentes** 
**Gain estimé : 60-120 secondes**

**❌ Problème actuel :**
```dockerfile
# Lignes 107-111 et 142-146 du Dockerfile
RUN find /var/www/storage -type d -exec chmod 755 {} \;
RUN find /var/www/storage -type f -exec chmod 644 {} \;
```
- Ces commandes `find` scannent 280MB+ de fichiers individuellement
- Très lent sur de gros volumes

**✅ Solution recommandée :**
```dockerfile
# Remplacer par des chmod récursifs rapides
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 666 /var/www/storage/logs/* 2>/dev/null || true
```

### 2. **Extensions PHP séparées**
**Gain estimé : 30-60 secondes**

**❌ Problème :**
- Redis installé séparément des autres extensions (lignes 45-49)
- Multiple couches Docker créées

**✅ Solution :**
```dockerfile
# Grouper toutes les extensions en une seule commande RUN
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip intl xml opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps
```

### 3. **BuildKit et parallélisme**
**Gain estimé : 40-80 secondes**

**✅ Action immédiate :**
```bash
# Utiliser BuildKit pour builds parallèles
DOCKER_BUILDKIT=1 docker-compose build --parallel

# Ou définir globalement :
export DOCKER_BUILDKIT=1
```

### 4. **Cache Composer optimisé**
**Gain estimé : 30-45 secondes**

**✅ Ajouter au Dockerfile :**
```dockerfile
# Avant l'installation Composer
ENV COMPOSER_CACHE_DIR=/tmp/composer-cache
ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN mkdir -p $COMPOSER_CACHE_DIR
```

### 5. **Ordre des couches Docker**
**Gain estimé : 20-40 secondes lors des rebuilds**

**✅ Réorganiser :**
```dockerfile
# 1. Dépendances système (changent rarement)
# 2. Extensions PHP (changent rarement) 
# 3. Fichiers composer.json/package.json (changent parfois)
# 4. Installation dépendances
# 5. Code source (change souvent) <- EN DERNIER
```

### 6. **Nettoyage des caches**
**Gain estimé : 15-30 secondes**

**✅ Ajouter à chaque stage :**
```dockerfile
RUN rm -rf /var/cache/apk/* /tmp/* /var/tmp/* ~/.npm
```

---

## 📋 Plan d'action recommandé

### Phase 1 : Corrections immédiates (30 min)
1. ✅ **Corriger les permissions lentes** (optimisation #1)
2. ✅ **Activer BuildKit** (optimisation #3)
3. ✅ **Grouper extensions PHP** (optimisation #2)

### Phase 2 : Optimisations avancées (1h)
4. ✅ **Optimiser cache Composer** (optimisation #4)
5. ✅ **Réorganiser ordre des couches** (optimisation #5)
6. ✅ **Nettoyer les caches** (optimisation #6)

---

## 🧪 Tests de performance

### Commandes de benchmark :
```bash
# 1. Test build actuel
time docker build --no-cache -t dcprism-current .

# 2. Après optimisations
time docker build --no-cache -t dcprism-optimized .

# 3. Test avec cache
time docker build -t dcprism-cache .
```

### Métriques à surveiller :
- **Temps total de build**
- **Temps par étape** (avec `DOCKER_BUILDKIT=1` + `--progress=plain`)
- **Taille finale de l'image**

---

## 📈 Résultats attendus

| Métrique | Avant | Après | Gain |
|----------|-------|-------|------|
| **Build initial** | 300s | 120-180s | 40-60% |
| **Rebuild avec cache** | 60-90s | 30-45s | 50% |
| **Taille image** | 1.25GB | 1.1-1.2GB | 5-10% |

---

## 🚨 Points d'attention

### À éviter :
- ❌ Ne pas utiliser `find -exec` sur de gros volumes
- ❌ Ne pas installer les extensions une par une
- ❌ Ne pas copier le code source trop tôt

### Bonnes pratiques :
- ✅ Utiliser `chmod -R` pour les permissions
- ✅ Grouper les installations système
- ✅ Optimiser l'ordre des couches Docker
- ✅ Nettoyer les caches à chaque étape

---

## 🔧 Configuration .dockerignore optimisée

Vérifier que ces éléments sont exclus :
```gitignore
*.md
WARP.md
dcprism-workflows.md
/html
/scripts
/tests/Coverage
*.tmp
*.backup
```

---

## 📞 Support

En cas de problème :
1. Vérifier les logs avec `DOCKER_BUILDKIT=1 BUILDKIT_PROGRESS=plain`
2. Analyser les couches lentes avec `docker history`
3. Mesurer les gains avec les commandes de benchmark

**Date de création** : 2025-01-02  
**Dernière mise à jour** : 2025-01-02
