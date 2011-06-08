#!/bin/sh

case $1 in
    help)
        php dbmigrator.php help ;;
    init)
        php dbmigrator.php init ;;
    create)
        php dbmigrator.php create ;;
    commit)
        php dbmigrator.php commit $2 ;;
    delete)
        php dbmigrator.php delete $2 ;;
    goto)
        php dbmigrator.php goto $2 $3 ;;
    info)
        php dbmigrator.php info ;;
    log)
        php dbmigrator.php log ;;
    showdelta)
        php dbmigrator.php showdelta $2 ;;
    *)
        echo "Unknown command."
esac