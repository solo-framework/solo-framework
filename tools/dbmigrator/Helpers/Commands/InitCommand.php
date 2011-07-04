<?php
/**
 * Команда инициализации репозитория миграций
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  InitCommand.php 27.05.11 17:34 evkur
 * @link     nolink
 */


require_once 'BaseCommand.php';
require_once realpath(dirname(__FILE__)) . '/../MigrationManager.php';

class InitCommand extends BaseCommand
{
    public function execute()
    {
        $this->receiver->init($this->config['migrationStorage']);
        echo "Initialized. Ok\n";
    }
}
