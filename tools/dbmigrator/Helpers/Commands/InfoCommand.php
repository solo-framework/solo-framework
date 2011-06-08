<?php
/**
 * Команда вывода информации о текущей версии БД
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  InfoCommand.php 27.05.11 17:33 evkur
 * @link     nolink
 */


require_once 'BaseCommand.php';
require_once realpath(dirname(__FILE__)) . '/../MigrationManager.php';

class InfoCommand extends BaseCommand
{
    public function execute()
    {
        $version = MigrationManager::getCurrentVersion($this->config['migrationStorage']);
        echo "Current migration version is #{$version}\n";
    }
}
