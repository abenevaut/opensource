## Build

```shell
docker build . --file Dockerfile --tag abenevaut/vapor-ci:test \
    --build-arg VAPOR_VERSION=81 \
    --build-arg COMPOSER_HASH=dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6
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

### With GithubAction

- Set the docker image in `.github/workflows/<your-pipeline>.yml`

```yaml
name: <your-pipeline>

jobs:
  build:
    runs-on: ubuntu-latest
    container: ghcr.io/abenevaut/vapor-ci:latest
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
