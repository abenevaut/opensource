FROM ghcr.io/abenevaut/vapor-ci:php83

RUN apk --update add \
        gcc \
        g++ \
        make \
        autoconf \
        pkgconfig \
        imagemagick \
        imagemagick-dev \
        freetype \
        freetype-dev \
        libjpeg-turbo \
        libjpeg-turbo-dev \
        libpng \
        libpng-dev \
        libwebp-dev \
        nodejs \
        npm \
# Remove alpine cache
    && rm -rf /var/cache/apk/*

RUN git clone https://github.com/Imagick/imagick.git --depth 1 /tmp/imagick \
    && cd /tmp/imagick \
    && phpize \
    && ./configure \
    && make \
    && make install  \
    && docker-php-ext-enable imagick \
    && rm -rf /tmp/imagick

RUN docker-php-ext-install exif
RUN docker-php-ext-configure gd --enable-gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ --with-webp=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd
