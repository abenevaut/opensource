#!/bin/sh

#
# Replace ENV vars in configuration files
#

for entry in "/usr/local/etc/php/templates/conf.d"/*;do
    if [[ -f $entry ]] ;then
      tmpfile=$(mktemp)
      cat $entry | envsubst "$(env | cut -d= -f1 | sed -e 's/^/$/')" | tee "$tmpfile" > /dev/null
      mv "$tmpfile" "/usr/local/etc/php/conf.d/$(basename $entry)"
    fi
done
