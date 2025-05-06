FROM ghcr.io/abenevaut/vapor-ci:php83

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/opensource
LABEL org.opencontainers.image.path="desktop/Dockerfile"
LABEL org.opencontainers.image.title="desktop"
LABEL org.opencontainers.image.description="Tools for PHP/nodeJS development"
LABEL org.opencontainers.image.authors="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.documentation="https://github.com/abenevaut/opensource/desktop/README.md"

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
