<?php
/**
 * точка входа в приложение
 *
 * PHP version 5
 *
 * @package  Application
 * @author   Andrey Filippov <afi.work@gmail.com>
 */

require_once "../framework/core/BaseApplication.php";
require_once '../app/Application.php';

$basePath = "../";
$config = dirname(__FILE__) . "/../config/local.php";

Application::createApplication($basePath, $config);
Application::getInstance()->handleRequest();

?>