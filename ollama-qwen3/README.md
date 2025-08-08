## Build

```shell
docker build . --file Dockerfile --build-arg TYPE=latest --tag abenevaut/ollama:test-qwen3-0.6b
docker build . --file Dockerfile --build-arg TYPE=rocm --tag abenevaut/ollama:test-rocm-qwen3-0.6b
```

Note: `rocm` tag are used to run Ollama with AMD CPU/GPU (https://rocm.docs.amd.com/projects/install-on-linux/en/latest/how-to/docker.html).

## Usage

- https://hub.docker.com/r/ollama/ollama

```shell
docker volume create ollama_data
docker run -it --rm --name ollama -p 11434:11434 -v ollama_data:/root/.ollama ghcr.io/abenevaut/ollama:qwen3-0.6b
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
docker run --rm -v //var/run/docker.sock:/var/run/docker.sock:ro -v $(pwd):/app -w /app ruby:3.4 bash -c "bundle config path vendor/bundle && bundle install && bundle exec rspec"
```

## Linter

```shell
bundle exec rubocop
```

### In Docker

```bash
docker run --rm -v //var/run/docker.sock:/var/run/docker.sock:ro -v $(pwd):/app -w /app ruby:3.4 bash -c "bundle config path vendor/bundle && bundle install && bundle exec rubocop"
```
