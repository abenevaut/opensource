ARG NODEJS_VERSION=22
FROM node:${NODEJS_VERSION}-alpine

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/opensource
LABEL org.opencontainers.image.path="node-default/Dockerfile"
LABEL org.opencontainers.image.title="node-default"
LABEL org.opencontainers.image.description="Inherits from Docker NodeJS images, this suite provides defaults NodeJs images to work with locally."
LABEL org.opencontainers.image.authors="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.documentation="https://github.com/abenevaut/opensource/node-default/README.md"

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
    && chmod +x /bin/docker-entrypoint.sh \
    && chmod +x /docker-entrypoint-init.d/01-uname.sh

# Expose the port storybook is reachable on
EXPOSE 6006

# Add application
WORKDIR /usr/src/app

# Let runit start services
ENTRYPOINT [ "/bin/docker-entrypoint.sh" ]

CMD []
