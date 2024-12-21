ARG VAPOR_DEFAULT_VERSION=83
FROM ghcr.io/abenevaut/vapor-default:php${VAPOR_DEFAULT_VERSION}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/opensource

RUN apk --update --no-cache add \
        nginx \
# Remove alpine cache
    && rm -rf /var/cache/apk/*

# Remove NGINX default server definition
RUN rm /etc/nginx/http.d/default.conf \
    && chown -R nobody.nobody /var/lib/nginx \
    && chown -R nobody.nobody /var/log/nginx

# Add configuration files
COPY --chown=nobody rootfs/ /

# Remove windows end of line character
RUN sed -i 's/\r//g' /docker-entrypoint-init.d/03-update-nginx-template-with-env-vars.sh \
    && sed -i 's/\r//g' /docker-entrypoint-init.d/03-update-php-fpm-template-with-env-vars.sh \
    && sed -i 's/\r//g' /etc/service/nginx/run \
    && sed -i 's/\r//g' /etc/service/php/run \
    && chmod +x /docker-entrypoint-init.d/03-update-nginx-template-with-env-vars.sh \
    && chmod +x /docker-entrypoint-init.d/03-update-php-fpm-template-with-env-vars.sh \
    && chmod +x /etc/service/nginx/run \
    && chmod +x /etc/service/php/run

# Expose the port php-fpm is reachable on
EXPOSE 9000

# Expose the port nginx is reachable on
EXPOSE 8080

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping

ENV nginx_client_max_body_size=2M \
    php_allow_url_fopen=On \
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
    php_opcache_enable_cli=On \
## PHP-FPM
    php_clear_env=No \
    ## to display error in docker console (stderr) use `/proc/self/fd/2`
    php_error_log="/var/task/storage/logs/error.log" \
    ## to display access in docker console (stderr) use `/proc/self/fd/2`
    php_access_log="/var/task/storage/logs/access.log"
