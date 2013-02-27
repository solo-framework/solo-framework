<?php
/**
 * Класс для отрисовки представлений и выполнения действий
 * Связующий копмонент
 *
 * PHP version 5
 *
 * @package Framework
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

namespace Solo\Core;

class Controller
{
	/**
	 * Режим отладки
	 *
	 * @var boolean
	 */
	public $isDebug = false;

	/**
	 * Имя класса обработчика шаблонов
	 *
	 * @var string
	 */
	private $templateHandlerClass = "Solo\\Core\\SmartyTemplateHandler";

	/**
	 * Расширение файлов, содержащих шаблоны
	 *
	 * @var string
	 */
	public $templateExtension = ".html";

	/**
	 * Ссылка на текущий экземпляр Controller
	 *
	 * @var Controller
	 */
	private static $instance = null;

	/**
	 * Приватный конструктор
	 *
	 * @param boolean $isDebug Режим отладки
	 *
	 * @return \solo\core\Controller
	 */
	private function __construct($isDebug)
	{
		$this->isDebug = $isDebug;
	}

	/**
	* Возвращает экземпляр класса
	*
	* @param boolean $isDebug Режим отладки
	*
	* @return Controller
	*/
	public static function getInstance($isDebug = false)
	{
		if (!isset(self::$instance))
			self::$instance = new self($isDebug);
		return self::$instance;
	}


	/**
	 * Отрисовывает текущее преставление или выполняет текущее действие
	 *
	 * @param string $actionName
	 * @param string $viewName
	 *
	 * @throws HTTP404Exception
	 * @return string
	 */
	public function execute($requestUri, Route $route)
	{
		try
		{
			$classname = $route->get($requestUri);

			// Создание экземпляра действия
			$rc = new \ReflectionClass($classname);

			if ($rc->isSubclassOf("Solo\\Core\\View"))
				return $this->handleView($rc);

			if ($rc->isSubclassOf("Solo\\Core\\Action"))
				$this->executeAction($rc);

			//$action = $rc->newInstance();



			//var_dump($action);

//			// обрабатываем действие
//			if (null !== $actionName)
//				$this->executeAction($actionName);
//
//			// отрисовка представления
//			if ($viewName)
//				return $this->handleView($viewName);
		}
		catch (ClassLoaderException $cle)
		{
			throw new HTTP404Exception($cle);
		}
	}

	/**
	 * Обработка и отрисовка запрашиваемого представления
	 *
	 * @param \ReflectionClass $view
	 *
	 * @throws \RuntimeException
	 * @internal param object $viewName
	 *
	 * @return string
	 */
	public function handleView(\ReflectionClass $view)
	{
		//$rc = new \ReflectionClass($viewName . "View");
		//$view = $rc->newInstance();

		// Нельзя напрямую отображать Компоненты
		if($view->implementsInterface("Solo\\Core\\IViewComponent"))
			throw new \RuntimeException("Can't display component {$view->name}");

		// значит, не нужно будет рисовать макет (layout)
		if ($view->implementsInterface("Solo\\Core\\IAjaxView"))
			return $this->handleAjaxView($view);

		$view = $view->newInstance();

		if ($view->layout == null)
			throw new \RuntimeException("Undefined 'layout' property for {$view->__toString()}");

		// Получение данных в Представлении
		$view->preRender();
		$view->render();
		$view->postRender();

		// экземпляр обработчика шаблонов
		$rcTh = new \ReflectionClass($this->getTemplateHandlerClass());
		$tplHandler = $rcTh->newInstance();

		$view->templateFile = $this->getViewTemplate($view);

		// Магическая переменная шаблона - имя файла запрашиваемого представления
		$tplHandler->assign("CURRENT_VIEW_TEMPLATE", $view->templateFile);

		// назначим шаблону переменные Представления
		$tplHandler = $this->assignToHandler($view, $tplHandler);

		// Вывод HTML
		$html = $tplHandler->fetch($this->getViewLayout($view), $view->cacheId, $view->compileId);


		//if ($this->isDebug)
		//	$html = $this->addDebugInfo($view->name, $html, $view->templateFile);

		return $html;
	}

	/**
	 * Добавляет дополнительную информацию в шаблон представления
	 *
	 * @param string $viewName Имя представления
	 * @param string $html Содержимое представления
	 * @param string $info Дополнительная информация
	 *
	 * @return string
	 */
	private function addDebugInfo($viewName, $html, $info = "")
	{
		return "<!-- begin of '{$viewName}View' {$info} -->\n{$html}\n<!-- end of '{$viewName}View' {$info} -->\n";
	}

	/**
	 * Возвращает HTML код представления Компонента
	 *
	 * @param string $className Имя класса Представления Компонента
	 * @param array $args Список значений, передаваемых в конструктор компонента
	 *
	 * @throws HTTP404Exception
	 * @return string
	 */
	public function renderViewComponent($className, $args = null)
	{
		try
		{
			//$rc = new \ReflectionClass($className . "View");
			$rc = new \ReflectionClass($className);
			if (count($args) !== 0)
				$view = $rc->newInstanceArgs($args);
			else
				$view = $rc->newInstance();

			// Получение данных в Представлении
			$view->preRender();
			$view->render();
			$view->postRender();

			$templateHandlerClass = $this->getTemplateHandlerClass();
			// экземпляр обработчика шаблонов
			$rcTh = new \ReflectionClass($templateHandlerClass);
			$tplHandler = $rcTh->newInstance();

			// переменные Представления отправим в шаблон
			$tplHandler = $this->assignToHandler($view, $tplHandler);

			$view->templateFile = $this->getViewTemplate($view);

			// Вывод HTML
			$html = $tplHandler->fetch($view->templateFile, $view->cacheId, $view->compileId);

			if ($this->isDebug)
				$html = $this->addDebugInfo($className, $html, $view->templateFile);

			return $html;
		}
		catch (ClassLoaderException $cle)
		{
			throw new HTTP404Exception($cle->getMessage());
		}
	}

	/**
	 * Отрисовка представления, имплементирующего IAjaxView
	 *
	 * @param View $view Экземпляр представления
	 *
	 * @return string
	 */
	public function handleAjaxView(\ReflectionClass $view)
	{
		$view = $view->newInstance();

		// Получение данных в Представлении
		$view->preRender();
		$view->render();
		$view->postRender();

		// экземпляр обработчика шаблонов
		$rcTh = new \ReflectionClass($this->getTemplateHandlerClass());
		$tplHandler = $rcTh->newInstance();

		// назначим шаблону переменные Представления
		$tplHandler = $this->assignToHandler($view, $tplHandler);

		// Вывод HTML
		return $tplHandler->fetch($this->getViewTemplate($view), $view->cacheId, $view->compileId);
	}

	/**
	 * Заполняем шаблон данными
	 *
	 * @param View $view Экземпляр шаблонизатора
	 *
	 * @param ITemplateHandler $th
	 *
	 * @return ITemplateHandler
	 */
	protected function assignToHandler(View $view, ITemplateHandler $th)
	{
		// помещаем в шаблон публичные переменные представления
		$rc = new \ReflectionClass($view);
		$props = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);

		foreach ($props as $item)
		{
			$name = $item->getName();
			// некрасиво, но быстрее
			$th->assign($name, $view->$name);
		}
		return $th;
	}

	/**
	 * Возвращает путь к файлу шаблона предаставления
	 *
	 * @param View $view Экземпляр представления
	 *
	 * @return string
	 */
	public function getViewTemplate(View $view)
	{
		// если указан каталог с шаблоном для этого представления
		$folder = "";
		if ($view->templateFolder !== null)
			$folder = $view->templateFolder . DIRECTORY_SEPARATOR;

		$file = null;

		//$fileId = str_replace('\\', "_", get_class($view));
		$fileId = str_replace('App\\Views\\', "", get_class($view));
		//$fileId = str_replace('\\', ".", $fileId);

		if ($view->templateFile)
			$file = $folder . $view->templateFile;
		else
			$file = $folder . $fileId . $this->templateExtension;

		$file = str_replace("\\", DIRECTORY_SEPARATOR, $file);

		return Configurator::get("application:directory.templates") . DIRECTORY_SEPARATOR . $file;
	}

	/**
	 * Возвращает путь к файлу общего шаблона страницы
	 *
	 * @param View $view
	 *
	 * @return string
	 */
	private function getViewLayout(View $view)
	{
		$layoutDir = Configurator::get("application:directory.layouts");
		$file = $layoutDir . DIRECTORY_SEPARATOR . $view->layout;
		return $file;
	}

	/**
	 * Выполнение действия
	 *
	 * @param string $className
	 *
	 * @throws \RuntimeException
	 * @return void
	 */
	public function executeAction($rc)
	{
		$requestMethod = $_SERVER["REQUEST_METHOD"];

		$action = $rc->newInstance();

		// проверим, совпадают ли HTTP методы у запроса и Действия
		if ($action->requestMethod !== $requestMethod)
			throw new \RuntimeException("Can't execute action '{$className}': current HTTP request
				method is '{$action->requestMethod}' against required '{$requestMethod}'");

		// выполнение цепочки методов Действия
		$action->preExecute();
		$action->execute();
		$action->postExecute();

		// Действие должно заканчиваться редиректом - иначе ошибка
		throw new \RuntimeException("Action '{$className}' has been executed. Redirect?");
	}

	/**
	 * Возвращает  имя класса обработчика шаблонов
	 *
	 * @return string
	 */
	public function getTemplateHandlerClass()
	{
		return $this->templateHandlerClass;
	}

	/**
	 * Устанавливает имя класса обработчика шаблонов
	 *
	 * @param string $templateHandlerClass
	 *
	 * @return void
	 */
	public function setTemplateHandlerClass($templateHandlerClass)
	{
		$this->templateHandlerClass = $templateHandlerClass;
	}
}
