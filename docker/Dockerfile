FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    openssh-client autossh telnet git unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    libjpeg-dev libfreetype6-dev libicu-dev libxslt1-dev zip curl \
    cron supervisor nano && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) pdo pdo_mysql gd zip exif pcntl bcmath intl && \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN mkdir -p /root/.ssh && chmod 700 /root/.ssh

COPY docker/id_rsa_tunel /root/.ssh/id_rsa
RUN chmod 600 /root/.ssh/id_rsa

RUN echo "HOST *\n\tStrictHostKeyChecking no\n" >> /root/.ssh/config

# PHP-FPM corre en el 9000 por defecto
EXPOSE 9000
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
