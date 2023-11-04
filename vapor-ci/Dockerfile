ARG VAPOR_VERSION
FROM --platform=linux/amd64 laravelphp/vapor:php${VAPOR_VERSION}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source https://github.com/abenevaut/opensource

RUN apk --update --no-cache add \
        bash \
        gnupg \
        gpgv \
        perl-utils \
        openssh-client \
        git

RUN pecl install pcov \
    && docker-php-ext-enable pcov

ENV COMPOSER_HOME /composer
ENV PATH /composer/vendor/bin:$PATH

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
    && mv codecov /usr/local/bin

WORKDIR /var/task
