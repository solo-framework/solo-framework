<?php
/**
 * точка входа в приложение
 *
 * PHP version 5
 *
 * @package  Application
 * @author   Andrey Filippov <afi@i-loto.ru>
 */
error_reporting(E_ALL | E_STRICT);
require_once "../framework/core/Application.php";


$basePath = "../";
$config = dirname(__FILE__) . "/../config/local.php";

Application::createApplication($basePath, $config);
Application::getInstance()->handleRequest();
?>