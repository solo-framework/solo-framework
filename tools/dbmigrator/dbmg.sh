#!/bin/sh

case $1 in
    help)
        php dbmigrator.php help ;;
    init)
        php dbmigrator.php init $2;;
    create)
        php dbmigrator.php create $2;;
    commit)
        php dbmigrator.php commit $2 $3;;
    delete)
        php dbmigrator.php delete $2 $3;;
    goto)
        php dbmigrator.php goto $2 $3 $4;;
    info)
        php dbmigrator.php info $2;;
    log)
        php dbmigrator.php log $2;;
    showdelta)
        php dbmigrator.php showdelta $2 $3;;
    *)
        php dbmigrator.php help ;;
esac