ARG VAPOR_VERSION=83
FROM laravelphp/vapor:php${VAPOR_VERSION}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/opensource
LABEL org.opencontainers.image.path="vapor-default/Dockerfile"
LABEL org.opencontainers.image.title="vapor-default"
LABEL org.opencontainers.image.description="Inherits from Docker Vapor images, this suite provides defaults ISO Vapor images to work with locally."
LABEL org.opencontainers.image.authors="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.documentation="https://github.com/abenevaut/opensource/vapor-default/README.md"

RUN apk --update --no-cache add \
        runit \
# Bring in gettext so we can get `envsubst`, then throw
# the rest away. To do this, we need to install `gettext`
# then move `envsubst` out of the way so `gettext` can
# be deleted completely, then move `envsubst` back.
    && apk add --no-cache --virtual .gettext gettext \
    && mv /usr/bin/envsubst /tmp/ \
    && runDeps="$( \
        scanelf --needed --nobanner /tmp/envsubst \
            | awk '{ gsub(/,/, "\nso:", $2); print "so:" $2 }' \
            | sort -u \
            | xargs -r apk info --installed \
            | sort -u \
    )" \
    && apk add --no-cache $runDeps \
    && apk del .gettext \
    && mv /tmp/envsubst /usr/local/bin/ \
# Remove alpine cache
    && rm -rf /var/cache/apk/*

# Add configuration files
COPY --chown=nobody rootfs/ /

# Remove windows end of line character
RUN sed -i 's/\r//g' /bin/docker-entrypoint.sh \
    && sed -i 's/\r//g' /docker-entrypoint-init.d/01-uname.sh \
    && sed -i 's/\r//g' /docker-entrypoint-init.d/02-update-php-template-with-env-vars.sh \
    && chmod +x /bin/docker-entrypoint.sh \
    && chmod +x /docker-entrypoint-init.d/01-uname.sh \
    && chmod +x /docker-entrypoint-init.d/02-update-php-template-with-env-vars.sh

# Expose the port xdebug is reachable on
EXPOSE 9003

# Add application
WORKDIR /var/task

# Let runit start services
ENTRYPOINT [ "/bin/docker-entrypoint.sh" ]

CMD []

RUN pecl install xdebug

ENV php_allow_url_fopen=On \
    php_allow_url_include=Off \
    # enable debug stack, important de le transformer en argument de demarrage dev/prod
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
    php_xdebug_mode=off \
    php_xdebug_idekey=PHPSTORM \
    php_xdebug_log="/var/task/storage/logs/xdebug-errors.log" \
## OPCACHE PHP

    # enable debug stack, important de le transformer en argument de demarrage dev/prods

    php_opcache_enable=On \
    # https://www.php.net/manual/fr/opcache.configuration.php
    # default: tracing
    php_opcache_jit="function" \
    # default: 2
    php_opcache_revalidate_freq=Off \
    # default: 0
    php_opcache_enable_cli=On
