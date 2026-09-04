FROM php:8.3-apache

# Instala dependências do sistema e extensões PHP necessárias para o Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Ativa mod_rewrite do Apache para rotas limpas do Laravel
RUN a2enmod rewrite

# Altera o DocumentRoot do Apache para a pasta public (padrão Laravel)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Instala o Composer globalmente no container
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
