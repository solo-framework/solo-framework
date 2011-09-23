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
        $numArgs = func_num_args();
        if ($numArgs < 2)
            throw new Exception("Comment and configPath required");

        $args = func_get_args();

        $configPath = $args[count($args) - 1];
        unset($args[count($args) - 1]);

        parent::__construct($configPath);

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
	    $version = $this->receiver->getCurrentVersion($this->config['migrationStorage']);
        echo "New migration #{$version} was added\n";
    }
}
