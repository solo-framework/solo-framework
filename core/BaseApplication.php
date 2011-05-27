<?php
/**
 * Базовый класс для всех приложений.
 *
 * PHP version 5
 *
 * @package  Core
 * @author   Andrey Filippov <afi@i-loto.ru>
 */

abstract class BaseApplication
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
	 * Скрипт-обработчик запросов
	 *
	 * @var string
	 */
	public static $entryScript = "index.php";

	/**
	 * Экземпляр приложения
	 *
	 * @var Application
	 */
	protected static $instance = null;

	/**
	 * Базовый каталог, в котором находятся все файлы приложения
	 *
	 * @var string
	 */
	protected $baseDir = ".";

	/**
	 * Коллекция соединений к БД
	 *
	 * @var array
	 */
	private static $connections = array();

	/**
	 * Нулевая точка отсчета времени выполнения кода
	 *
	 * @var float
	 */
	protected static $start = 0;

	/**
	 * Уровень отлавливаемых ошибок PHP
	 * По-умолчанию 32767 => E_ALL | E_STRICT
	 *
	 * @var int
	 */
	public $errorLevel = 32767;

	/**
	 * Игнорировать ли другие подобные обработчики
	 * ошибок, которые могут быть определены в других библиотеках
	 *
	 * @var boolean
	 */
	protected $ignoreOther = false;

	/**
	 *
	 *
	 * @var unknown_type
	 */
	protected $prevErrorHandler = null;

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
	 * Метод-обработчик ошибок, генерируемых интерпретатором PHP
	 * все ошибки преобразуются в исключение ErrorException
	 *
	 * @param int $errno Номер ошибки
	 * @param string $errstr Сообщение об ошибке
	 * @param string $errfile Файл, в котором обнаружена ошибка
	 * @param string $errline Номер строки файла, в котором обнаружена ошибка
	 *
	 * @throws ErrorException
	 * @return boolean
	 */
	public function throwErrorException($errno, $errstr, $errfile, $errline)
	{
		if (!($errno & error_reporting()))
			return false;
		if (!($errno & $this->errorLevel))
		{
			if (!$this->ignoreOther)
			{
				if ($this->prevErrorHandler)
				{
					$args = func_get_args();
					call_user_func_array($this->prevErrorHandler, $args);
				}
				else
				{
					return false;
				}
			}
			return true;
		}

		throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
	}


	/**
	 * Выполняет код при уничтожении объекта приложения
	 *
	 * @return void
	 */
	public function __destruct()
	{
		restore_error_handler();
	}

	/**
	 * Возвращает разницу во времени с момента старта приложения
	 *
	 * string float
	 */
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
	 * @param string $className Имя класса, наследуемого от BaseApplication (только для PHP < 5.3.x)
	 *
	 * @throws RuntimeException
	 *
	 * @return Application
	 */
	public static function createApplication($baseDir, $configFile, $className = "Application")
	{
		self::$start = microtime(true);
		$baseDir = realpath($baseDir);

		// Здесь определи самую главную константу -
		// путь, где находятся все файлы
		define("BASE_DIRECTORY", $baseDir);

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

			// установим обработчик ошибок, генерируемых интерпретатором PHP
			// все ошибки преобразуются в исключения
			self::$instance->prevErrorHandler = self::$instance->setErrorExceptionHandler();

			// загрузка конфигурации
			self::$instance->loadConfiguration($configFile);

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
	protected function onBeginHandleRequest()
	{

	}

	/**
	 * Этот метод вызывается самым последним.
	 * В нем можно разместить код, освобождающий ресурсы
	 *
	 * @return void
	 */
	protected function onEndHandleRequest()
	{
		self::$instance->closeConnections();
	}

	/**
	 * Устанавливает метод для перехвата ошибок,
	 * генерируемых интерпретатором PHP
	 *
	 * Если поведение метода не соответствует вашим требованиям, метод
	 * может быть переопределен в наследуемом классе
	 *
	 * return mixed
	 */
	protected function setErrorExceptionHandler()
	{
		return set_error_handler(array(self::$instance, "throwErrorException"));
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
		Context::start(Configurator::get("application:sessionname"));

		// режим работы приложения
		self::$isDebug = Configurator::get("application:debug");
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
		// путь к файлам ядра фреймворка
		$frameworkDirectory = realpath(dirname(__FILE__) . "/..");

		// подключение конфигуратора
		require_once $frameworkDirectory . "/core/IConfiguratorParser.php";
		require_once $frameworkDirectory . "/core/Configurator.php";
		require_once $frameworkDirectory . "/core/PHPConfiguratorParser.php";
		Configurator::init(new PHPConfiguratorParser($configFile));

		// подключаем загрузчик классов
		require_once $frameworkDirectory . "/core/ClassLoader.php";
		ClassLoader::init($this->baseDir, $this->baseDir . DIRECTORY_SEPARATOR . "var/class.map");

		// Установка псевдонимов из конфигуратора
		$aliases = Configurator::get("import:alias");

		if ($aliases)
		{
			foreach ($aliases as $name => $value)
				ClassLoader::setPathByAlias($name, $value);
		}

		// импортируем все каталоги, которые были указаны в настройках
		$imports = Configurator::getArray("import:import");
		foreach ($imports as $item)
			ClassLoader::import($item);
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
	public function handleRequest()
	{
		try
		{
			// В этом методе можно разместить код, который должен выполняться
			// при каждом запросе.
			self::$instance->onBeginHandleRequest();

			// создание объекта обработчика запросов
			$controller = Controller::getInstance(self::$isDebug);

			// узнаем, какое действие запрашивается
			$actionName = Request::get("action");

			// или какое представление: если ничего не задано - показываем IndexView
			$viewName = Request::get("view", self::$defaultView);

			// обработка запроса
			// если было запрошено представление - получим HTML
			$html = $controller->execute($actionName, $viewName);

			// вывод в браузер
			self::$instance->display($html);

			// Завершение обработки запроса
			self::$instance->onEndHandleRequest();
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
	public function getConnection($name)
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
	public function closeConnections()
	{
		foreach (self::$connections as $name => $adapter)
		{
			$adapter->close();
		}
	}

	/**
	 * Редирект на указанный URL
	 * Если указан текст сообщения, то он помещается в Context
	 * для дальнейшего использования, например, при отображении ошибок.
	 *
	 * @param string $url URL
	 * @param string $message Текст сообщения
	 *
	 * @return void
	 */
	public function redirect($url, $message = null, $flashMessageId = null)
	{
		if($message != null)
			Context::setFlashMessage($message, $flashMessageId);

		Request::redirect($url);
	}


	/**
	 * Редирект на предыдущую страницу (HTTP_REFERER)
	 * Если указан текст сообщения, то он помещается в Context
	 * для дальнейшего использования, например, при отображении ошибок.
	 *
	 * @param string|Exception $message Текст сообщения
	 *
	 * @return void
	 */
	public function redirectBack($message = null, $flashMessageId = "error")
	{
		if($message != null)
			Context::setFlashMessage($message, $flashMessageId);

		Request::redirect(Request::prevUri());
	}

}

?>
