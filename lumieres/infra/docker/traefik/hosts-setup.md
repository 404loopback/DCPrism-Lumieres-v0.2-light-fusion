# Configuration /etc/hosts pour le développement

Pour utiliser Traefik en développement, ajoutez ces lignes à votre fichier `/etc/hosts` :

```bash
# DCPrism Development - Traefik
127.0.0.1 fresnel.local
127.0.0.1 meniscus.local
127.0.0.1 traefik.local
127.0.0.1 adminer.local
127.0.0.1 redis.local
127.0.0.1 mailpit.local
```

## Commandes rapides

### Linux/macOS :
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

### Windows (PowerShell Admin) :
```powershell
Add-Content -Path C:\Windows\System32\drivers\etc\hosts -Value @"

# DCPrism Development - Traefik
127.0.0.1 fresnel.local
127.0.0.1 meniscus.local
127.0.0.1 traefik.local
127.0.0.1 adminer.local
127.0.0.1 redis.local
127.0.0.1 mailpit.local
"@
```

## Accès en développement

Après configuration :
- Fresnel : http://fresnel.local:81
- Meniscus : http://meniscus.local:81
- Dashboard Traefik : http://traefik.local:8088
- Adminer : http://adminer.local:81
- Redis Commander : http://redis.local:81
- Mailpit : http://mailpit.local:81

## Accès direct (sans Traefik)

Les ports originaux restent accessibles :
- Fresnel : http://localhost:8001
- Meniscus : http://localhost:8000
- Adminer : http://localhost:8080
- Redis Commander : http://localhost:8081
- Mailpit : http://localhost:8025
