ARG VAPOR_VERSION
FROM ghcr.io/roadrunner-server/roadrunner:latest AS roadrunner

ARG VAPOR_VERSION
FROM --platform=linux/amd64 laravelphp/vapor:php${VAPOR_VERSION}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source https://github.com/abenevaut/opensource

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

USER nobody

WORKDIR /var/task

EXPOSE 8080
