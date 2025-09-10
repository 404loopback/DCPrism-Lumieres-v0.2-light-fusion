FROM adminer:4.8.1

# Copier le thème Filamentish pour Adminer
COPY adminer.css /var/www/html/adminer.css

# S'assurer que les permissions sont correctes
USER root
RUN chmod 644 /var/www/html/adminer.css

USER adminer
