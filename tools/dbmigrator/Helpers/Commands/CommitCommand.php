<?php
/**
 * Команда добавления миграции
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  CommitCommand.php 27.05.11 17:28 evkur
 * @link     nolink
 */


require_once 'BaseCommand.php';
require_once realpath(dirname(__FILE__)) . '/../MigrationManager.php';

class CommitCommand extends BaseCommand
{
    private $comment = null;

    function __construct()
    {
        parent::__construct();

        $numArgs = func_num_args();
        if ($numArgs == 0)
            throw new Exception("Comment required");

        $args = func_get_args();
        $this->comment = implode(" ", $args);

        if (stristr(PHP_OS, 'WIN'))
        {
            $this->comment = iconv('windows-1251', 'utf-8', $this->comment);
        }        
    }

    public function execute()
    {
        $this->receiver->commitMigration(
            $this->config['migrationTempPath'],
            $this->config['migrationStorage'],
            $this->comment
        );
        echo "New migration was added\n";
    }
}
