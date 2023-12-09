## Image contains

| Tool           | Version                  |
|----------------|--------------------------|
| PHP            | VAPOR_VERSION            |
| pcov           | defined by alpine images |
| codecov        | latest                   |
| composer       | latest, COMPOSER_HASH    |
| bash           | defined by alpine images |
| gnupg          | defined by alpine images |
| gpgv           | defined by alpine images |
| perl-utils     | defined by alpine images |
| openssh-client | defined by alpine images |
| git            | defined by alpine images |

## Container running flow

How the rootfs files ares used:

![](./vapor-ci.svg)

## Tools

- https://serverspec.org/resource_types.html
