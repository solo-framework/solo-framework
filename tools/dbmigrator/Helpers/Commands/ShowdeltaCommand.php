<?php
/**
 * Команда показывает дельту по бинарным логам
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  ShowdeltaCommand.php 06.06.11 13:59 evkur
 * @link     nolink
 */

require_once 'BaseCommand.php';
require_once realpath(dirname(__FILE__)) . '/../MigrationManager.php';

class ShowdeltaCommand extends BaseCommand
{
    private $showUnique = false;

    function __construct($showUnique = false)
    {
        parent::__construct();

        $this->showUnique = ($showUnique == '-u') ? true : false;
    }

    public function execute()
    {
        echo $this->receiver->getDeltaByBinLog(
            $this->config['binaryLogPath'],
            $this->config['migrationStorage'],
            $this->showUnique
        );
    }
}
