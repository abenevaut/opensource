## Image contains

| Tool           | Version                  |
|----------------|--------------------------|
| PHP            | VAPOR_VERSION            |
| pcov           | defined by alpine images |
| codecov        | latest                   |
| composer       | latest, COMPOSER_HASH    |
| nodejs         | defined by alpine images |
| npm            | defined by alpine images |
| bash           | defined by alpine images |
| gnupg          | defined by alpine images |
| gpgv           | defined by alpine images |
| perl-utils     | defined by alpine images |
| openssh-client | defined by alpine images |
| git            | defined by alpine images |

## Build

```shell
docker build . --file Dockerfile --tag abenevaut/vapor-ci:test \
    --build-arg VAPOR_VERSION=81 \
    --build-arg COMPOSER_HASH=e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02
```

- VAPOR_VERSION: vapor docker version, default `81`
- COMPOSER_HASH: composer hash to validate the binary (https://getcomposer.org/download/)
- 🤓see also defined env variable in docker-compose en file located in upper directory (`../.env.example`)

## Usage

### With CircleCi

- Set the docker image in `.circleci/config.yml`

```yaml
version: 2

jobs:
  build:
    docker:
      - image: ghcr.io/abenevaut/vapor-ci:latest
```

### Customize with inheritance

#### Install PHP extension

```dockerfile
RUN pecl install imagick
RUN docker-php-ext-enable imagick
```

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
