# vapor-default

Inherits from [Docker Vapor images](https://github.com/laravel/vapor-dockerfiles) ([Docker hub](https://hub.docker.com/r/laravelphp/vapor/tags)), this suite provides defaults ISO Vapor images to work with locally.

## Build

```shell
docker build . --file Dockerfile --tag abenevaut/vapor-default:test --build-arg VAPOR_VERSION=83
```

- VAPOR_VERSION: vapor docker version, default `83`

## Usage

### Customize with inheritance

#### Install PHP extension

```dockerfile
RUN pecl install imagick
RUN docker-php-ext-enable imagick
```

Then, setup extension file (ex: `imagick.ini`) in `rootfs/usr/local/etc/php/conf.d` or in `rootfs/usr/local/etc/php/templates/conf.d`, if you would like to override configuration values with ENV vars.

Note: the entrypoint script run services located in `rootfs/etc/service`, and `php/run` setup templates.

## Test

Docker testing is running with Ruby 2.7 (with https://bundler.io/)

```shell
bundle config path vendor/bundle
bundle install
bundle exec rspec
```

### On Windows

- https://rubyinstaller.org/downloads/
- Setup Docker with setting "Expose daemon on tcp://localhost:2375 without TLS"

```shell
DOCKER_URL=tcp://localhost:2375 bundle exec rspec
```

## Linter

```shell
bundle exec rubocop
```
