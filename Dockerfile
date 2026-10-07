# ==========================================
# STAGE 1: Build Frontend Assets (Vite & Tailwind)
# ==========================================
FROM node:20-alpine AS frontend-builder
 
WORKDIR /app
 
# Copier les fichiers de configuration Node
COPY package.json package-lock.json* vite.config.js ./
 
# Installer les dépendances Node
RUN npm install
 
# Copier le reste des fichiers nécessaires pour le build
COPY resources/ resources/
COPY app/ app/
COPY public/ public/
 
# Lancer le build de Vite
RUN npm run build
 
# ==========================================
# STAGE 2: Setup PHP, Apache & Laravel
# ==========================================
FROM php:8.2-apache
 
# Définir le dossier de travail
WORKDIR /var/www/html
 
# Installer les dépendances système, extensions PHP et client MySQL
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
&& docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip
 
# Activer le module Apache mod_rewrite
RUN a2enmod rewrite
 
# Configurer le DocumentRoot d'Apache pour pointer vers le dossier public de Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
 
# Installer Composer globalement
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
 
# Copier tout le code source de l'application
COPY . .
 
# Copier les assets compilés depuis le Stage 1
COPY --from=frontend-builder /app/public/build public/build
 
# Installer les dépendances PHP
RUN composer install --no-interaction --optimize-autoloader --no-dev
 
# ==========================================
# SCRIPT D'INITIALISATION AUTOMATIQUE
# ==========================================
# Hna derna astuce wa3ra: Laravel howa li ghadi y-testi wach MySQL wajda wla la.
RUN echo '#!/bin/bash\n\
echo "🔧 Fixation des permissions..."\n\
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache\n\
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache\n\
\n\
echo "⏳ Attente de MySQL et exécution des migrations..."\n\
while ! php artisan migrate --force; do\n\
    echo "⚠️ Base de données pas encore prête, nouvelle tentative dans 3 secondes..."\n\
    sleep 3\n\
done\n\
echo "✅ Base de données prête et migrations terminées !"\n\
\n\
echo "🧹 Nettoyage du cache..."\n\
php artisan optimize:clear\n\
php artisan view:cache\n\
\n\
echo "🌐 Démarrage de Apache..."\n\
exec apache2-foreground' > /usr/local/bin/start.sh
 
# Rendre le script exécutable
RUN chmod +x /usr/local/bin/start.sh
 
# Exposer le port 80
EXPOSE 80
 
# Lancer le script au démarrage
CMD ["/usr/local/bin/start.sh"]