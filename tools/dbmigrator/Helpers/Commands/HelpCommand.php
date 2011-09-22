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
See example config file 'dbmigration.ini.example'.

Commands:

help - list of commands

init <configPath>    - create empty migration table

create <configPath>  - create temp migration directory

commit <comment> <configPath> - commit migration

info <configPath>    - show current migration version

log <configPath>     - show migrations log

goto  <id|head> <configPath> [-f] - migrate to version
      <id> - migratoin id
      <head> - migrate to last
      [-f] - force migration

showdelta <configPath> [-u] - show delta by mysql binlog
          [-u] - show only unique queries

";

        echo $msg;
    }
}
