<?php

class ClassLoader
{
	/**
	 * Имя файла, в котором содержится информация
	 * 				о местонахождении файлов с классами
	 *
	 * @var string
	 */
	private static $classMapFile = null;

	/**
	 * Признак уже загруженного репозитория классов
	 *
	 * @var boolean
	 */
	private static $isLoaded = false;

	/**
	 * Репозиторий ипортированных каталогов
	 * и файлов
	 *
	 * @var mixed
	 */
	private static $repository = array(
							"importedDirs" => array(),
							"classMap" => array()
						);

	private static $importedDirs = array();

	private static $classMap = array();
	
	private static $mathod = null;
	

	/**
	 * Приватный конструктор
	 *
	 * @return void
	 */
	private function __construct()
	{

	}

	/**
	 * Регистрация метода для автозагрузки файлов
	 *
	 * @param string $classMapFile Имя файла, в котором содержится информация
	 * 				о местонахождении файлов с классами
	 * @param string $method Имя метода, выполняющего загрузку классов
	 *
	 * @return void
	 */
	public static function init($classMapFile, $method = "ClassLoader::autoload")
	{
		self::$mathod = $method;
		spl_autoload_register($method);
		self::$classMapFile = BASE_DIR . DIRECTORY_SEPARATOR . $classMapFile;
	}

	private static function readClassMap()
	{
		if (file_exists(self::$classMapFile) && !self::$isLoaded)
		{
			$file = file_get_contents(self::$classMapFile);
			$repository = unserialize($file);
			self::$classMap  = $repository["classMap"];
			self::$importedDirs = $repository["importedDirs"];
			self::$isLoaded = true;
		}
	}

	private static function writeClassMap()
	{
		$out["importedDirs"] = self::$importedDirs;
		$out["classMap"] = self::$classMap;

		$res = @file_put_contents(self::$classMapFile, serialize($out), LOCK_EX);
		if (!$res)
			throw new Exception("ClassLoader: can't write repository file to " . self::$classMapFile);
	}

	/**
	 * Импортирует каталог или отдельный класс.
	 * Импортируются все файлы с расширением .php
	 *
	 * @param string|array $path Путь к импортируемому файлу или каталогу (или список путей)
	 * 							 пример: файл - ClassLoader::import("path/to/file/FileName.php");
	 * 									 каталог - ClassLoader::import("path/to/file/*");
	 */
	public static function import($path, $className = null)
	{
		$needWrite = false;

		// считаем инф-ю из репозитория
		$repo = self::readClassMap();

		// проверим, это каталог или файл
		// если каталог, то в конце строки должен стоять знак "*"
		$isFile = substr($path, -1) !== "*";

		// Это каталог, проверяем, был ли он уже импортирован
		if (!$isFile)
		{
			// убираем * и слэши
			$path = str_replace("*", "", $path);
			$path = trim($path, '/\\');
			$path = BASE_DIR . DIRECTORY_SEPARATOR .$path;

			// не импортирован
			if (!in_array($path, self::$importedDirs))
			{
				$needWrite = true;
				self::$importedDirs[] = $path;

				// сканируем каталог и добавляем все файлы в репозиторий
				$di = new DirectoryIterator($path);
				while ($di->valid())
				{
					self::addToClassMap($di->getPathName());
					$di->next();
				}
			}
		}
		else
		{
			$needWrite = self::addToClassMap(BASE_DIR . DIRECTORY_SEPARATOR . $path, $className);
		}

		// запишем изменения в файл
		if ($needWrite)
			self::writeClassMap();
	}

	private static function addToClassMap($path, $className = null)
	{
		if (!file_exists($path))
			throw new Exception("ClassLoader: can't import file {$path}. File does not exists.");

		$pathinfo = pathinfo($path);

		if ($pathinfo["extension"] !== "php")
			return false;

		if ($className !== null)
			$fileName = strtolower($className);
		else
			$fileName = strtolower($pathinfo["filename"]);

		if (!isset(self::$classMap[$fileName]))
		{
			self::$classMap[$fileName] = $path;
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	 *
	 *
	 * @return
	 */
	public static function getImported()
	{
		return self::$importedDirs;
	}

	public static function getClassMap()
	{
		return self::$classMap;
	}


	/**
	 * Реализует автозагрузку файлов с классами
	 * Читает файл репозитория: формат файла - сериализованный массив вида
	 * array ("classname" => "path/to/classname.php" [, ...])
	 *
	 * @param string $class Имя класса
	 *
	 * @return void
	 */
	protected static function autoload($class)
	{
		// Игнорируем классы Smarty - у него свой загрузчик
		//if (strpos($class, "Smarty_") !== false)
		//	return true;

		$file = @self::$classMap[strtolower($class)];
		if ($file === null)
			throw new Exception("ClassLoader: Class '{$class}' does not exists in repository");
		require_once $file;
	}
	
	public static function registerAutoloader($callback)
	{
		spl_autoload_unregister(self::$mathod);
		spl_autoload_register($callback);
		spl_autoload_register(self::$mathod);
	}	
}

?>