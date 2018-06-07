#!/usr/bin/env bash

function usage {
    echo "Usage:"
    echo "  $0 [options]"
    echo ""
    echo "Options:"
    echo "  -c, --coverage          Generate CodeCoverage HTML report into _output/coverage"
    echo "  -o, --codecept-args     Args and options to pass to \"codecept run\" command"
    echo "  -h, --help              Display this help message"
    exit 1
}

if [ ! -f "./codeception.yml" ]; then
    echo "File codeception.yml does not exist."
    exit 1
fi

export PATH="$PATH:$HOME/.composer/vendor/bin/:./vendor/bin:./bin"

CODECEPT_OPTIONS=""
while [[ $# -gt 0 ]]
do
    case "$1" in
        -c|--coverage)
            CODECEPT_OPTIONS="$CODECEPT_OPTIONS --coverage --coverage-html"
        ;;
        -o|--codecept-args)
            CODECEPT_OPTIONS="$CODECEPT_OPTIONS $2"
            shift # pass value
        ;;
        -h|--help)
            usage
        ;;
        *)
            echo "The \"$1\" option does not exist."
            usage
            exit 1
        ;;
    esac
    shift # pass argument or value
done

echo "Runing codecept with following arguments :  $CODECEPT_OPTIONS"

codecept build

php bin/console doctrine:schema:update --force --env=test
php bin/console doctrine:fixtures:load --no-interaction --env=test
sqlite3 tests/_data/test.sqlite .dump > tests/_data/test.sql

codecept run $CODECEPT_OPTIONS
