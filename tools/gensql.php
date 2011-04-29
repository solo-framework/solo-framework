<?php
/**
 * Файл генерирует дамп базы данных MySQL
 * scheme.sql - Схема базы
 * data.sql - данные таблиц
 * triggers.sql - скрип создания триггеров
 * procedures.sql - скрипт создания хранимых процедур
 * 
 * Вызов скрипта php -f gensql.php host_name database_name mysql_user mysql_password
 * 
 * PHP version 5
 * 
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: gensql.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

//Все аргументы, переданные скрипту
$host = @$argv[1];
$dbName = @$argv[2];
$user = @$argv[3];
$password = @$argv[4];

echo <<<EOT

=========================================
SQL dump generator
	Output files : 
		scheme.sql - Scheme of database
		data.sql - Data for database
		triggers.sql - Scheme of triggers
		procedures.sql - Scheme of stored procedures
=========================================	

EOT;

if (!$host || !$dbName || !$user || !$password)
{
	echo <<<EOT

Usage: php -f gensql.php host_name database_name mysql_user mysql_password	

EOT;
exit();
}

// кодировка выходного SQL скрипта
$encoding = "utf8";

#------------------------------------
# Генерация схемы
#------------------------------------

$retVal = null;
$output = null;
echo "Creating scheme for '{$dbName}'....\n";
$res = exec("mysqldump --host={$host} --password={$password} -u {$user} --dump-date=false --skip-triggers --no-autocommit --disable-keys --add-drop-table --set-charset --default-character-set={$encoding} --no-data {$dbName} 2>&1", $output, $retVal);
echo ($retVal == 0) ? "ok\n" : die("Error: " . print_r($output, 1));

// склеить возвращенную строку с SQL-кодом
$schemeData = implode("\n", $output);

// нужно удалить строки типа AUTO_INCREMENT=123
$schemeData = preg_replace('/(AUTO_INCREMENT=[\d]+)/si', '', $schemeData);

// нужно удалить строки типа /*!50017 DEFINER=`root`@`localhost`*/ 
$schemeData = preg_replace('%/\*![\d]+\sDEFINER.*?\*/%si', '', $schemeData);
file_put_contents("scheme.sql", $schemeData);

#------------------------------------
# Генерация данных
#------------------------------------

$retVal = null;
$output = null;
echo "Creating data for '{$dbName}'....\n";
$res = system("mysqldump --host={$host} --password={$password} -u {$user} --dump-date=false --skip-triggers --no-autocommit --disable-keys --set-charset --default-character-set={$encoding} --no-create-info --extended-insert=false  --result-file=data.sql {$dbName} 2>&1", $retVal);
echo ($retVal == 0) ? "ok\n" : die("Error: " . print_r($output, 1));
	
#------------------------------------
# Генерация триггеров
#------------------------------------

$output = null;
$retVal = null;
echo "Creating triggers for '{$dbName}'....\n";
$res = exec("mysqldump --host={$host} --password={$password} -u {$user} --dump-date=false --disable-keys  --default-character-set={$encoding} --no-create-info --no-data --extended-insert=false --triggers=true {$dbName} 2>&1", $output, $retVal);
echo ($retVal == 0) ? "ok\n" : die("Error: " . print_r($output, 1));

// склеить возвращенную строку с SQL-кодом
$triggersData = implode("\n", $output);

// нужно удалить строки типа /*!50017 DEFINER=`root`@`localhost`*/ 
$triggersData = preg_replace('%/\*![\d]+\sDEFINER.*?\*/%si', '', $triggersData);
file_put_contents("triggers.sql", $triggersData);
	
#------------------------------------
# Генерация хранимых процедур
#------------------------------------	

$output = null;
$retVal = null;
echo "Creating stored procedures for '{$dbName}'....\n";
$res = exec("mysqldump --host={$host} --password={$password} -u {$user} --dump-date=false --routines --default-character-set={$encoding} --no-create-info --no-data --extended-insert=false --triggers=false {$dbName} 2>&1", $output, $retVal);
echo ($retVal == 0) ? "ok\n" : die("Error: " . print_r($output, 1));

// склеить возвращенную строку с SQL-кодом
$spData = implode("\n", $output);

// нужно удалить строки типа /*!50017 DEFINER=`root`@`localhost`*/ 
$spData = preg_replace('%/\*![\d]+\sDEFINER.*?\*/%si', '', $spData);
file_put_contents("procedures.sql", $spData);

echo "\nCompleted...\n";
?>