#
# docker build --build-arg VAPOR_VERSION="81" . --tag abenevaut/opensource:latest-vapor-roadrunner
#
# Add `--add-host host.docker.internal:host-gateway` when you use windows
# docker run --rm -v "./.docker/storage:/var/task/storage" -p 8080:8080 abenevaut/opensource:latest-vapor-roadrunner
#

FROM ghcr.io/roadrunner-server/roadrunner:2023.3.3 AS roadrunner
ARG VAPOR_VERSION
FROM --platform=linux/amd64 laravelphp/vapor:php${VAPOR_VERSION}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source https://github.com/abenevaut/opensource

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

EXPOSE 8080

WORKDIR /var/task

COPY . /var/task

USER root

RUN chown -R nobody.nobody /var/task

CMD rr serve -c /var/task/.rr.yaml -w /var/task
