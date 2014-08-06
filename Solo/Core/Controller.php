<?php
/**
 * Класс для обработки Действий и отрисовки Представлений
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core;

use Solo\Core\UI\ITemplateHandler;
use Solo\Core\UI\View;

class Controller implements IApplicationComponent
{
	/**
	 * Полное название класса, выполняющего обработку шаблона
	 *
	 * @var string
	 */
	public $rendererClass = null;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * Расширение файлов, содержащих шаблоны
	 *
	 * @var string
	 */
	public $templateExtension = ".html";

	/**
	 * Режим отладки
	 *
	 * @var bool
	 */
	public $isDebug = false;

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


	public function initComponent()
	{

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
	 * @param string $requestUri Строка запроса query string
	 * @param Router $route Экземпляр маршрутизатора
	 *
	 * @throws HTTP404Exception
	 * @return string
	 */
	public function execute($requestUri, Router $route)
	{
		$classname = $route->getClass($requestUri);
		if (is_null($classname))
			throw new HTTP404Exception("Can't find routing for '{$requestUri}'");

		// Создание экземпляра действия
		$rc = new \ReflectionClass($classname);

		$ns = __NAMESPACE__;
		if ($rc->isSubclassOf("{$ns}\\UI\\View"))
			return $this->handleView($rc);

		if ($rc->isSubclassOf("{$ns}\\Action"))
			$this->executeAction($rc);
	}

	/**
	 * Обработка и отрисовка запрашиваемого представления
	 *
	 * @param \ReflectionClass $view
	 *
	 * @param array $args Аргументы, передаваемые в конструктор View
	 *
	 * @throws \RuntimeException
	 * @internal param object $viewName
	 *
	 * @return string
	 */
	public function handleView(\ReflectionClass $view, $args = null)
	{
		// Нельзя напрямую отображать Компоненты
		if($view->implementsInterface("Solo\\Core\\UI\\IComponent"))
			throw new \RuntimeException("Can't display component {$view->name}");

		// значит, не нужно будет рисовать макет (layout)
		if ($view->implementsInterface("Solo\\Core\\UI\\IAjaxView"))
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
		$rcTh = new \ReflectionClass($this->getRenderClass());
		$tplHandler = $rcTh->newInstanceArgs(array($this->options, $this->currentView->getExtraData()));

		$this->currentView->templateFile = $this->getViewTemplate($this->currentView);

		// Магическая переменная шаблона - имя файла запрашиваемого представления
		$tplHandler->assign("CURRENT_VIEW_TEMPLATE", $this->currentView->templateFile);

		// назначим шаблону переменные Представления
		$tplHandler = $this->assignToHandler($this->currentView, $tplHandler);

		// Вывод HTML
		$html = $tplHandler->fetch($this->getViewLayout($this->currentView));
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
	protected function addDebugInfo($viewName, $html, $info = "")
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
		$rc = new \ReflectionClass($className);
		if (count($args) !== 0)
			$view = $rc->newInstanceArgs($args);
		else
			$view = $rc->newInstance();

		// Получение данных в Представлении
		$view->preRender();
		$view->render();
		$view->postRender();

		$templateHandlerClass = $this->getRenderClass();
		// экземпляр обработчика шаблонов
		$rcTh = new \ReflectionClass($templateHandlerClass);
		$tplHandler = $rcTh->newInstanceArgs(array($this->options, $view->getExtraData()));

		// переменные Представления отправим в шаблон
		$tplHandler = $this->assignToHandler($view, $tplHandler);
		$view->templateFile = $this->getViewTemplate($view);

		// Вывод HTML
		$html = $tplHandler->fetch($view->templateFile);

		if ($this->isDebug)
			$html = $this->addDebugInfo($className, $html, $view->templateFile);

		return $html;
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
		$rcTh = new \ReflectionClass($this->getRenderClass());
		$tplHandler = $rcTh->newInstance($this->options, $view->getExtraData());

		// назначим шаблону переменные Представления
		$tplHandler = $this->assignToHandler($view, $tplHandler);

		// Вывод HTML
		return $tplHandler->fetch($this->getViewTemplate($view));
	}

	/**
	 * Заполняем шаблон данными
	 *
	 * @param View $view Экземпляр шаблонизатора
	 *
	 * @param ITemplateHandler $th Экземпляр обработчика шаблонов
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
		return $file;
	}

	/**
	 * Возвращает путь к файлу общего шаблона страницы
	 *
	 * @param View $view
	 *
	 * @return string
	 */
	protected function getViewLayout(View $view)
	{
		return $view->layout;
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
				method is '{$requestMethod}'. You have to use '{$action->requestMethod}'.");

		// выполнение цепочки методов Действия
		$action->preExecute();
		$action->execute();
		$action->postExecute();

		// Действие должно заканчиваться редиректом - иначе ошибка
		throw new \RuntimeException("Action '{$rc->getName()}' has been executed. Have you forgotten to do a redirect?");
	}

	/**
	 * Возвращает  имя класса обработчика шаблонов
	 *
	 * @return string
	 */
	public function getRenderClass()
	{
		return $this->rendererClass;
	}
}

