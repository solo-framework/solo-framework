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
	 * Представление, обрабатываемое в текущем запросе
	 *
	 * @var View
	 */
	private $currentView = null;

	/**
	 * Имя класса текущего предствления
	 *
	 * @var string
	 */
	private $currentViewName = null;

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
	 * Возвращает имя класса текущего Представления
	 *
	 * @return string
	 */
	public function getCurrentViewName()
	{
		if (!$this->currentViewName)
			$this->currentViewName = get_class($this->currentView);
		
		return $this->currentViewName;
	}

	/**
	 * Возвращает экземпляр текущего Представления
	 *
	 * @return null|View
	 */
	public function getCurrentView()
	{
		return $this->currentView;
	}

	/**
	 * Отрисовывает текущее преставление или выполняет текущее действие
	 *
	 * @param $requestUri Строка запроса query string
	 * @param Route $route Экземпляр маршрутизатора
	 *
	 * @throws HTTP404Exception
	 * @return string
	 */
	public function execute($requestUri, Route $route)
	{
		$classname = $route->getClass($requestUri);
		if (is_null($classname))
			throw new HTTP404Exception("Can't find routing for '{$requestUri}'");

		// Создание экземпляра действия
		$rc = new \ReflectionClass($classname);

		$ns = __NAMESPACE__;
		if ($rc->isSubclassOf("{$ns}\\View"))
			return $this->handleView($rc);

		if ($rc->isSubclassOf("{$ns}\\Action"))
			$this->executeAction($rc);
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
	public function handleView(\ReflectionClass $view, $args = null)
	{
		// Нельзя напрямую отображать Компоненты
		if($view->implementsInterface("Solo\\Core\\IViewComponent"))
			throw new \RuntimeException("Can't display component {$view->name}");

		// значит, не нужно будет рисовать макет (layout)
		if ($view->implementsInterface("Solo\\Core\\IAjaxView"))
			return $this->handleAjaxView($view);

		if (count($args) !== 0)
			$this->currentView = $view->newInstanceArgs($args);
		else
		$this->currentView = $view->newInstance();

		if ($this->currentView->layout == null)
			throw new \RuntimeException("Undefined 'layout' property for " . get_class($view));

		// Получение данных в Представлении
		$this->currentView->preRender();
		$this->currentView->render();
		$this->currentView->postRender();

		// экземпляр обработчика шаблонов
		$rcTh = new \ReflectionClass($this->getTemplateHandlerClass());
		$tplHandler = $rcTh->newInstance();

		$this->currentView->templateFile = $this->getViewTemplate($this->currentView);

		// Магическая переменная шаблона - имя файла запрашиваемого представления
		$tplHandler->assign("CURRENT_VIEW_TEMPLATE", $this->currentView->templateFile);

		// назначим шаблону переменные Представления
		$tplHandler = $this->assignToHandler($this->currentView, $tplHandler);

		// Вывод HTML
		$html = $tplHandler->fetch($this->getViewLayout($this->currentView), $this->currentView->cacheId, $this->currentView->compileId);


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
		return "<!-- begin of '{$viewName}' {$info} -->\n{$html}\n<!-- end of '{$viewName}' {$info} -->\n";
	}

	/**
	 * Возвращает HTML-код представления
	 *
	 * @param string $className  Имя класса Представления
	 * @param array $args Список значений, передаваемых в конструктор компонента
	 *
	 * @return string
	 */
	public function renderView($className, $args = null)
	{
		$rc = new \ReflectionClass($className);

		if (!is_array($args))
			$args = array($args);

		return $this->handleView($rc, $args);
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
		catch (\Exception $cle)
		{
			throw new HTTP404Exception($cle->getMessage());
		}
	}

	/**
	 * Отрисовка представления, имплементирующего IAjaxView
	 *
	 * @param \ReflectionClass $view Экземпляр представления
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
		$fileId = get_class($view);

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
	 * @param \ReflectionClass $rc Экземпляр класса Action
	 *
	 * @throws \RuntimeException
	 *
	 * @return void
	 */
	public function executeAction(\ReflectionClass $rc)
	{
		$requestMethod = $_SERVER["REQUEST_METHOD"];
		$action = $rc->newInstance();

		// проверим, совпадают ли HTTP методы у запроса и Действия
		if ($action->requestMethod !== $requestMethod)
			throw new \RuntimeException("Can't execute action '{$rc->getName()}': current HTTP request
				method is '{$action->requestMethod}' against required '{$requestMethod}'");

		// выполнение цепочки методов Действия
		$action->preExecute();
		$action->execute();
		$action->postExecute();

		// Действие должно заканчиваться редиректом - иначе ошибка
		throw new \RuntimeException("Action '{$rc->getName()}' has been executed. Redirect?");
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
