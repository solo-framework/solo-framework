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
	 * Имя представления по умолчанию
	 *
	 * @var string
	 */
	public static $defaultView = "index";

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

	private static $start = 0;

	/**
	 * Приватный коструктор для реализации Singleton
	 *
	 * @return void
	 */
	protected function __construct($baseDir)
	{
		$this->baseDir = $baseDir;
	}

	public static function getExecutionTime()
	{
		return microtime(true) - self::$start;
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
		self::$start = microtime(true);
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
			self::$instance->configurePaths();

			// загрузка конфигурации
			self::$instance->loadConfiguration($configFile);

			// Импортирование классов
			self::$instance->importClasses();

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
	 * В этом методе устанавливаются значения псевдонимов для
	 * различных каталогов
	 *
	 * Если структура каталогов, определенная по-умолчанию, не
	 * соответствует вашим требованиям, этот метод можно переопределить.
	 *
	 * @return void
	 */
	protected function configurePaths()
	{
		//
		// Структура каталогов по-умолчанию
		//  .. <- BASE_DIRECTORY
		//   +app
		//   +public
		//   +config
		//   +var
		//
		// если нужна другая - переопределите метод configurePaths()

		// Здесь определи самую главную константу -
		// путь, где находятся все файлы
		define("BASE_DIRECTORY", $this->baseDir);

		// путь к файлам ядра фреймворка
		$frameworkDirectory = dirname(__FILE__);

		// подключаем загрузчик классов
		require_once $frameworkDirectory . "/ClassLoader.php";

		// файл с репозиторием классов будет записываться в каталог /var
		ClassLoader::init($this->baseDir, $this->baseDir . DIRECTORY_SEPARATOR . "var/class.map");

		//
		// Установка псевдонимов путей. Псевдонимы используются для упрощения
		// указания путей. Использование: ClassLoader::import("@framework/SomeClass.php");
		// В этом случае директива @framework заменяется на полный путь к соответствующему каталогу
		//

		// базовый каталог, где находятся файлы приложения
		ClassLoader::setPathByAlias("base", $this->baseDir);

		// путь к файлам ядра фреймворка
		ClassLoader::setPathByAlias("framework", $frameworkDirectory);

		// путь к каталогу, где находятся файлы бизнес-логики (views, actions, etc.)
		ClassLoader::setPathByAlias("app", $this->baseDir . DIRECTORY_SEPARATOR . "app");

		// указывает на корневой каталог виртуального сервера Apache
		ClassLoader::setPathByAlias("public", $this->baseDir . DIRECTORY_SEPARATOR . "public");
	}



	/**
	 * Инициализация  всех необходимых объектов: логгера, контекста и пр.
	 *
	 * @return void
	 */
	protected function init()
	{
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
	protected function importClasses()
	{
		// импортируем каталоги с файлами фреймворка
		ClassLoader::import("@framework/*");
		ClassLoader::import("@framework/db/*");

		// импортируем все каталоги, которые были указаны в настройках
		$imports = Configurator::getArray("import:import");
		foreach ($imports as $item)
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
		$frameworkDir = ClassLoader::getPathByAlias("framework");
		require_once $frameworkDir . "/IConfiguratorParser.php";
		require_once $frameworkDir . "/Configurator.php";
		require_once $frameworkDir . "/IniConfiguratorParser.php";

		Configurator::init(new IniConfiguratorParser($configFile));
	}

	/**
	 * Обработка исключений, возникших при выполнении метода handleRequest
	 * В наследуемом классе можно переопределить обработку ошибок
	 *
	 * @param Exception $e Экземпляр исключения
	 * 
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
		// отправляем заголовки, запрещающие кэширование
		if (Configurator::get("application:nocache"))
			Request::sendNoCacheHeaders();
		
		echo $result;
	}


	/**
	 * Обработка HTTP-запроса.
	 *
	 * Запрос имеет вид index.php?view=viewname для отображения Представления
	 * и index.php?action=actionName для выполнения Действия
	 *
	 * @static
	 *
	 * @return void
	 */
	public static function handleRequest()
	{
		try
		{
			// В этом методе можно разместить код, который должен выполняться
			// при каждом запросе.
			self::$instance->onBeforehHandleRequest();

			// создание объекта обработчика запросов
			$controller = Controller::getInstance(self::$isDebug);

			// узнаем, какое действие запрашивается
			$actionName = Request::getVar("action");

			// или какое представление: если ничего не задано - показываем IndexView
			$viewName = Request::getVar("view", self::$defaultView);

			// обработка запроса
			// если было запрошено представление - получим HTML
			$html = $controller->execute($actionName, $viewName);

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
	 * Возвращает соединение к БД по его имени
	 *
	 * @param string $name Имя соединения (см. секцию database в конфигурации)
	 *
	 * @return IDBAdapter Соединение к БД
	 * */
	public static function getConnection($name)
	{
		if (!@key_exists($name, self::$connections))
		{
			$adapterName = Configurator::get($name . ":driver") . "Adapter";
			$adapter = new $adapterName();
			$adapter->setConfig(Configurator::getSection($name));
			self::$connections[$name] = $adapter;
		}

		return self::$connections[$name];
	}

	/**
	 * Закрывает все соединения с БД
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
