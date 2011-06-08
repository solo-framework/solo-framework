<?php
/**
 * Команда создания миграции
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  CreateCommand.php 27.05.11 17:29 evkur
 * @link     nolink
 */

require_once 'BaseCommand.php';

class CreateCommand extends BaseCommand
{
    public function execute()
    {
        $migrPath = $this->config['migrationTempPath'];
        MigrationManager::createMigration($migrPath);
        echo "Created {$migrPath}\n";
    }
}
