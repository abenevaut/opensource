## Build

```shell
docker build . --file Dockerfile --tag abenevaut/self-coder-github-ci:test
```

## Usage

### With GithubAction

- Set the docker image in `.github/workflows/<your-pipeline>.yml`

```yaml
name: <your-pipeline>

jobs:
  build:
    runs-on: ubuntu-latest
    container: ghcr.io/abenevaut/copilot-ci:latest
```

## Test

Docker testing is running with Ruby 3.4 (with https://bundler.io/)

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

### In Docker

```bash
docker run --rm -v //var/run/docker.sock:/var/run/docker.sock:ro -v $(pwd):/app -w /app ruby:3.4 bash -c "apt-get update -yqq && apt-get install -yqq docker-cli && bundle config path vendor/bundle && bundle install && bundle exec rspec"
```

## Linter

```shell
bundle exec rubocop
```

### In Docker

```bash
docker run --rm -v //var/run/docker.sock:/var/run/docker.sock:ro -v $(pwd):/app -w /app ruby:3.4 bash -c "apt-get update -yqq && apt-get install -yqq docker-cli && bundle config path vendor/bundle && bundle install && bundle exec rubocop"
```
