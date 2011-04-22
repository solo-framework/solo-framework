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
					then 5.3.x you need to set name of inheritable class as getInstance() function parameter.");
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
			self::$instance->registerAutoloader();

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

	public function onBeforeRun()
	{
		
	}

	public function onAfterRun()
	{
		self::closeConnections();
		exit();
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
	public function configurePathConstants()
	{
		// базовый каталог, где находятся файлы приложения
		define("BASE_DIR", $this->baseDir);

		// путь к файлам ядра фреймворка
		defined("FRAMEWORK_CORE_DIR") or define("FRAMEWORK_CORE_DIR", dirname(__FILE__));

		// путь к каталогу, где находятся файлы бизнес-логики (views, actions, etc.)
		defined("APPLICATION_DIR") or define("APPLICATION_DIR", BASE_DIR . DIRECTORY_SEPARATOR . "app" );

		// указывает на корневой каталог виртуального сервера Apache
		defined("DOCUMENT_ROOT_DIR") or define("DOCUMENT_ROOT_DIR", BASE_DIR . DIRECTORY_SEPARATOR . "public");
	}

	/**
	 * Инициализация  всех необходимых объектов: логгера, контекста и пр.
	 *
	 * @return void
	 */
	public function init()
	{
		require_once FRAMEWORK_CORE_DIR . "/Session.php";
		require_once FRAMEWORK_CORE_DIR . "/Logger.php";
		require_once FRAMEWORK_CORE_DIR . "/Context.php";
		require_once FRAMEWORK_CORE_DIR . "/Binder.php";


		// Инициализация логгера
		Logger::init(Configurator::getSection("logger"));

		// Старт контекста приложения (сессии)
		Context::start(Configurator::get("application:name"));

		self::$isDebug = Configurator::get("application:debug");
	}

	/**
	 * Устанавливаем загрузчик классов.
	 * Если поведение метода не соответствует вашим требованиям, метод
	 * может быть переопределен в наследуемом классе
	 *
	 * @return void
	 */
	public function registerAutoloader()
	{
		// подключаем загрузчик классов
		require_once FRAMEWORK_CORE_DIR . "/DefaultClassLoader.php";
		DefaultClassLoader::init(Configurator::get("framework:file.repository"));
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
	public function loadConfiguration($configFile)
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
	 * Обработка исключений, возникших при выполнении метода run
	 *
	 * @param Exception $e
	 * @return void
	 */
	public function handleException(Exception $e)
	{
		throw $e;
	}


	/**
	 * Запуск приложения
	 *
	 * @static
	 * 
	 * @return void
	 */
	public static function run()
	{
		try
		{
			self::$instance->onBeforeRun();

			// создание объекта обработчика запросов
			$binder = Binder::getInstance(self::$isDebug);

			// узнаем, какое действие запрашивается
			$actionName = Request::getVar("action");

			// или какое представление: если ничего не задано - показываем IndexView
			$viewName = Request::getVar("view", "index");

			 // вывод в браузер
			echo $binder->execute($actionName, $viewName);

			self::$instance->onAfterRun();
		}
		catch (Exception $e)
		{
			self::getInstance()->handleException($e);
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
