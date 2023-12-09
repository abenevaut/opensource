## Build

```shell
docker build . --file Dockerfile --tag abenevaut/vapor-roadrunner:test \
    --build-arg VAPOR_VERSION=81 
```

- VAPOR_VERSION: vapor docker version, default `81`
- 🤓see also defined env variable in docker-compose en file located in upper directory (`../.env.example`)

## Usage

### Inheritance

```dockerfile
FROM --platform=linux/amd64 abenevaut/vapor-roadrunner:latest

COPY . /var/task

USER root
RUN chown -R nobody.nobody /var/task

CMD rr serve -c /var/task/.rr.yaml -w /var/task

EXPOSE 8080
```

Note: the `CMD` instruction is required to execute roadrunner binary with flexibilities.
If you are adventurous you can add a roadrunner service in `rootfs/etc/service` to lunch it automatically.

- example of `rootfs/etc/service/roadrunner/run`: (remember to `COPY` it from your Dockerfile)

```shell
#!/bin/sh -e

rr serve -c /var/task/.rr.yaml -w /var/task
```

### Customize with inheritance

#### Install PHP extension

```dockerfile
RUN pecl install imagick
RUN docker-php-ext-enable imagick
```

Then, setup extension file (ex: `imagick.ini`) in `rootfs/usr/local/etc/php/conf.d` or in `rootfs/usr/local/etc/php/templates/conf.d`, if you would like to override configuration values with ENV vars.

Note: the entrypoint script run services located in `rootfs/etc/service`, and `php/run` setup templates.

## Test

Docker testing is running with Ruby (with https://bundler.io/)

```shell
bundle config path vendor/bundle
bundle install
bundle exec rspec
```

### On Windows

- Setup Docker with setting "Expose daemon on tcp://localhost:2375 without TLS"

```shell
DOCKER_URL=tcp://localhost:2375 bundle exec rspec
```

## Linter

```shell
bundle exec rubocop
```
