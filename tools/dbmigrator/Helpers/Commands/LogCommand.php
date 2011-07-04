<?php
/**
 * Команда показывает логи миграций
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  LogCommand.php 27.05.11 17:34 evkur
 * @link     nolink
 */

require_once 'BaseCommand.php';
require_once realpath(dirname(__FILE__)) . '/../MigrationManager.php';

class LogCommand extends BaseCommand
{    
    public function execute()
    {
        date_default_timezone_set('Europe/Moscow');
        $migrations = $this->receiver->getAllMigrations('DESC');

        echo "Number\tCreateTime\t\tComment\n\n";
        /* @var $m Migration */
        foreach ($migrations as $m)
        {
            echo "#{$m->number}\t";
            echo date('Y-m-d H:i:s', $m->createTime) . "\t";

            if (stristr(PHP_OS, 'WIN'))
            {
                echo iconv('utf-8', 'windows-1251', $m->comment) . "\n";
            }
            else
            {
                echo $m->comment . "\n";
            }

        }
    }
}
