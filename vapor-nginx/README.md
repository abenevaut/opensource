## Image contains

| Tool  | Version                  |
|-------|--------------------------|
| PHP   | VAPOR_VERSION            |
| nginx | defined by alpine images |

## Build


```shell
docker build . --file Dockerfile --tag abenevaut/vapor-nginx:test \
    --build-arg VAPOR_VERSION=81 
```

- VAPOR_VERSION: vapor docker version, default `81`
- 🤓see also defined env variable in docker-compose en file located in upper directory (`../.env.example`)

## Usage

### Inheritance

```dockerfile
FROM --platform=linux/amd64 abenevaut/vapor-nginx:latest

COPY . /var/task

USER root
RUN chown -R nobody.nobody /var/task
```

### Customize with heritage

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
