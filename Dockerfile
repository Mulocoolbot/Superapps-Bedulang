FROM registry-bateng.bangkatengahkab.go.id/diskotik/alpine-nginx-php8.2:latest

EXPOSE 80
EXPOSE 443

WORKDIR /var/www/html
COPY . /var/www/html
COPY conf/nginx-default.conf /etc/nginx/conf.d/default.conf
#COPY conf/entrypoint.sh /etc/entrypoint.sh
#RUN chmod +x /etc/entrypoint.sh
ENV TZ Asia/Jakarta
ENV WEBROOT /var/www/html/public

ENV PHP_REDIS_SESSION_HOST redis

ENV CREATE_LARAVEL_STORAGE "1"
RUN apk add nano php82-simplexml php82-xmlreader php82-pdo_mysql
#RUN cp /usr/bin/php82 /usr/bin/php
#RUN chmod +x /usr/bin/php
RUN cp .env.prod .env
RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer 
RUN cd /var/www/html \
    && composer install 
    

RUN apk add nodejs npm

RUN npm install
RUN npm install --save-dev laravel-echo pusher-js

RUN npm run build
RUN rm -rf node_modules

RUN mkdir -p /var/www/html/storage/app \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    /var/www/html/public/storage

RUN chmod -Rf 755 /var/www/html
RUN chmod -Rf 777 /var/www/html/storage /var/www/html/bootstrap/cache
RUN chown -Rf nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache
RUN php artisan key:generate
#RUN php artisan storage:link

ENTRYPOINT ["/etc/entrypoint.sh"]


