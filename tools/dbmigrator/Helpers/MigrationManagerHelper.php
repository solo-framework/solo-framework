<?php
/**
 * Инкапсулиреут в себе низкоуровневые операции с БД и файловой системой
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  MigrationManagerHelper.php 27.05.11 17:40 evkur
 * @link     nolink
 */

class MigrationManagerHelper
{
    public static $sqlFileSet = array('scheme.sql', 'data.sql', 'procedures.sql', 'triggers.sql', 'delta.sql');
    
    private $db = null;
    private $host = null;
    private $user = null;
    private $password = null;
    private $dbname = null;
    private $encoding = "utf8";

    /**
     * Устанавливает соединение с базой
     *
     * @param string $host Адрес сервера
     * @param string $user Имя пользователя БД
     * @param string $password Пароль
     * @param string $dbname Имя БД
     */
    function __construct($host, $user, $password, $dbname)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->dbname = $dbname;

        $this->db = @mysql_connect($this->host, $this->user, $this->password);
        $this->checkDBConnect();

        mysql_select_db($this->dbname,  $this->db);
        $this->checkDBError();

        $this->executeQuery("SET NAMES utf8");
    }

    /**
     * Закрывает соединение с базой
     */
    function __destruct()
    {
        if (!is_null($this->db))
            @mysql_close($this->db);
    }

    /**
     * Создает таблицу migration
     *
     * @return void
     */
    public function createTable()
    {
        mysql_select_db($this->dbname,  $this->db);
        $this->checkDBError();

        $this->executeQuery
        (
            "CREATE TABLE IF NOT EXISTS `__migration`
            (
                `id`			INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `createTime`	DECIMAL(14, 4) NOT NULL,
                `comment`		VARCHAR(255) NOT NULL,
                PRIMARY KEY (`id`)
            )"
        );
    }

    public function dropTable()
    {
        $this->executeQuery("DROP TABLE IF EXISTS `{$this->dbname}`.`__migration`");
    }

    /**
     * Удаляет все из базы
     *
     * @return void
     */
    public function makeDBEmpty()
    {
        $this->executeQuery("DROP DATABASE IF EXISTS {$this->dbname}");
        $this->executeQuery("CREATE DATABASE {$this->dbname}");
    }

        /**
     * Выполняет sql запрос
     *
     * @param  $sql Запрос
     *
     * @return resource
     */
    public function executeQuery($sql)
    {
        $res = mysql_query($sql, $this->db);
        $this->checkDBError($this->db);

        return $res;
    }

    /**
     * Проверка ошибки mysql
     *
     * @throws RuntimeException
     * @return void
     */
    public function checkDBError()
	{
		$errNum = mysql_errno($this->db);
		$errorDesc = mysql_error($this->db);
		if (0 != $errNum)
			throw new RuntimeException("DataBase error: [". $errNum ."] " . $errorDesc, $errNum);
	}

    /**
     * Проверяет соединение с БД
     *
     * @throws RuntimeException
     *
     * @return void
     */
    public function checkDBConnect()
	{
		$errNum = mysql_errno();
		$errorDesc = mysql_error();
		if (0 != $errNum)
			throw new RuntimeException("DataBase error: [". $errNum ."] " . $errorDesc, $errNum);
	}

    /**
     * Выполняет набор sql файлов из директории
     *
     * @throws Exception
     * @param  $dir Директория с файлами миграции
     * @param  $fileSet Набор имен файлов для выполнения
     *
     * @return void
     */
    public function importFiles($dir, $fileSet)
    {
        foreach ($fileSet as $fileName)
        {
            $path = "{$dir}/{$fileName}";
            if (!is_readable($path))
                throw new Exception("Can't read {$path}");

            $this->executeSQLFromFile($path);
        }
    }

    /**
     * Проверка директорий перед записью
     *
     * @throws Exception
     * @param  $migrStorage Путь, где хранятся миграции
     * @param  $pathToCreate Путь к директории, которую необходимо создать
     *
     * @return void
     */
    public function checkDir($migrStorage, $pathToCreate)
    {
        if (!is_dir($migrStorage))
            throw new Exception("Directory '{$migrStorage}' does not exist");

        if (!is_writable($migrStorage))
            throw new Exception("Directory '{$migrStorage}' is not writable");

        if (is_dir($pathToCreate))
            throw new Exception("Directory {$pathToCreate} already exist");
    }

    public function checkFile($path)
    {
        //$hashEmptyDelta = "19f52acc9ba33950daf5e2980ca56bd1";
	    $hashEmptyDelta = "1b05bbfbef36037f33011dbddedc5d34";

        if (!file_exists($path) || !is_readable($path))
            throw new Exception("Not found temp delta in {$path}. You should use 'create'");

        if ($hashEmptyDelta === md5_file($path))
            throw new Exception("Put your code in {$path}");
    }

    /**
     * Загружает файл с SQL кодом напрямую в БД
     *
     * @param string $file путь к файлу с SQL кодом
     *
     * @return boolean|RuntimeException
     */
	public function executeSQLFromFile($file)
	{
		$retVal = null;
		$output = null;
		exec("mysql --host={$this->host} --password={$this->password} -u {$this->user} {$this->dbname} < {$file} 2>&1", $output, $retVal);
		if ($retVal !== 0)
			throw new RuntimeException($this->parseConsoleError($output));

		return true;
	}

    /**
     * Преобразует список сообщений в строку
     *
     * @param array $array список сообщений
     *
     * @return string
     */
    public function parseConsoleError($array)
    {
        return implode(PHP_EOL, $array);
    }

    /**
     * Удаляет директоррии с миграцией
     *
     * @static
     * @param  $path Путь до миграции
     *
     * @return void
     */
    public static function cleanMigrDir($path)
    {
        foreach (self::$sqlFileSet as $fileName)
        {
            @unlink("{$path}/{$fileName}");
        }
        @rmdir("{$path}");
    }

    public static function createEmptyDelta($path)
    {
        file_put_contents("{$path}/delta.sql", self::getDeltaTemplate());
    }

    public static function getDeltaTemplate()
    {
        $str = "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $str .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $str .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $str .= "/*!40101 SET NAMES utf8 */;\n";
        $str .= "/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;\n";
        $str .= "/*!40103 SET TIME_ZONE='+00:00' */;\n";
        $str .= "/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;\n";
        $str .= "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n";
        $str .= "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n";
        $str .= "/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\n";
        $str .= "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;\n";
        $str .= "\n/*YOU CODE HERE*/\n\n";
        $str .= "/*MIGRATION_INSERT_STATEMENT*/\n";
        $str .= "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n";
        $str .= "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n";
        $str .= "/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;\n";
        $str .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
        $str .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
        $str .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
        $str .= "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\n";

        return $str;
    }

    /**
     * Создает файл дельты из бинарных логов
     *
     * @param  $logPath Путь к бинарным логам MySQL
     * @param  $startTime Время, с которого начинать искать дельту
     *
     * @param bool $unique
     * @return string
     */
    public function getDeltaByBinLog($logPath, $startTime, $unique = false)
    {
        $sLogs = "";
        $res = $this->executeQuery('SHOW BINARY LOGS');
        while ($log = mysql_fetch_array($res))
		{
			$sLogs .= $logPath . DIRECTORY_SEPARATOR . $log['Log_name'] . " ";
		}
        $command = "mysqlbinlog -s -d {$this->dbname} --start-datetime=\"{$startTime}\" -t {$sLogs}" . "\n";
        exec($command, $q);
        $out = implode("\n", $q);

        preg_match("/DELIMITER\s(.*?)\n/is", $out, $result);

        $delimeter = $result[1];
        $queries = explode($delimeter, $out);
        $queries = $this->filterQueries($queries);

        if ($unique)
            $queries = $this->getUniqueQueries($queries);

        $strQueries = implode($delimeter . PHP_EOL, $queries);

        return $strQueries;
    }

    public function getUniqueQueries($queries)
    {
        $qArray  = array();
        foreach ($queries as $q)
        {
            $qArray[] = $q;
        }

        $patters = array(
            "/\t+/is" => " ",
            "/\n+/is" => " ",
            "/\s+/is" => " "
        );
        $res = preg_replace(array_keys($patters), array_values($patters), $qArray);
        $res = array_map('strtolower', $res);
        $res = array_unique($res);

        $uniqueQueries = array();
        foreach ($res as $k => $v)
        {
            $uniqueQueries[] = $qArray[$k];
        }
        
        return $uniqueQueries;
    }

    public function filterQueries($queries)
    {
        $patters = array(
            "{/\*!.+?\*/;}is"    => "", // директивы вида /*!40019 SET ....*/;
            "/^\s*SET.*/is"      => "", // запросы SET ....*/;
            "/^\s*use.*/is"      => "", // запросы use...
            "{/\*!\\\C.+?\*/}is" => "", // запросы /*!\C utf8 */
            "/^\n*/is"           => "", // пустые строки в начале запроса
            "/\n*$/is"           => ""  // пустые строки в конце запроса
        );
        $res = preg_replace(array_keys($patters), array_values($patters), $queries);
        $res = array_filter($res);

        return $res;
    }

    public function createDump($path)
    {
        if (!@mkdir($path, 0777))
            throw new Exception("Can't create {$path}");

        $this->genScheme($path);
        $this->genData($path);
        $this->genTriggers($path);
        $this->genProcedures($path);
    }

    /**
     * Генерация схемы
     *
     * @param $path Директория ,где будет сохранен файл
     *
     * @return void
     */
    private function genScheme($path)
    {
        $retVal = null;
        $output = null;
        echo "Creating scheme for '{$this->dbname}'....\n";
        exec("mysqldump --host={$this->host} --password={$this->password} -u {$this->user} --dump-date=false --skip-triggers --no-autocommit --disable-keys --add-drop-table --set-charset --default-character-set={$this->encoding} --no-data {$this->dbname} --skip-comments 2>&1", $output, $retVal);
        echo ($retVal == 0) ? "ok\n" : die("Error: " . print_r($output, 1));

        // склеить возвращенную строку с SQL-кодом
        $schemeData = implode("\n", $output);

        // нужно удалить строки типа AUTO_INCREMENT=123
        $schemeData = preg_replace('/(AUTO_INCREMENT=[\d]+)/si', '', $schemeData);

        // нужно удалить строки типа /*!50017 DEFINER=`root`@`localhost`*/
        $schemeData = preg_replace('%/\*![\d]+\sDEFINER.*?\*/%si', '', $schemeData);
        file_put_contents("{$path}/scheme.sql", $schemeData);
    }

    /**
     * Генерация данных
     *
     * @param $path Директория ,где будет сохранен файл
     *
     * @return void
     */
    private function genData($path)
    {
        $retVal = null;
        $output = null;
        echo "Creating data for '{$this->dbname}'....\n";
        system("mysqldump --host={$this->host} --password={$this->password} -u {$this->user} --dump-date=false --skip-triggers --no-autocommit --disable-keys --set-charset --default-character-set={$this->encoding} --no-create-info --extended-insert=false  --result-file={$path}/data.sql {$this->dbname} --skip-comments 2>&1", $retVal);
        echo ($retVal == 0) ? "ok\n" : die("Error: " . print_r($output, 1));
    }

    /**
     * Генерация триггеров
     *
     * @param $path Директория ,где будет сохранен файл
     *
     * @return void
     */
    private function genTriggers($path)
    {
        $output = null;
        $retVal = null;
        echo "Creating triggers for '{$this->dbname}'....\n";
        exec("mysqldump --host={$this->host} --password={$this->password} -u {$this->user} --dump-date=false --disable-keys  --default-character-set={$this->encoding} --no-create-info --no-data --extended-insert=false --triggers=true {$this->dbname} --skip-comments 2>&1 ", $output, $retVal);
        echo ($retVal == 0) ? "ok\n" : die("Error: " . print_r($output, 1));

        // склеить возвращенную строку с SQL-кодом
        $triggersData = implode("\n", $output);

        // нужно удалить строки типа /*!50017 DEFINER=`root`@`localhost`*/
        $triggersData = preg_replace('%/\*![\d]+\sDEFINER.*?\*/%si', '', $triggersData);
        file_put_contents("{$path}/triggers.sql", $triggersData);
    }

    /**
     * Генерация хранимых процедур
     * 
     * @param $path Директория ,где будет сохранен файл
     *
     * @return void
     */
    private function genProcedures($path)
    {
        $output = null;
        $retVal = null;
        echo "Creating stored procedures for '{$this->dbname}'....\n";
        exec("mysqldump --host={$this->host} --password={$this->password} -u {$this->user} --dump-date=false --routines --default-character-set={$this->encoding} --no-create-info --no-data --extended-insert=false --triggers=false {$this->dbname} --skip-comments 2>&1", $output, $retVal);
        echo ($retVal == 0) ? "ok\n" : die("Error: " . print_r($output, 1));

        // склеить возвращенную строку с SQL-кодом
        $spData = implode("\n", $output);

        // нужно удалить строки типа /*!50017 DEFINER=`root`@`localhost`*/
        $spData = preg_replace('%/\*![\d]+\sDEFINER.*?\*/%si', '', $spData);
        file_put_contents("{$path}/procedures.sql", $spData);

        echo "\nCompleted...\n";
    }
}
