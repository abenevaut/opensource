## Build

```shell
docker build . --file Dockerfile --build-arg TYPE=latest --tag abenevaut/ollama-and-friends:test
docker build . --file Dockerfile --build-arg TYPE=rocm --tag abenevaut/ollama-and-friends:test-rocm
```

Note: `rocm` tag are used to run Ollama with AMD CPU/GPU (https://rocm.docs.amd.com/projects/install-on-linux/en/latest/how-to/docker.html).

### Build custom models locally

#### generalist-writer

```shell
ollama create abenevaut/generalist-writer:dsm7xx-mistral -f models/dsm7xx-generalist-writer-mistral.modelfile
ollama create abenevaut/generalist-writer-mistral:dsm7xx-qwen2.5 -f models/dsm7xx-generalist-writer-qwen2.5.modelfile
```

#### php-quality-expert

```shell
ollama create abenevaut/php-quality-expert:dsm7xx-codellama -f models/dsm7xx-php-quality-expert-codellama.modelfile
ollama create abenevaut/php-quality-expert:dsm7xx-mistral -f models/dsm7xx-php-quality-expert-mistral.modelfile
ollama create abenevaut/php-quality-expert:dsm7xx-qwen2.5 -f models/dsm7xx-php-quality-expert-qwen2.5.modelfile
```

## Usage

- https://hub.docker.com/r/ollama/ollama

```shell
docker volume create ollama_data
docker run -it --rm --name ollama -p 11434:11434 -v ollama_data:/root/.ollama ghcr.io/abenevaut/ollama-and-friends:latest
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
