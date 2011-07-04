<?php
/**
 * Управление логикой миграций
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  MigrationManager.php 27.05.11 17:39 evkur
 * @link     nolink
 */

require_once 'Migration.php';
require_once 'MigrationManagerHelper.php';
require_once 'DirectoryHandler.php';

class MigrationManager
{
    public $dbHelper = null;

    function __construct($host, $user, $password, $dbname)
    {
        $this->dbHelper = new MigrationManagerHelper($host, $user, $password, $dbname);
    }

    function __destruct()
    {
        $this->dbHelper->__destruct();
    }

    public function init($migrStorage)
    {
        // создаем таблицу
        $this->dbHelper->createTable();

        // создаем нулевой каталог c дампом базы
        $this->dbHelper->createDump($migrStorage . "/0");

        // создаем запись в таблице
        if (!$this->getMigrationByNumber(0))
            $this->insertMigration(0, 'Init migration');

        // устанавливаем версию миграции
        self::setCurrentVersion($migrStorage, 0);

    }

    /**
     * Возвращает все миграции
     * 
     * @param string $order Порядок сортировки
     *
     * @return array of @see Migration
     */
    public function getAllMigrations($order = "ASC")
    {
        $migrations = array();

        $sql = "SELECT * FROM __migration ORDER BY number {$order}";
        $res = $this->dbHelper->executeQuery($sql);
        while ($m = mysql_fetch_object($res, "Migration"))
		{
			$migrations[]  = $m;
		}

        return $migrations;
    }

    /**
     * Возвращает последнюю миграцию
     *
     * @return Migration
     */
    public function getLastMigration()
    {
        $sql = "SELECT * FROM __migration ORDER BY createTime DESC LIMIT 1";
        $res = $this->dbHelper->executeQuery($sql);
        $m = mysql_fetch_object($res);

        return $m;
    }

    /**
     * Возвращает миграцию по номеру
     *
     * @param  $num Номер миграции
     *
     * @return an|object
     */
    public function getMigrationByNumber($num)
    {
        $sql = "SELECT * FROM __migration WHERE number = {$num}";
        $res = $this->dbHelper->executeQuery($sql);
        $m = mysql_fetch_object($res);

        return $m;
    }

    /**
     * Создает файл delta.sql в указанной директории
     *
     * @throws Exception
     * @param  $migrPath Имя директории, где создать миграцию
     *
     * @return void
     */
    public static function createMigration($migrPath)
    {
        if (!@mkdir($migrPath, 0777, true))
            throw new Exception("Can't create {$migrPath}");

        MigrationManagerHelper::createEmptyDelta($migrPath);
    }

    /**
     * Добавляет миграцию в хранилище
     *
     * @throws Exception
     * @param  $migrPath Директория, где хранится миграция для добаваления
     * @param  $migrStorage Директория, где хранятся миграции
     * @param string $comment Комментарий к миграции
     *
     * @return void
     */
    public function commitMigration($migrPath, $migrStorage, $comment = '')
    {
        $lastMigration = $this->getLastMigration();
        if (!$lastMigration)
            throw new Exception("Need init migration");

        $nextMigrationNum = $lastMigration->number + 1;

        $path = "{$migrStorage}/{$nextMigrationNum}";

        $this->dbHelper->checkDir($migrStorage, $path);
        $this->dbHelper->checkFile("{$migrPath}/delta.sql");

        //dump
        $this->dbHelper->createDump($path);

        //copy delta
        if (!copy("{$migrPath}/delta.sql", "{$path}/delta.sql"))
            throw new Exception("Can't copy {$migrPath}/delta.sql to {$path}/delta.sql");

        DirectoryHandler::delete($migrPath);

        sleep(1);

        $this->insertMigration($nextMigrationNum, $comment);
        self::setCurrentVersion($migrStorage, $nextMigrationNum);
    }

    /**
    * Удаляет миграцию
    *
    * @throws Exception
    * @param  $migrNum Номер миграции
    * @param  $migrStorage Директория, где хранятся миграции
    *
    * @return void
    */
    public function deleteMigration($migrNum, $migrStorage)
    {
        $this->checkMigrationNum($migrNum);
        
        $sql = "DELETE FROM __migration WHERE number = {$migrNum}";
        $this->dbHelper->executeQuery($sql);

        MigrationManagerHelper::cleanMigrDir("{$migrStorage}/{$migrNum}");
    }

    public function insertMigration($num, $comment)
    {
        $sql = "INSERT INTO __migration (number, createTime, comment)
            VALUES ({$num}, unix_timestamp(NOW()), '{$comment}')";

        $this->dbHelper->executeQuery($sql);
    }

    /**
     * Накатывает миграции от 0 до $number из хранилища,
     * если установлен force, то накатывает только $number
     *
     * @param  $migrStorage Директория, где хранятся миграции
     * @param  $number Номер миграции
     * @param bool $force Флаг
     *
     * @return void
     */
    public function gotoMigration($migrStorage, $number, $force = false)
    {
        $migrations = $this->getAllMigrations();

        $this->checkMigrations($migrations);
        $this->checkStorage($migrStorage, $migrations);
        $this->checkMigrationNum($number,$migrations);

        $this->dbHelper->makeDBEmpty();

        if ($force)
            $this->dbHelper->importFiles("{$migrStorage}/{$number}",
                array('scheme.sql', 'data.sql', 'procedures.sql', 'triggers.sql'));
        else
        {
            for ($i = 0; $i <= $number; $i++)
            {
                if ($migrations[$i]->number == 0)
                    $this->dbHelper->importFiles("{$migrStorage}/{$migrations[$i]->number}",
                        array('scheme.sql', 'data.sql', 'procedures.sql', 'triggers.sql'));
                else
                    $this->dbHelper->importFiles("{$migrStorage}/{$migrations[$i]->number}", array('delta.sql'));
            }
        }
        $this->restoreMigrations($migrations);
        self::setCurrentVersion($migrStorage, $number);

    }

    /**
     * Восстанавливает состояние всех миграций
     *
     * @param  $migrations
     *
     * @return void
     */
    public function restoreMigrations($migrations)
    {
        $this->dbHelper->dropTable();
        $this->dbHelper->createTable();
        
        /* @var $m Migration */
        foreach ($migrations as $m)
        {
            $sql = "INSERT INTO __migration (id, number, createTime, comment)
                VALUES ({$m->id}, {$m->number}, {$m->createTime}, '{$m->comment}');";
            $this->dbHelper->executeQuery($sql);
        }
    }


    /**
     * Накатывает все миграции до последней существующей
     *
     * @param  $migrStorage Директория, где хранятся миграции
     * @throws Exception
     *
     * @return void
     */
    public function gotoLastMigration($migrStorage)
    {
         $lastMigration = $this->getLastMigration();
         if (is_null($lastMigration))
             throw new Exception("Can't found migrations");

         $this->gotoMigration($migrStorage, $lastMigration->number);
    }

    /**
     * Проверяет валидность Миграций в базе
     *
     * @throws Exception
     * @param  $migrations массив сущностей миграций
     *
     * @return void
     */
    public function checkMigrations($migrations)
    {
        if (is_null($migrations) || empty($migrations))
            throw new Exception("Can't found migrations");

        if ($migrations[0]->number != 0)
            throw new Exception("Can't found initial migration (with 0 number)");
    }

    /**
     * Проверяет соответствие записей таблицы миграций и директорий в хранилище миграций
     *
     * @param  $migrStorage Путь, где хранятся миграции
     * @param  $migrations Массив объектов миграций
     *
     * @return bool
     */
    public function checkStorage($migrStorage, $migrations)
    {
        //TODO: not implemented
        return true;
    }

    /**
     * Проверяет номер миграции
     *
     * @throws Exception
     * @param  $number
     *
     * @return void
     */
    public function checkMigrationNum($number)
    {
        if (!$this->getMigrationByNumber($number))
            throw new Exception("Migration #{$number} not found");
    }

    /**
     * Тестирует работоспособность миграций,
     * пока не используется
     *
     * @param  $migrStorage Путь, где хранятся миграции
     *
     * @return void
     */
    public function test($migrStorage)
    {
        $migrations = $this->getAllMigrations();

        $this->checkMigrations($migrations);
        $this->checkStorage($migrStorage, $migrations);
    }

    public static function getCurrentVersion($migrStorage)
    {
        $path = $migrStorage . DIRECTORY_SEPARATOR . 'migration.xml';
        if (!file_exists($path))
            throw new Exception("File {$path} not found");

        $xml = new DomDocument('1.0','utf-8');
        $xml->load($path);
        return $xml->getElementsByTagName('version')->item(0)->nodeValue;
    }

    public static function setCurrentVersion($migrStorage, $version = 0)
    {
        $path = $migrStorage . DIRECTORY_SEPARATOR . 'migration.xml';
        $xml = new DomDocument('1.0','utf-8');

        if (!file_exists($path))
            $version = 0;

        $xml->loadXML("<version>{$version}</version>");
        $xml->save($path);
    }


    public function getDeltaByBinLog($binaryLogPath, $migrStorage, $unique = false)
    {
        $currMigration = $this->getMigrationByNumber($this->getCurrentVersion($migrStorage));

        if (!$currMigration)
            throw new Exception("Incorrect current migration");

        $r = $this->dbHelper->executeQuery("SELECT NOW()");
        $endTime = mysql_result($r, 0);

        $res = $this->dbHelper->executeQuery("SELECT FROM_UNIXTIME({$currMigration->createTime})");
        $startTime = mysql_result($res, 0);

        echo "# Delta from {$startTime} to {$endTime}";
        if ($unique)
            echo " (Unique)";

        $queries = $this->dbHelper->getDeltaByBinLog($binaryLogPath, $startTime, $unique);

        echo "\n\n";
        
        return $queries . "\n";
    }
}
