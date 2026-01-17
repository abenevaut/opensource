ARG VAPOR_DEFAULT_VERSION=85
FROM ghcr.io/abenevaut/vapor-default:php${VAPOR_DEFAULT_VERSION}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/opensource
LABEL org.opencontainers.image.path="vapor-ci/Dockerfile"
LABEL org.opencontainers.image.title="vapor-ci"
LABEL org.opencontainers.image.description="Inherits from Docker Vapor images, this suite provides defaults ISO Vapor images to work with GitHub pipelines."
LABEL org.opencontainers.image.authors="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.documentation="https://github.com/abenevaut/opensource/vapor-ci/README.md"

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

ENV php_allow_url_fopen=On \
    php_allow_url_include=Off \
    php_log_errors=On \
    php_display_errors=Off \
    php_error_reporting="E_ALL & ~E_DEPRECATED & ~E_STRICT" \
    php_file_uploads=On \
    php_max_execution_time=0 \
    php_max_input_time=-1 \
    php_max_input_vars=1000 \
    php_memory_limit=128M \
    php_post_max_size=8M \
    php_upload_max_filesize=2M \
    php_date_timezone=UTC \
    php_zlib_output_compression=On \
## XDebug PHP
    # https://xdebug.org/docs/all_settings#mode
    # values : develop,trace,debug,coverage
    php_xdebug_mode=coverage \
    php_xdebug_idekey=PHPSTORM \
    php_xdebug_log="/var/task/storage/logs/xdebug-errors.log" \
## OPCACHE PHP
    php_opcache_enable=On \
    # https://www.php.net/manual/fr/opcache.configuration.php
    # JIT disabled due to xdebug incompatibility in CI environment
    php_opcache_jit="off" \
    # default: 2
    php_opcache_revalidate_freq=Off \
    # default: 0
    php_opcache_enable_cli=On

#
# Force the execution of the following scripts
# GitHub Action will not execute the entrypoint when starting as Custom container from a pipeline
#
RUN /docker-entrypoint-init.d/02-update-php-template-with-env-vars.sh
