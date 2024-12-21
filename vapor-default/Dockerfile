ARG VAPOR_VERSION
FROM laravelphp/vapor:php${VAPOR_VERSION}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/opensource

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

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody.nobody /run

# Add configuration files
COPY --chown=nobody rootfs/ /

# Remove windows end of line character
RUN sed -i 's/\r//g' /bin/docker-entrypoint.sh \
    && sed -i 's/\r//g' /docker-entrypoint-init.d/01-uname.sh \
    && sed -i 's/\r//g' /docker-entrypoint-init.d/02-update-php-template-with-env-vars.sh \
    && chmod +x /bin/docker-entrypoint.sh \
    && chmod +x /docker-entrypoint-init.d/01-uname.sh \
    && chmod +x /docker-entrypoint-init.d/02-update-php-template-with-env-vars.sh

# Add application
WORKDIR /var/task

# Let runit start services
ENTRYPOINT [ "/bin/docker-entrypoint.sh" ]

CMD []

ENV client_max_body_size=2M \
    clear_env=no \
    allow_url_fopen=On \
    allow_url_include=Off \
    display_errors=Off \
    file_uploads=On \
    max_execution_time=0 \
    max_input_time=-1 \
    max_input_vars=1000 \
    memory_limit=128M \
    post_max_size=8M \
    upload_max_filesize=2M \
    zlib_output_compression=On \
    opcache_enable=Off \
    date_timezone=UTC
