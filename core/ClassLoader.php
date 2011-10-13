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

	private static $aliases = null;

	private static $isFileExist = false;

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
		self::$isFileExist = is_file($classMapFile);		
	}

	/**
	 * Возвращает путь по его псевдониму
	 *
	 * @static
	 * @param string $alias Псевдоним пути
	 *
	 * @return string
	 */
	public static function getPathByAlias($alias)
	{
		if (isset(self::$aliases[$alias]))
			return self::$aliases[$alias];
		else
			throw new Exception("Undefined alias '{$alias}'");
	}

	/**
	 * Устанавливает псевдоним для пути к каталогу
	 *
	 * @static
	 * @param string $alias Псевдоним пути
	 * @param string $path Путь к каталогу
	 *
	 * @return void
	 */
	public static function setPathByAlias($alias, $path)
	{
		self::$aliases[$alias] = $path;
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
		if (self::$isFileExist && !self::$isLoaded)
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

		self::$isFileExist = true;
	}


	/**
	 * Распознает псевдонимы в пути и заменяет их на
	 * реальные пути
	 *
	 * @static
	 * @param string $path Путь к файлу или каталогу
	 *
	 * @return null|string
	 */
	private static function detectAlias($path)
	{
		$matches = null;
		if (preg_match('%@([\w]+)[/]?%', $path, $matches))
		{
			$path = str_replace("@" . $matches[1], "", $path);
			$pathByAlias = self::getPathByAlias($matches[1]);

			if (!is_file($pathByAlias . $path) && !is_dir($pathByAlias . $path))
				throw new Exception("ClassLoader: path '{$pathByAlias}{$path}' does not exists.");

			return realpath($pathByAlias . $path);
		}
		else
			return null;
	}

	/**
	 * Импортирует каталог или отдельный класс.
	 * Импортируются все файлы с расширением .php
	 *
	 * @param string|array $path Путь к импортируемому файлу или каталогу
	 * @param string Имя класса, который находится в файле
	 *
	 * @return void
	 */
	public static function import($path, $className = null)
	{
		$needWrite = false;

		// считаем инф-ю из репозитория
		self::readClassMap();

		// проверим, это каталог или файл
		// если каталог, то в конце строки должен стоять знак "*"
		$isFile = substr($path, -1) !== "*";

		// Это каталог. Проверяем, был ли он уже импортирован
		if (!$isFile)
		{
			// убираем * и слэши
			$path = str_replace("*", "", $path);
			$path = trim($path, '/\\');

			// не импортирован
			if (!in_array($path, self::$importedDirs))
			{
				self::$importedDirs[] = $path;
				$res = self::detectAlias($path);
				if (null !== $res)
					$path = $res;
				else
					$path = self::$baseDirectory . DIRECTORY_SEPARATOR .$path;

				if (!is_dir($path))
					throw new Exception("ClassLoader: can't find directory '{$path}'");

				// сканируем каталог и добавляем все файлы в репозиторий
				$di = new DirectoryIterator($path);
				while ($di->valid())
				{
					self::addToClassMap($di->getPathName());
					$di->next();
				}
				$needWrite = true;
			}
		}
		else
		{
			$res = self::detectAlias($path);
			if (null !== $res)
				$needWrite = self::addToClassMap($res, $className);
			else
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
		if (!is_file($path) && !is_dir($path))
			throw new Exception("ClassLoader: can't import file {$path}. File does not exists.");

		if (!is_file($path))
			return false;

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
			if ($path !== self::$classMap[$fileName])
				throw new Exception("ClassLoader: class '{$fileName}' (from {$path}) already imported in " . self::$classMap[$fileName]);
			else
				return false;
		}
	}


	/**
	 * Очищает репозиторий классов и удаляет файл,
	 * в который записывается репозиторий
	 *
	 * @return void
	 */
	public static function reset()
	{
		self::$classMap = array();
		self::$importedDirs = array();

		if (is_file(self::$classMapFile))
			unlink(self::$classMapFile);
	}

	/**
	 *
	 *
	 * @return
	 */
	public static function getImportedDirs()
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
		$class = strtolower($class);
		if (array_key_exists($class, self::$classMap))
			require_once self::$classMap[strtolower($class)];
		else
			throw new Exception("ClassLoader: Class '{$class}' does not exists in repository");
		//require_once $file;
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