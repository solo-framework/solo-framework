<?php
/**
 * Команда миграции к версии БД
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  GotoCommand.php 27.05.11 17:31 evkur
 * @link     nolink
 */


require_once 'BaseCommand.php';
require_once realpath(dirname(__FILE__)) . '/../MigrationManager.php';

class GotoCommand extends BaseCommand
{
    private $migrNum = null;
    private $forceMigrate = false;

    function __construct($migrNum, $forceMigrate = false)
    {
        parent::__construct();
        
        $this->migrNum = $migrNum;
        $this->forceMigrate = ($forceMigrate == '-f') ? true : false;
    }
    
    public function execute()
    {
        echo "Please, waiting...\n";
        if ($this->migrNum == 'head')
        {
            $this->receiver->gotoLastMigration($this->config['migrationStorage']);
            echo "Migration to HEAD was succeed\n";
        }
        else
        {
            $this->receiver->gotoMigration(
                $this->config['migrationStorage'],
                $this->migrNum,
                $this->forceMigrate
            );

            echo "Migration to #{$this->migrNum} was succeed.";
            echo $this->forceMigrate ? " (Forced)\n" : "\n";
        }
    }
}
