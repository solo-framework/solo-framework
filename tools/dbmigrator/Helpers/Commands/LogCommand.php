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

        echo "\tUid\t\tCreateTime\t\tComment\n\n";

        /* @var $m Migration */
        foreach ($migrations as $m)
        {
            printf("%-19s %-23s %s\n",
                $m->createTime,
                date('Y-m-d H:i:s', $m->createTime),
                stristr(PHP_OS, 'WIN') ?
                    iconv('utf-8', 'windows-1251', $m->comment) : $m->comment
            );

        }
    }
}
