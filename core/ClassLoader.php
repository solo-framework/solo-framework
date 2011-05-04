<?php
/**
 * Класс предоставляющий методы для автоматического подключения
 * файлов с PHP классами
 *
 * PHP version 5
 *
 * @package  Core
 * @author   Andrey Filippov <afi@i-loto.ru>
 */

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
	 * Список импортированных каталогов
	 *
	 * @var array
	 */
	private static $importedDirs = array();

	/**
	 * Список импортированных классов
	 *
	 * @var array
	 */
	private static $classMap = array();

	/**
	 * Имя метода выполняющего загрузку классов
	 *
	 * @var string
	 */
	private static $method = null;

	/**
	 * Базовый каталог, относительно которого
	 * указываются все остальные пути
	 *
	 * @var string
	 */
	private static $baseDirectory = null;


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
	 * @param string $baseDirectory Базовый каталог для файлов
	 * @param string $classMapFile Имя файла, в котором содержится информация
	 * 				о местонахождении файлов с классами
	 * @param string $method Имя метода, выполняющего загрузку классов
	 *
	 * @return void
	 */
	public static function init($baseDirectory, $classMapFile, $method = "ClassLoader::autoload")
	{
		self::$method = $method;
		self::$baseDirectory = $baseDirectory;
		spl_autoload_register($method);
		self::$classMapFile = $classMapFile;
	}

	/**
	 * Считывает информацию из репозитория классов,
	 * если этот файл существует и еще не был загружен
	 *
	 * @static
	 *
	 * @return void
	 */
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

	/**
	 * Записывает в файл репозиторий классов
	 *
	 * @static
	 * @throws Exception
	 *
	 * @return void
	 */
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
		self::readClassMap();

		// проверим, это каталог или файл
		// если каталог, то в конце строки должен стоять знак "*"
		$isFile = substr($path, -1) !== "*";

		// Это каталог, проверяем, был ли он уже импортирован
		if (!$isFile)
		{
			// убираем * и слэши
			$path = str_replace("*", "", $path);
			$path = trim($path, '/\\');
			$path = self::$baseDirectory . DIRECTORY_SEPARATOR .$path;

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
			$needWrite = self::addToClassMap(self::$baseDirectory . DIRECTORY_SEPARATOR . $path, $className);
		}

		// запишем изменения в файл
		if ($needWrite)
			self::writeClassMap();
	}

	/**
	 * Добавляет в репозиторий классов путь к файлу, где он определен и имя класса.
	 * Имя класса по-умолчанию идентично имени файла, если это условие не соблюдено, то
	 * можно указать имя класса, содержащегося в этом файле вторым параметром
	 *
	 * @static
	 * @throws Exception
	 * @param string $path путь к файлу, где он определен класс
	 * @param null|string $className Имя класса, содержащегося в файле
	 *
	 * @return bool
	 */
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
		$file = @self::$classMap[strtolower($class)];
		if ($file === null)
			throw new Exception("ClassLoader: Class '{$class}' does not exists in repository");
		require_once $file;
	}

	/**
	 * Регистрирует метода в качестве автоматического загрузчика классов
	 *
	 * @static
	 * @param string $callback Имя метода, регистрируемого
	 *                         в качестве автоматического загрузчика классов
	 *
	 * @return void
	 */
	public static function registerAutoloader($callback)
	{
		spl_autoload_unregister(self::$method);
		spl_autoload_register($callback);
		spl_autoload_register(self::$method);
	}	
}

?>