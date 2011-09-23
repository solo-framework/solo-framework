<?php
/**
 * Мигратор БД
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  dbmigrator.php 27.05.11 17:41 evkur
 * @link     nolink
 */

date_default_timezone_set("Europe/Moscow");

require_once 'Helpers/ArgumentsExtractor.php';
require_once 'Helpers/CommandFactory.php';

try
{
    list($command, $params) = ArgumentsExtractor::extract($argv);
    $command = CommandFactory::create($command, $params);
    $command->execute();
}
catch (Exception $e)
{
    echo $e->getMessage() . PHP_EOL;
    //echo $e->getTraceAsString() . PHP_EOL;
}