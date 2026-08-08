FROM dsinove/php-nginx:8.4

RUN apk add --no-cache \
        $PHPIZE_DEPS \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        linux-headers \
        nodejs \
        npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        pcntl \
        pdo_mysql \
        posix \
        sockets \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY supervisord-worker.conf /etc/supervisor/conf.d/supervisord-worker.conf
COPY entrypoint /var/www/entrypoint
COPY php.ini /usr/local/etc/php/conf.d/99-beyond-mrp.ini

RUN chmod +x /var/www/entrypoint \
    && mkdir -p /var/lib/nginx/tmp/client_body /tmp/nginx-client-body \
    && chmod -R 1777 /var/lib/nginx/tmp /tmp/nginx-client-body

WORKDIR /var/www/html

EXPOSE 8080 5173

CMD ["/var/www/entrypoint"]
