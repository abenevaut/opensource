# vapor-default

Inherits from [Docker Vapor images](https://github.com/laravel/vapor-dockerfiles) ([Docker hub](https://hub.docker.com/r/laravelphp/vapor/tags)), this suite provides defaults ISO Vapor images to work with locally.

## Build

### Build arguments

| Argument | Description | Default | Values |
|----------|-------------|---------|--------|
| `TARGETARCH` | Target architecture | `amd64` | `amd64`, `arm64` |
| `VAPOR_VERSION` | Vapor Docker PHP version | `85` | `83`, `84`, `85` |
| `__VAPOR_RUNTIME` | Vapor runtime (required by base image) | `docker` | `docker` (amd64), `docker-arm` (arm64) |

### AMD64 (default)

```shell
docker build . --file Dockerfile --tag abenevaut/vapor-default:test \
  --build-arg VAPOR_VERSION=85
```

### ARM64 (Apple Silicon, etc.)

```shell
docker build . --file Dockerfile --tag abenevaut/vapor-default:test \
  --build-arg TARGETARCH=arm64 \
  --build-arg __VAPOR_RUNTIME=docker-arm \
  --build-arg VAPOR_VERSION=85
```

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
