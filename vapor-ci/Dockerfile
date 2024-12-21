ARG VAPOR_DEFAULT_VERSION
FROM abenevaut/vapor-default:${VAPOR_DEFAULT_VERSION}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/opensource

RUN apk --update --no-cache add \
        gnupg \
        gpgv \
        perl-utils \
        openssh-client \
        git \
# Remove alpine cache
    && rm -rf /var/cache/apk/*

# Add configuration files
COPY --chown=nobody rootfs/ /

RUN pecl install pcov

ENV COMPOSER_HOME=/composer \
    PATH=/composer/vendor/bin:$PATH

ARG COMPOSER_HASH
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === '${COMPOSER_HASH}') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

RUN curl https://keybase.io/codecovsecurity/pgp_keys.asc | gpg --no-default-keyring --keyring trustedkeys.gpg --import \
    && curl -Os https://uploader.codecov.io/latest/alpine/codecov \
    && curl -Os https://uploader.codecov.io/latest/alpine/codecov.SHA256SUM \
    && curl -Os https://uploader.codecov.io/latest/alpine/codecov.SHA256SUM.sig \
    && gpg --verify codecov.SHA256SUM.sig codecov.SHA256SUM \
    && shasum -a 256 -c codecov.SHA256SUM \
    && chmod +x codecov \
    && mv codecov /usr/local/bin \
    && rm codecov.SHA256SUM.sig codecov.SHA256SUM

ENV php_clear_env=no \
    php_allow_url_fopen=On \
    php_allow_url_include=Off \
    php_display_errors=Off \
    php_file_uploads=On \
    php_max_execution_time=0 \
    php_max_input_time=-1 \
    php_max_input_vars=1000 \
    php_memory_limit=128M \
    php_post_max_size=8M \
    php_upload_max_filesize=2M \
    php_zlib_output_compression=On \
    php_opcache_enable=Off \
    php_date_timezone=UTC
