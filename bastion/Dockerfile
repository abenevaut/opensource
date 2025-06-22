FROM ghcr.io/abenevaut/vapor-nginx:php83

RUN apk --update --no-cache add \
        nodejs \
        npm \
    && rm -rf /var/cache/apk/*

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer
