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
	 * @return void
	 */
	private function __construct()
	{

	}

	/**
	* Возвращает экземпляр класса
	*
	* @return Binder
	*/
	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new self();
		return self::$instance;
	}


	/**
	 * Отрисовывает текущее преставление или выполняет текущее действие
	 *
	 * @param string $actionName
	 * @param string $viewName
	 */
	public function execute($actionName, $viewName)
	{
		// обрабатываем действие
		if (null !== $actionName)
		{
			$this->executeAction($actionName);

			// Действие должно заканчиваться редиректом - иначе ошибка
			//echo "Action '{$actionName}' has been executed. Redirect?";
			//exit();
		}

		// отрисовка представления
		if ($viewName)
			return $this->handleView($viewName);
	}

	/**
	 * Обработка и отрисовка запрашиваемого представления
	 *
	 * @param unknown_type $viewName
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

		// макет нужен
		//$isPage = $rc->implementsInterface("IPage");

		if ($view->layout == null)
			throw new RuntimeException("Undefined 'layout' property for {$viewName}");

		//if ($view->templateFile == null)
		//	throw new RuntimeException("Undefined 'templateFile' property for '{$viewName}'");

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
		return $tplHandler->fetch($this->getViewLayout($view));
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
		//if ($view->templateFile == null)
		//	throw new RuntimeException("Undefined 'templateFile' property file for '{$className}'");
		$view->templateFile = $this->getViewTemplate($view);

		// Вывод HTML
		return $tplHandler->fetch($view->templateFile);
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
			$th->assign($item->getName(), $item->getValue($view));

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
	 * Отправляет HTTP заголовок 404
	 *
	 * @return void
	 */
	public static function send404()
	{
		header("HTTP/1.1 404 Not Found");
		exit();
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