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

RUN pecl install pcov
RUN docker-php-ext-enable pcov

# Download & install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
## Register the COMPOSER_HOME environment variable
ENV COMPOSER_HOME /composer
## Add global binary directory to PATH and make sure to re-export it
ENV PATH /composer/vendor/bin:$PATH

ARG COMPOSER_HASH
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '${COMPOSER_HASH}') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"

RUN curl https://keybase.io/codecovsecurity/pgp_keys.asc | gpg --no-default-keyring --keyring trustedkeys.gpg --import
RUN curl -Os https://uploader.codecov.io/latest/alpine/codecov
RUN curl -Os https://uploader.codecov.io/latest/alpine/codecov.SHA256SUM
RUN curl -Os https://uploader.codecov.io/latest/alpine/codecov.SHA256SUM.sig
RUN gpg --verify codecov.SHA256SUM.sig codecov.SHA256SUM
RUN shasum -a 256 -c codecov.SHA256SUM
RUN chmod +x codecov
RUN mv codecov /usr/local/bin

WORKDIR /var/task
