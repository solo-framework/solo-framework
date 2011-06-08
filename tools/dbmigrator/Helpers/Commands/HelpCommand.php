<?php
/**
 * Команда вывода справки
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  HelpCommand.php 27.05.11 17:32 evkur
 * @link     nolink
 */


require_once 'BaseCommand.php';

class HelpCommand extends BaseCommand
{
    function __construct() {}

    public function execute()
    {
$msg = "
DataBase Migration tool. Only MySQL supports.
See config file 'dbmigration.ini'.

Commands:

init    - create empty migration table

create  - create temp migration directory

commit [<comment>] - commit migration

delete <num> - delete migration with number <num>

goto <num|head> [-f] - migrate to version
    <num> - migratoin number
    <head> - migrate to last
    [-f] - force migration

help - list of commands

info - show current migration version

log - show migrations log

showdelta [-u] - show delta by mysql binlog
    [-u] - show only unique queries

";

        echo $msg;
    }
}
