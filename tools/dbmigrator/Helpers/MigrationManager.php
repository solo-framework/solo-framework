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

		// проверяем существование начальной миграции
		if ($this->getMigrationById(1))
			throw new Exception("Can't apply init migration, because another exists");

	    // строим миграцию
		$uid = $this->buildMigration($migrStorage, 'Init migration');

		// устанавливаем версию миграции
		self::setCurrentVersion($migrStorage, $uid);

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
        if (!$this->getMigrationById(1))
            throw new Exception("Need init migration");

		$this->dbHelper->checkFile("{$migrPath}/delta.sql");

		$uid = $this->buildMigration($migrStorage, $comment);

	    $path = "{$migrStorage}/{$uid}";

        //copy delta
        if (!copy("{$migrPath}/delta.sql", "{$path}/delta.sql"))
            throw new Exception("Can't copy {$migrPath}/delta.sql to {$path}/delta.sql");

        self::putInsertMigrationSql($uid, $comment, $path);

        DirectoryHandler::delete($migrPath);

        self::setCurrentVersion($migrStorage, $uid);
    }

    /**
     * @static
     * @param $createTime
     * @param $comment
     * @param $path
     * @return void
     */
    public static function putInsertMigrationSql($createTime, $comment, $path)
    { 
        $sql = "\nINSERT INTO __migration (createTime, comment) VALUES ({$createTime}, '{$comment}');\n";
        $file = file_get_contents("{$path}/delta.sql");
        file_put_contents("{$path}/delta.sql", str_replace("/*MIGRATION_INSERT_STATEMENT*/", $sql, $file));
    }

    /**
	 * Строит миграцию
	 *
	 * @param $migrStorage
	 * @param $comment
	 * 
	 * @return mixed
	 */
	public function buildMigration($migrStorage, $comment)
	{
		$time = $this->getCurrentTime();
		$path = "{$migrStorage}/{$time}";

		$this->dbHelper->checkDir($migrStorage, $path);
		
		// создаем запись в таблице
		if (!$this->getMigrationByTime($time))
		{
			sleep(1);
			$this->insertMigration($time, $comment);
		}

		// создаем начальный каталог c дампом базы
		$this->dbHelper->createDump($path);

		return $time;
	}

	public function getCurrentTime()
	{
		return number_format(microtime(true), 4, '.', '');
	}

	/**
	 * Накатывает миграции от 0 до $number из хранилища,
	 * если установлен force, то накатывает только $number
	 *
	 * @param string $migrStorage Директория, где хранятся миграции
	 * @param string $uid Уникальный идентификатор миграции
	 * @param bool $force Флаг
	 *
	 *
	 * @internal param $uuid Номер миграции
	 * @return void
	 */
    public function gotoMigration($migrStorage, $uid, $force = false)
    {
        $migrations = $this->getAllMigrations();

        $this->checkMigrations($migrations);
        $this->checkMigration($uid, $migrations);

        $this->dbHelper->makeDBEmpty();

        if ($force)
        {
	        $this->dbHelper->importFiles("{$migrStorage}/{$uid}",
                array('scheme.sql', 'data.sql', 'procedures.sql', 'triggers.sql'));
        }
        else
        {
	        /**
	         * @var $m Migration
	         */
	        foreach ($migrations as $m)
	        {
				if ($m->id == 1)
				{
					$this->dbHelper->importFiles("{$migrStorage}/{$m->createTime}",
                        array('scheme.sql', 'data.sql', 'procedures.sql', 'triggers.sql'));
				}
                else
                {
	                $this->dbHelper->importFiles("{$migrStorage}/{$m->createTime}", array('delta.sql'));
                }

                if ($m->createTime == $uid)
                {
	                break;
                }
	        }
        }
        $this->restoreMigrations($migrations);
        self::setCurrentVersion($migrStorage, $uid);
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

        $sql = "SELECT * FROM __migration ORDER BY createTime {$order}";
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
	 * Возвращает миграцию по времени создания
	 *
	 * @param $time
	 *
	 * @return an|object
	 */
    public function getMigrationByTime($time)
    {
        $sql = "SELECT * FROM __migration WHERE createTime = {$time}";
        $res = $this->dbHelper->executeQuery($sql);
        $m = mysql_fetch_object($res);

        return $m;
    }

	/**
	 * Возвращает миграцию id
	 *
	 * @param $id
	 *
	 * @return an|object
	 */
	public function getMigrationById($id)
	{
		$sql = "SELECT * FROM __migration WHERE id = {$id}";
        $res = $this->dbHelper->executeQuery($sql);
        $m = mysql_fetch_object($res);

        return $m;
	}

   /**
	* Возврщает номер последней папки
	*
	* @param $migrStorage
	*
	* @return mixed
	*/
	private function getMigrationsByDirectories($migrStorage)
	{
		$pattern = "/^\d{10}\.\d{4}$/is";
		return DirectoryHandler::dirList($migrStorage, $pattern);
	}

    /**
     * Создает файл delta.sql в указанной директории
     *
     * @throws Exception
     * @param  $migrPath Имя директории, где создать миграцию
     *
     * @return void
     */
    public static function createTempMigration($migrPath)
    {
        if (!@mkdir($migrPath, 0777, true))
            throw new Exception("Can't create {$migrPath}");

        MigrationManagerHelper::createEmptyDelta($migrPath);
    }

    /**
    * Удаляет миграцию
    *
    * @throws Exception
    * @param  $migrUuid Номер миграции
    * @param  $migrStorage Директория, где хранятся миграции
    *
    * @return void
    */
    public function deleteMigration($migrUuid, $migrStorage)
    {
        $this->checkMigration($migrUuid);
        
        $sql = "DELETE FROM __migration WHERE uuid = '{$migrUuid}'";
        $this->dbHelper->executeQuery($sql);

        MigrationManagerHelper::cleanMigrDir("{$migrStorage}/{$migrUuid}");
    }

    public function insertMigration($createTime, $comment)
    {
        $sql = "INSERT INTO __migration (createTime, comment)
            VALUES ({$createTime}, '{$comment}')";

        $this->dbHelper->executeQuery($sql);
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
            $sql = "INSERT INTO __migration (id, createTime, comment)
                VALUES ({$m->id}, {$m->createTime}, '{$m->comment}');";
            $this->dbHelper->executeQuery($sql);
        }
    }

	public function gotoLastMigration($migrStorage)
	{
		 $migrationsUids = $this->getMigrationsByDirectories($migrStorage);
	     if (empty($migrationsUids))
	         throw new Exception("Can't found migrations");

	     $this->gotoMigrationWithoutHistory($migrStorage, $migrationsUids);
	}


	private function gotoMigrationWithoutHistory($migrStorage, $migrationsUids)
	{
		$this->dbHelper->makeDBEmpty();

        // apply init migration
		$_migrationsUids = $migrationsUids;
        $uid = array_shift($_migrationsUids);
        $this->dbHelper->importFiles("{$migrStorage}/{$uid}",
                array('scheme.sql', 'data.sql', 'procedures.sql', 'triggers.sql'));

        foreach ($_migrationsUids as $uid)
        {
            $this->dbHelper->importFiles("{$migrStorage}/{$uid}", array('delta.sql'));
        }

		self::setCurrentVersion($migrStorage, end($migrationsUids));
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

        if ($migrations[0]->id != 1)
            throw new Exception("Can't found initial migration (with id 1)");
    }

    /**
     * Проверяет номер миграции
     *
     * @throws Exception
     * @param  $uuid
     *
     * @return void
     */
    public function checkMigration($uuid)
    {
        if (!$this->getMigrationByTime($uuid))
            throw new Exception("Migration {$uuid} not found");
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

    public static function setCurrentVersion($migrStorage, $version)
    {
        $path = $migrStorage . DIRECTORY_SEPARATOR . 'migration.xml';
        $xml = new DomDocument('1.0','utf-8');

        $xml->loadXML("<version>{$version}</version>");
        $xml->save($path);
    }

    public function getDeltaByBinLog($binaryLogPath, $migrStorage, $unique = false)
    {
        $currMigration = $this->getMigrationByTime($this->getCurrentVersion($migrStorage));

        if (!$currMigration)
            throw new Exception("Incorrect current migration");

        $r = $this->dbHelper->executeQuery("SELECT NOW()");
        $endTime = mysql_result($r, 0);

        $res = $this->dbHelper->executeQuery("SELECT FROM_UNIXTIME({$currMigration->createTime})");
        $startTime = mysql_result($res, 0);

        $queries = $this->dbHelper->getDeltaByBinLog($binaryLogPath, $startTime, $unique);

        echo "# Delta from {$startTime} to {$endTime}";
        if ($unique)
            echo " (Unique)";

        echo "\n\n";
        
        return $queries . "\n";
    }

}
