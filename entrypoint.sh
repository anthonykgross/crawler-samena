#!/bin/bash
set -e

php composer.phar install
chmod 777 * -Rf
php index.php "$CRAWLER_START_DATE" "$CRAWLER_END_DATE"