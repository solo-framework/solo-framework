<?php
/**
 * Базовая класс команды
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  BaseCommand.php 27.05.11 17:27 evkur
 * @link     nolink
 */


abstract class BaseCommand
{
    protected $receiver = null;
    protected $config = null;

    function __construct($configPath)
    {
        if (!file_exists($configPath))
        {
            throw new Exception("Configuration file: '{$configPath}' not found");
        }

        $this->config = parse_ini_file($configPath);
        
        $this->receiver = new MigrationManager(
            $this->config['host'],
            $this->config['user'],
            $this->config['pass'],
            $this->config['dbname']
        );
    }

    abstract public function execute();
}
