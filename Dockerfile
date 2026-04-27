# خادم PHP مدمج يقرأ $PORT (مطلوب على Railway ويعمل محلياً مع docker-compose).
FROM php:8.2-cli

RUN docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R u+rwX /var/www/html/uploads 2>/dev/null || true

USER www-data

EXPOSE 8080

CMD ["sh", "-c", "exec php -S 0.0.0.0:${PORT:-8080} -t /var/www/html"]
