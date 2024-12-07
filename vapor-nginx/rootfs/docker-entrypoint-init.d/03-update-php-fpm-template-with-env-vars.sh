#!/bin/sh

#
# Replace ENV vars in configuration files
#

tmpfile=$(mktemp)
cat /usr/local/etc/php/templates/php-fpm.d/www.conf | envsubst "$(env | cut -d= -f1 | sed -e 's/^/$/')" | tee "$tmpfile" > /dev/null
mv "$tmpfile" /usr/local/etc/php-fpm.d/www.conf
