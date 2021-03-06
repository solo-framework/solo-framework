<?php
/**
 * Базовый класс для всех приложений.
 *
 * PHP version 5
 *
 * @package  Core
 * @author   Andrey Filippov <afi.work@gmail.com>
 */

namespace Solo\Core;

use Solo\Core\Handler\Handler;
use Solo\Logger\Logger;

abstract class BaseApplication
{
	/**
	 * Консольный режим работы приложения.
	 * Сессия не стартует, не отправляются HTTP-заголовки
	 *
	 * @var bool
	 */
	public static $isConsoleApp = false;
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
	public $defaultView = "index";

	/**
	 * Скрипт-обработчик запросов
	 *
	 * @var string
	 */
	public $entryScript = "index.php";

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
	 * Коллекция компонентов, доступны по имени
	 *
	 * @var array
	 */
	protected $components = array();

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
	 * @var callable
	 */
	protected $prevErrorHandler = null;

	/**
	 * Правила роутинга
	 *
	 * @var Route
	 */
	protected $route = null;

	/**
	 * Список объектов IHandler
	 *
	 * @var Handler[]
	 */
	protected $handlers = array();


	/**
	 * Приватный коструктор для реализации Singleton
	 *
	 * @param string $baseDir Каталог, в котором находятся файлы приложения
	 *
	 * @return \solo\core\BaseApplication
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
	 * @throws \ErrorException
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

		throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
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
	 * @throws \RuntimeException
	 * @return void
	 */
	public function __clone()
	{
		throw new \RuntimeException("Can't clone singleton object");
	}

	/**
	 * Создает экземпляр приложения
	 *
	 * @static
	 *
	 * @param string $baseDir Базовый каталог, в котором находятся все файлы приложения
	 * @param string $configFile Путь к файлу с конфигурацией
	 *
	 * @return Application
	 */
	public static function createApplication($baseDir, $configFile)
	{
		self::$start = microtime(true);
		$baseDir = realpath($baseDir);

		// Здесь определим самую главную константу -
		// путь к базовому каталогу
		define("BASE_DIRECTORY", $baseDir);

		$className = get_called_class();

		if (self::$instance == null)
		{
			self::$instance = new $className($baseDir);

			// установим обработчик ошибок, генерируемых интерпретатором PHP
			// все ошибки преобразуются в исключения
			self::$instance->prevErrorHandler = self::$instance->setErrorExceptionHandler();

			// загрузка конфигурации
			self::$instance->loadConfiguration($configFile);

			// режим работы приложения
			self::$isDebug = Configurator::get("application:debug");

			// Инициализация логгера
//			Logger::init(Configurator::getSection("logger"));

			ComponentRegistry::getInstance()->getComponent("logger");
		}

		return self::$instance;
	}

	/**
	 * Возвращает экземпляр приложения
	 *
	 * @static
	 *
	 * @return BaseApplication
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
	 * Инициализация  всех необходимых объектов:контекста и пр.
	 *
	 * @return void
	 */
	protected function init()
	{
		$handlers = Configurator::getArray("application:handlers");

		foreach ($handlers as $class => $params)
		{
			$inst = new $class();
			$inst->init($params);
			if (!$inst->isEnabled)
				continue;
			$inst->onBegin();
			$this->handlers[] = $inst;
		}

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
		// загрузка конфигурационного файла
		Configurator::init(new PHPConfiguratorParser($configFile));

		// загрузка файла с правилами маршрутизации
		$this->route = require_once Configurator::get("application:routing");
	}

	/**
	 * Обработка исключений, возникших при выполнении метода handleRequest
	 * В наследуемом классе можно переопределить обработку ошибок
	 *
	 * @param \Exception $e Экземпляр исключения
	 *
	 * @throws \Exception
	 * @return void
	 */
	public function handleException(\Exception $e)
	{
		if (self::$isConsoleApp)
		{
			//Logger::error($e);
			Logger::get("core")->error("Catch exception in console", $e);
			echo $e->getMessage();
			exit();
		}

		if (self::$isDebug)
		{
			header("HTTP/1.1 500 Internal Server Error");

			$ev = new ErrorViz($e);
			$ev->show();
			Logger::get("core")->error("Catch exception in debug application mode", $e);
			exit();
		}
		else
		{
			$controller = ComponentRegistry::getInstance()->getComponent("controller");
			if ($e instanceof HTTP404Exception)
			{
				Response::addHeader("HTTP/1.1 404 Not Found");

				$this->display(
						$controller->renderView(
								Configurator::get("application:error404class"), $e
						)
				);
				exit();
			}
			if ($e instanceof \Exception)
			{
				$this->display(
						$controller->renderView(
								Configurator::get("application:errorClass"), $e
						)
				);

				Logger::get("core")->error("Catch exception", $e);
				//Logger::error($e);
				exit();
			}
		}
	}

	/**
	 * Отправка заголовков и вывод в браузер.
	 * В наследуемом классе можно переопределить поведение
	 *
	 * @param string $result Строка, выводимая в браузер
	 *
	 * @return void
	 */
	protected function display($result)
	{
		Response::sendHeaders();
		echo $result;
	}


	/**
	 * Обработка HTTP-запроса.
	 *
	 * @return void
	 */
	public function handleRequest()
	{
		try
		{
			// запуск всех обработчиков
			self::$instance->init();

			// В этом методе можно разместить код, который должен выполняться
			// при каждом запросе.
			self::$instance->onBeginHandleRequest();

			// создание объекта обработчика запросов
			$controller = ComponentRegistry::getInstance()->getComponent("controller");

			// обработка запроса
			// если было запрошено представление - получим HTML
			$html = $controller->execute($_SERVER["REQUEST_URI"], $this->route);

			// завершаем работу обработчиков
			foreach ($this->handlers as $handler)
			{
				$html = $handler->onFinish($html);
			}

			// вывод в браузер
			self::$instance->display($html);

			// Завершение обработки запроса
			self::$instance->onEndHandleRequest();
		}
		catch (\Exception $e)
		{
			self::$instance->handleException($e);
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
	 * @param null $flashMessageId
	 *
	 * @return void
	 */
	public function redirect($url, $message = null, $flashMessageId = null)
	{
		if($message != null)
			Context::setFlashMessage($message, $flashMessageId);

		Response::redirect($url);
	}


	/**
	 * Редирект на предыдущую страницу (HTTP_REFERER)
	 * Если указан текст сообщения, то он помещается в Context
	 * для дальнейшего использования, например, при отображении ошибок.
	 *
	 * @param string|\Exception $message Текст сообщения
	 *
	 * @param string $flashMessageId
	 *
	 * @return void
	 */
	public function redirectBack($message = null, $flashMessageId = "error")
	{
		if($message != null)
			Context::setFlashMessage($message, $flashMessageId);

		Response::redirect(Request::prevUri());
	}
}
