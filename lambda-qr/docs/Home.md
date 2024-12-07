# lambda-qr documentation
This lambda function allows to generate QR Code from parameters.

## Install

Clone the repository, then install the dependencies:
```shell
composer install
```

[Bref.sh](https://bref.sh/docs/setup) is integrated to deploy the function to AWS lambda.

```shell
npm install -g serverless@3
serverless config credentials --provider aws --key "key" --secret "secret"
```

## Usage

Test locally the function:

```shell
serverless bref:local -f qr --data '{"correction": "L", "format": "png", "size": 100, "text": "1234"}'
serverless bref:local -f qr --data '{"correction": "L", "format": "png", "size": 100, "text": "1234", "image": "https://www.abenevaut.dev/favicon/android-chrome-96x96.png"}'
```

### Staging deployment

```
composer install
serverless deploy
```

### Production deployment

- https://bref.sh/docs/deploy#deploying-for-production

```
composer install --prefer-dist --optimize-autoloader --no-dev
serverless deploy --stage=production
```

## Automated testing

- [bats-core](https://bats-core.readthedocs.io/en/stable/index.html)
- [bats-core GitHub Action](https://github.com/bats-core/bats-action)
