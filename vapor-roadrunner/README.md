## Build

```shell
docker build . --file Dockerfile --tag abenevaut/vapor-roadrunner:test \
    --build-arg VAPOR_VERSION=81 \
    --platform=linux/arm64/v8
```

- VAPOR_VERSION: vapor docker version, default `81`
- 🤓see also defined env variable in docker-compose en file located in upper directory (`../.env.example`)

## Usage

### Inheritance

```dockerfile
FROM --platform=linux/amd64 abenevaut/vapor-roadrunner:latest

COPY . /var/task

USER root

# Add services & configuration files
COPY --chown=nobody rootfs/ /

RUN chown -R nobody.nobody /var/task

EXPOSE 8080
```

Add a roadrunner service in `rootfs/etc/service` to lunch it automatically.

- example of `rootfs/etc/service/roadrunner/run`:
```shell
#!/bin/sh -e

for entry in "/usr/local/etc/php/templates/conf.d"/*;do
    if [[ -f $entry ]] ;then
      tmpfile=$(mktemp)
      cat $entry | envsubst "$(env | cut -d= -f1 | sed -e 's/^/$/')" | tee "$tmpfile" > /dev/null
      mv "$tmpfile" "/usr/local/etc/php/conf.d/$(basename $entry)"
    fi
done

# pipe stderr to stdout and run nginx omiting ENV vars to avoid security leaks
exec 2>&1
exec rr serve -c /var/task/.rr.yaml -w /var/task
```

- example of `rootfs/etc/service/octane/run`:
```shell
#!/bin/sh -e

for entry in "/usr/local/etc/php/templates/conf.d"/*;do
    if [[ -f $entry ]] ;then
      tmpfile=$(mktemp)
      cat $entry | envsubst "$(env | cut -d= -f1 | sed -e 's/^/$/')" | tee "$tmpfile" > /dev/null
      mv "$tmpfile" "/usr/local/etc/php/conf.d/$(basename $entry)"
    fi
done

# pipe stderr to stdout and run nginx omitting ENV vars to avoid security leaks
exec 2>&1
exec php /var/task/artisan octane:start --port=8080 --rr-config=/var/task/.rr.yaml
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
