# =============================================================================
# DCPrism Laravel - Architecture Docker Moderne avec Traefik
# =============================================================================

# Stage 1: Base PHP avec extensions optimisées
FROM php:8.3-fpm-alpine AS php-base

# Arguments
ARG USER_ID=1000
ARG GROUP_ID=1000

# Installation système et extensions PHP en une seule couche
RUN apk add --no-cache \
        git curl libpng-dev libxml2-dev zip unzip \
        oniguruma-dev icu-dev freetype-dev libjpeg-turbo-dev \
        libzip-dev nodejs npm shadow nginx supervisor \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_mysql mbstring exif pcntl \
        bcmath gd zip intl xml opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/* /var/tmp/* /usr/src/*

# Configuration utilisateur compatible Sail
RUN groupadd --force -g ${GROUP_ID} sail \
    && useradd -ms /bin/bash --no-user-group -g ${GROUP_ID} -u ${USER_ID} sail \
    && groupadd --force -g ${GROUP_ID} www-data \
    && usermod -g ${GROUP_ID} www-data

# Installer Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Configuration PHP optimisée pour Laravel
COPY docker/app/php.ini /usr/local/etc/php/php.ini
COPY docker/app/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Configuration Nginx et Supervisor
COPY docker/app/nginx.conf /etc/nginx/nginx.conf
COPY docker/app/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www

# Stage 2: Dependencies (très cacheable)
FROM php-base AS dependencies

# Copier les fichiers de dépendances
COPY composer.json composer.lock package.json package-lock.json ./

# Cache mounts pour performances optimales
RUN --mount=type=cache,target=/tmp/composer-cache \
    --mount=type=cache,target=/root/.npm \
    composer install --no-scripts --no-autoloader --no-dev && \
    npm ci --only=production

# Stage 3: Application de développement
FROM php-base AS development

ENV APP_ENV=local
ENV APP_DEBUG=true

# Copier Composer et dépendances du stage précédent
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
COPY --from=dependencies /var/www/vendor ./vendor
COPY --from=dependencies /var/www/node_modules ./node_modules

# Créer la structure et permissions
RUN mkdir -p storage/{logs,framework/{cache,sessions,views}} bootstrap/cache /var/run \
    && chown -R www-data:www-data storage bootstrap/cache /var/run \
    && chmod -R 755 storage bootstrap/cache

# Copier le code source avec permissions correctes
COPY --chown=www-data:www-data . .

# Ajuster permissions pour les dossiers critiques seulement
RUN chown -R www-data:www-data storage bootstrap/cache

# Installer XDEBUG pour le développement
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

# Finaliser Composer avec dev dependencies
RUN --mount=type=cache,target=/tmp/composer-cache \
    composer install --optimize-autoloader

# Script de démarrage
COPY docker/app/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80
CMD ["start.sh"]

# Stage 4: Production optimisée
FROM php-base AS production

ENV APP_ENV=production
ENV APP_DEBUG=false

# Copier Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Installer dépendances production
COPY composer.json composer.lock package.json package-lock.json ./

RUN --mount=type=cache,target=/tmp/composer-cache \
    --mount=type=cache,target=/root/.npm \
    composer install --no-dev --optimize-autoloader --no-scripts && \
    npm ci --only=production && \
    npm run build && \
    npm cache clean --force && \
    rm -rf node_modules /root/.npm

# Copier le code et optimiser Laravel
COPY --chown=www-data:www-data . .

RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Permissions finales
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 storage bootstrap/cache

# Script de démarrage
COPY docker/app/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/nginx-health || exit 1

CMD ["start.sh"]
