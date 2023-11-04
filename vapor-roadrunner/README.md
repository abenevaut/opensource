#
# docker build --build-arg VAPOR_VERSION="81" . --tag abenevaut/vapor-roadrunner:latest
#
# Add `--add-host host.docker.internal:host-gateway` when you use windows
# docker run --rm -v "./.docker/storage:/var/task/storage" -p 8080:8080 abenevaut/vapor-roadrunner:latest
#

# Docker images

First of all, do `cp .env.example .env`

## Set up servers

- Create your environment file `cp .env.example .env` & set it up with your own parameters

### Build parameters

- VAPOR_VERSION : vapor docker image version, default `81` (https://hub.docker.com/r/laravelphp/vapor/tags)
- COMPOSER_HASH : composer hash to validate the composer binary (https://getcomposer.org/download/)

#### Images tags

- TAG_VAPOR : tag the `vapor` image during build, default `node_16-php_${VAPOR_VERSION}`

## Build images

- in this project, `vapor-roadrunner` image is pushed to docker hub
- the production tag defined is `TAG_VAPOR` with `php${VAPOR_VERSION}`

### Build latest image (example with `vapor` image)

This image is create when we update node or php or both version

- update `.env` with new node/php version
- first build the latest tag with command `TAG_VAPOR=latest docker-compose build vapor-roadrunner`
- build the new tag with command `docker-compose build vapor-roadrunner`; that build should be really fast, because it uses the `latest` tag as cache
- finally, push tags to docker hub `TAG_VAPOR=latest docker-compose push vapor-roadrunner` then `docker-compose push vapor-roadrunner`

### Build unfollowed revision tag (example with `vapor` image)

- update `.env` with new node/php version
- for this kind of build, we DO NOT build `latest` tag
- build the new tag with command `docker-compose build vapor-roadrunner`; you are able to create custom tag by using  `TAG_VAPOR=<custom_tag> docker-compose vapor-roadrunner`
- finally, push tags to docker hub `docker-compose push vapor-roadrunner`; you are able to create custom tag by using  `TAG_VAPOR=<custom_tag> docker-compose vapor-roadrunner`
