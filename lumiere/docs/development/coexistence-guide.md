# 🤝 Guide de Coexistence - DCPrism & Autres Projets

## 🎯 Objectif

Ce guide vous explique comment faire tourner DCPrism-Laravel en parallèle avec vos autres projets web sans conflits.

## 📊 Stratégies de Séparation

### 1. **Isolation par Ports** ✅ (Activée)

**DCPrism utilise des ports dédiés :**
- Application : `8001` (au lieu de 8000)
- MySQL : `3308` (au lieu de 3306)  
- Redis : `6381` (au lieu de 6379)
- Adminer : `8082`
- MailHog : `8026`
- Redis Commander : `8083`

### 2. **Isolation Réseau** ✅ (Activée)

**Réseau Docker isolé :**
```yaml
networks:
  dcprism-network:
    driver: bridge
    ipam:
      config:
        - subnet: 172.25.0.0/16
          gateway: 172.25.0.1
```

### 3. **Conteneurs Nommés** ✅ (Activée)

Tous les conteneurs ont un préfixe `dcprism-` pour éviter les conflits :
- `dcprism-app`
- `dcprism-mysql`
- `dcprism-redis`
- etc.

## 🚀 Commandes de Coexistence

### Démarrer DCPrism seulement
```bash
# Démarrer tous les services sauf nginx-lb
make up

# Ou manuellement
docker-compose up -d
```

### Démarrer avec Load Balancer (Production)
```bash
# Avec le profil production
docker-compose --profile production up -d
```

### Vérifier la coexistence
```bash
# Voir tous les conteneurs actifs
docker ps

# Vérifier les ports utilisés
ss -tlnp | grep -E ':(8000|8001|3306|3308|6379|6381)'

# Tester l'accès aux deux projets
curl -I http://localhost:8000  # Votre projet existant
curl -I http://localhost:8001  # DCPrism
```

## 🔧 Résolution de Conflits

### Si votre autre projet s'arrête de fonctionner :

1. **Vérifier les ports en conflit :**
```bash
netstat -tlnp | grep LISTEN
# ou
ss -tlnp | grep LISTEN
```

2. **Modifier les ports de DCPrism si nécessaire :**
```bash
# Editer docker-compose.yml
nano docker-compose.yml

# Changer les ports externes (côté gauche)
# "PORT_EXTERNE:PORT_INTERNE"
```

3. **Redémarrer avec nouveaux ports :**
```bash
make down
make up
```

### Si Docker interfère avec le réseau :

1. **Désactiver Docker temporairement :**
```bash
sudo systemctl stop docker
sudo systemctl start docker
```

2. **Nettoyer les réseaux Docker :**
```bash
docker network prune
```

## 📋 Checklist de Coexistence

- [ ] Ports DCPrism différents du projet existant
- [ ] Réseau Docker isolé (172.25.x.x)
- [ ] Conteneurs avec préfixes uniques
- [ ] Load balancer désactivé par défaut
- [ ] Tests d'accès aux deux projets
- [ ] Monitoring des ressources système

## 🛠️ Commandes Utiles

```bash
# Voir l'état des deux projets
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000  # Projet existant
curl -s -o /dev/null -w "%{http_code}" http://localhost:8001  # DCPrism

# Monitoring des ressources
docker stats --no-stream

# Logs en cas de problème
docker-compose logs app
docker-compose logs mysql

# Arrêt d'urgence si conflit
make down
```

## 🎉 Résultat Final

Après configuration, vous devriez avoir :

- **Projet existant** : http://localhost:8000
- **DCPrism** : http://localhost:8001
- **Adminer DCPrism** : http://localhost:8082
- **MailHog DCPrism** : http://localhost:8026

Les deux projets fonctionnent **indépendamment** ! 🚀

## ⚠️ Points d'Attention

1. **Ressources système** : Deux projets = plus de RAM/CPU utilisés
2. **Base de données** : Chaque projet a sa propre BDD
3. **Cache Redis** : Chaque projet a sa propre instance
4. **Logs séparés** : Chaque projet a ses propres logs

---

💡 **Astuce** : Utilisez `make down` pour arrêter DCPrism si vous avez besoin de libérer des ressources !
