<?php
/**
 * Класс для отрисовки представлений и выполнения действий
 * Связующий копмонент
 *
 * PHP version 5
 *
 * @package Framework
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class Binder
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
	private $templateHandlerClass = "SmartyTemplateHandler";

	/**
	 * Расширение файлов, содержащих шаблоны
	 *
	 * @var string
	 */
	public $templateExtension = ".html";

	/**
	 * Ссылка на текущий экземпляр Binder
	 *
	 * @var Binder
	 */
	private static $instance = null;

	/**
	 * Приватный конструктор
	 *
	 * @param boolean $isDebug Режим отладки
	 *
	 * @return void
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
	* @return Binder
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
	 * @return string
	 */
	public function execute($actionName, $viewName)
	{
		// обрабатываем действие
		if (null !== $actionName)
			$this->executeAction($actionName);

		// отрисовка представления
		if ($viewName)
			return $this->handleView($viewName);
	}

	/**
	 * Обработка и отрисовка запрашиваемого представления
	 *
	 * @param string $viewName
	 *
	 * @return string
	 */
	public function handleView($viewName)
	{
		$rc = new ReflectionClass($viewName . "View");
		$view = $rc->newInstance();

		// Нельзя напрямую отображать Компоненты
		if($rc->implementsInterface("IComponent"))
			throw new RuntimeException("Can't display component {$viewName}");

		// значит, не нужно будет рисовать макет (layout)
		if ($rc->implementsInterface("IAjaxView"))
			return $this->handleAjaxView($view);

		if ($view->layout == null)
			throw new RuntimeException("Undefined 'layout' property for {$viewName}");

		// Получение данных в Представлении
		$view->preRender();
		$view->render();
		$view->postRender();

		// экземпляр обработчика шаблонов
		$rcTh = new ReflectionClass($this->getTemplateHandlerClass());
		$tplHandler = $rcTh->newInstance();

		$view->templateFile = $this->getViewTemplate($view);

		// Магическая переменная шаблона - имя файла запрашиваемого представления
		$tplHandler->assign("CURRENT_VIEW_TEMPLATE", $view->templateFile);

		// назначим шаблону переменные Представления
		$tplHandler = $this->assignToHandler($view, $tplHandler);

		// Вывод HTML
		$html = $tplHandler->fetch($this->getViewLayout($view));

		if ($this->isDebug)
			$html = $this->addDebugInfo($viewName, $html, $view->templateFile);

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
	 * @return string
	 */
	public function renderComponent($className, $args = null)
	{
		$rc = new ReflectionClass($className . "View");
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
		$rcTh = new ReflectionClass($templateHandlerClass);
		$tplHandler = $rcTh->newInstance();

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
	 *
	 *
	 * @return
	 */
	public function handleAjaxView(IAjaxView $view)
	{
		// экземпляр обработчика шаблонов
		$rcTh = new ReflectionClass($this->getTemplateHandlerClass());
		$tplHandler = $rcTh->newInstance();

		// назначим шаблону переменные Представления
		$tplHandler = $this->assignToHandler($view, $tplHandler);

		// Вывод HTML
		return $tplHandler->fetch($this->getViewTemplate($view));
	}

	/**
	 * Заполняем шаблон данными
	 *
	 * @param View $view Экземпляр представления
	 * @param View $view Экземпляр шаблонизатора
	 *
	 * @return ITemplateHandler
	 */
	protected function assignToHandler(View $view, ITemplateHandler $th)
	{
		// помещаем в шаблон публичные переменные представления
		$rc = new ReflectionClass($view);
		$props = $rc->getProperties(ReflectionProperty::IS_PUBLIC);

		foreach ($props as $item)
		{
			$name = $item->getName();
			// некрасиво, но быстрее
			$th->assign($name, $view->$name);
		}
		return $th;
	}

	public function getViewTemplate(View $view)
	{
		// если указан каталог с шаблоном для этого представления
		$folder = "";
		if ($view->templateFolder !== null)
			$folder = $view->templateFolder . DIRECTORY_SEPARATOR;

		$file = null;
		if ($view->templateFile)
			$file = $folder . $view->templateFile;
		else
			$file = $folder . get_class($view) . $this->templateExtension;

		return Configurator::get("framework:directory.templates") . DIRECTORY_SEPARATOR . $file;
	}

	/**
	* Возвращает путь к файлу общего шаблона страницы
	*
	* @return string
	*/
	private function getViewLayout(View $view)
	{
		$layoutDir = Configurator::get("framework:directory.layouts");
		$file = $layoutDir . DIRECTORY_SEPARATOR . $view->layout;
		return $file;
	}

	/**
	 * Выполнение действия
	 *
	 * @param string $className
	 *
	 * @return void
	 */
	public function executeAction($className)
	{
		$requestMethod = $_SERVER["REQUEST_METHOD"];

		// Создание экземпляра действия
		$rc = new ReflectionClass($className . "Action");
		$action = $rc->newInstance();

		// проверим, совпадают ли HTTP методы у запроса и Действия
		if ($action->requestMethod !== $requestMethod)
			throw new RuntimeException("Can't execute action '{$className}': current HTTP request
				method is '{$action->requestMethod}' against required '{$requestMethod}'");

		// выполнение цепочки методов Действия
		$action->preExecute();
		$action->execute();
		$action->postExecute();

		// Действие должно заканчиваться редиректом - иначе ошибка
		throw new RuntimeException("Action '{$className}' has been executed. Redirect?");
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
?>