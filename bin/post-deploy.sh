#!/bin/sh
#
# If DB schema has changed then update DB schema

php bin/console doctrine:schema:validate --env=prod --quiet

if [ $? -ne 0 ]; then
    php bin/console doctrine:schema:update --force --env=prod
fi
