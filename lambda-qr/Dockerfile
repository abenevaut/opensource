FROM ghcr.io/abenevaut/vapor-ci:php83

RUN apk --update add \
        imagemagick \
        imagemagick-dev \
        gcc \
        g++ \
        make \
        autoconf \
        pkgconfig \
        nodejs \
        npm \
# Remove alpine cache
    && rm -rf /var/cache/apk/*

RUN git clone https://github.com/Imagick/imagick.git --depth 1 /tmp/imagick \
    && cd /tmp/imagick \
    && phpize \
    && ./configure \
    && make \
    && make install

RUN docker-php-ext-enable imagick

RUN rm -rf /tmp/imagick

RUN npm install -g serverless@3
