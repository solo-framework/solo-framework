<?php
/**
 * Базовый класс для всех приложений.
 *
 * PHP version 5
 *
 * @package  Core
 * @author   Andrey Filippov <afi@i-loto.ru>
 */

class Application
{
	/**
	 * Режим работы приложения
	 *
	 * @var bool
	 */
	public static $isDebug = false;

	/**
	 * Экземпляр приложения
	 *
	 * @var Application
	 */
	private static $instance = null;

	/**
	 * Базовый каталог, в котором находятся все файлы приложения
	 *
	 * @var string
	 */
	private $baseDir = ".";
	
	private static $aliases = null;
								

	/**
	 * Коллекция соединений к БД
	 *
	 * @var array
	 */
	private static $connections = array();

	/**
	 * Приватный коструктор для реализации Singleton
	 *
	 * @return void
	 */
	protected function __construct($baseDir)
	{
		$this->baseDir = $baseDir;
	}

	/**
	 * Клонировать тоже запретим
	 *
	 * @throws RuntimeException
	 * @return void
	 */
	public function __clone()
	{
		throw new RuntimeException("Can't clone singleton object");
	}

	/**
	 * Создает экземпляр приложения
	 *
	 * @static
	 *
	 * @param string $baseDir Базовый каталог, в котором находятся все файлы приложения
	 * @param string $configFile Путь к файлу с конфигурацией
	 * @param string $className Имя класса, наследуемого от Application (только для PHP < 5.3.x)
	 *
	 * @throws RuntimeException
	 *
	 * @return Application|null
	 */
	public static function createApplication($baseDir, $configFile, $className = null)
	{
		$baseDir = realpath($baseDir);

		// хак для возможности наследования от singleton
		// в разных версиях PHP
		if (version_compare(phpversion(), "5.3.0", ">="))
		{
			$className = get_called_class();
		}
		else
		{
			if ($className == null)
				throw new RuntimeException("If you have installed PHP version less
					then 5.3.x you need to set name of inheritable class as createApplication() function parameter.");
		}

		if (self::$instance == null)
		{
			if ($className !== null)
				self::$instance = new $className($baseDir);
			else
				self::$instance = new self($baseDir);

			// настроим пути
			self::$instance->configurePathConstants();

			// загрузка конфигурации
			self::$instance->loadConfiguration($configFile);

			// Установка автозагрузчика классов
			self::$instance->registerClassLoader();

			// Инициализация  всех необходимых объектов: логгера, контекста и пр.
			self::$instance->init();
		}
		return self::$instance;
	}

	/**
	 * Возвращает экземпляр приложения
	 *
	 * @static
	 *
	 * @return Application
	 */
	public static function getInstance()
	{
		return self::$instance;
	}

	/**
	 * Выполняется перед обработкой HTTP-запроса
	 * В этом методе можно разместить код, который должен выполняться
	 * при каждом запросе.
	 *
	 * @return void
	 */
	protected function onBeforehHandleRequest()
	{

	}

	/**
	 * Этот метод вызывается самым последним.
	 * В нем можно разместить код, освобождающий ресурсы
	 *
	 * @return void
	 */
	protected function onAfterHandleRequest()
	{
		self::closeConnections();
	}

	/**
	 * В этом методе устанавливаются значения констант, которые
	 * содержат пути к различным каталогам.
	 *
	 * Если структура каталогов, определенная по-умолчанию, не
	 * соответствует вашим требованиям, этот метод можно переопределить.
	 *
	 * @return void
	 */
	protected function configurePathConstants()
	{
		// базовый каталог, где находятся файлы приложения
		define("BASE_DIR", $this->baseDir);

		// путь к файлам ядра фреймворка
		defined("FRAMEWORK_CORE_DIR") or define("FRAMEWORK_CORE_DIR", dirname(__FILE__));

		// путь к каталогу, где находятся файлы бизнес-логики (views, actions, etc.)
		defined("APPLICATION_DIR") or define("APPLICATION_DIR", BASE_DIR . DIRECTORY_SEPARATOR . "app" );

		// указывает на корневой каталог виртуального сервера Apache
		defined("DOCUMENT_ROOT_DIR") or define("DOCUMENT_ROOT_DIR", BASE_DIR . DIRECTORY_SEPARATOR . "public");
		
		// псевдонимы путей
		
		// базовый каталог, где находятся файлы приложения
		self::$aliases["base"] = $this->baseDir;

		// путь к файлам ядра фреймворка 
		self::$aliases["framework"] = dirname(__FILE__);

		// путь к каталогу, где находятся файлы бизнес-логики (views, actions, etc.)
		self::$aliases["app"] =  $this->baseDir . DIRECTORY_SEPARATOR . "app";

		// указывает на корневой каталог виртуального сервера Apache
		self::$aliases["web"] = $this->baseDir . DIRECTORY_SEPARATOR . "public";

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
			throw new Exception("Undefined alias {$alias}");
	}

	/**
	 * Устанавливает псевдоним для пути к каталогу
	 *
	 * @static
	 * @param string $path Путь к каталогу
	 * @param string $alias Псевдоним пути
	 *
	 * @return void
	 */
	public static function setPathByAlias($path, $alias)
	{
		self::$aliases[$alias] = $path;
	}

	/**
	 * Инициализация  всех необходимых объектов: логгера, контекста и пр.
	 *
	 * @return void
	 */
	protected function init()
	{
		$frameworkDir = self::getPathByAlias("framework");
		require_once $frameworkDir . "/Request.php";
		require_once $frameworkDir . "/Session.php";
		require_once $frameworkDir . "/Logger.php";
		require_once $frameworkDir . "/Context.php";
		require_once $frameworkDir . "/Binder.php";


		// Инициализация логгера
		Logger::init(Configurator::getSection("logger"));

		// Старт контекста приложения (сессии)
		Context::start(Configurator::get("application:name"));

		// режим работы приложения
		self::$isDebug = Configurator::get("application:debug");
	}

	/**
	 * Устанавливаем загрузчик классов.
	 * Если поведение метода не соответствует вашим требованиям, метод
	 * может быть переопределен в наследуемом классе
	 *
	 * @return void
	 */
	protected function registerClassLoader()
	{
		// подключаем загрузчик классов
		//require_once FRAMEWORK_CORE_DIR . "/DefaultClassLoader.php";
		//DefaultClassLoader::init(Configurator::get('framework:file.repository'));
		require_once FRAMEWORK_CORE_DIR . "/ClassLoader.php";
		//ClassLoader::init(Configurator::get("application:import"));
		
		//ClassLoader::init("var/class.map");
		//ClassLoader::import("framework/core/*");
		//ClassLoader::import("app/views/*");
		ClassLoader::init(Configurator::get("import:classMapFile"));
		$impotrs = Configurator::getArray("import:import");
		
		foreach ($impotrs as $item)
			ClassLoader::import($item);
	}

	/**
	 * Загрузка файла с конфигурацией
	 *
	 * Если поведение метода не соответствует вашим требованиям, метод
	 * может быть переопределен в наследуемом классе
	 *
	 * @param string $configFile Путь к файлу с конфигурацией
	 * @return void
	 */
	protected function loadConfiguration($configFile)
	{
		// подключение конфигуратора
		// по умолчанию будем использовать INI конфигурацию
		// по желанию, можно реализовать свой класс
		require_once FRAMEWORK_CORE_DIR . "/IConfiguratorParser.php";
		require_once FRAMEWORK_CORE_DIR . "/Configurator.php";
		require_once FRAMEWORK_CORE_DIR . "/IniConfiguratorParser.php";

		Configurator::init(new IniConfiguratorParser($configFile));
	}

	/**
	 * Обработка исключений, возникших при выполнении метода handleRequest
	 *
	 * @param Exception $e
	 * @return void
	 */
	protected function handleException(Exception $e)
	{
		if (self::$isDebug)
		{
			throw $e;
		}
		else 
		{
			header("HTTP/1.1 404 Not Found");
			exit("404 Not Found");			
		}
	}

	/**
	 * Вывод в браузер.
	 * В наследуемом классе можно переопределить поведение
	 *
	 * @param string $result Строка, выводимая в браузер
	 *
	 * @return void
	 */
	protected function display($result)
	{
		echo $result;
	}


	/**
	 * Обработка HTTP-запроса
	 *
	 * @static
	 *
	 * @return void
	 */
	public static function handleRequest()
	{
		try
		{
			self::$instance->onBeforehHandleRequest();

			// создание объекта обработчика запросов
			$binder = Binder::getInstance(self::$isDebug);

			// узнаем, какое действие запрашивается
			$actionName = Request::getVar("action");

			// или какое представление: если ничего не задано - показываем IndexView
			$viewName = Request::getVar("view", "index");

			// обработка запроса
			// если было запрошено представление - получим HTML
			$html = $binder->execute($actionName, $viewName);

			// вывод в браузер
			self::$instance->display($html);

			// Завершение обработки запроса
			self::$instance->onAfterHandleRequest();
		}
		catch (Exception $e)
		{
			self::$instance->handleException($e);
		}
	}

	/**
	 * Возвращает соединение по его имени
	 *
	 * @param string $name Имя соединения (см. секцию database в конфигурации)
	 *
	 * @return resource Соединение к БД
	 * */
	public static function getConnection($name)
	{
		if (!@key_exists($name, self::$connections))
		{
			$adapter = DBFactory::factory(Configurator::get($name . ":driver"));
			$adapter->setConfig(Configurator::getSection($name));
			self::$connections[$name] = $adapter;
		}

		return self::$connections[$name];
	}

	/**
	 * Disconnect from MySQL adapters
	 *
	 * @return void
	 */
	public static function closeConnections()
	{
		foreach (self::$connections as $name => $adapter)
		{
			$adapter->close();
		}
	}
}

?>
