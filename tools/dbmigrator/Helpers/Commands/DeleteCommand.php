<?php
/**
 * Команда удаления миграции
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  DeleteCommand.php 27.05.11 17:30 evkur
 * @link     nolink
 */


require_once 'BaseCommand.php';
require_once realpath(dirname(__FILE__)) . '/../MigrationManager.php';

class DeleteCommand extends BaseCommand
{
    private $migrNum = null;

    function __construct($migrNum)
    {
        parent::__construct();
        
        $this->migrNum = $migrNum;                
    }

    public function execute()
    {
        $this->receiver->deleteMigration($this->migrNum, $this->migrStorage);
        echo "Migration #{$this->migrNum} was deleted\n";
    }
}
