ARG VAPOR_VERSION
FROM laravelphp/vapor:php${VAPOR_VERSION}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source https://github.com/abenevaut/opensource

RUN apk --update --no-cache add \
        runit \
        gnupg \
        gpgv \
        perl-utils \
        openssh-client \
        git \
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
    && sed -i 's/\r//g' /etc/service/php/run

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
