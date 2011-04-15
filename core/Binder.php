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
			$this->handleView($viewName);
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

		// значит, не нужно рисовать макет (layout)
		$isAjax = $rc->implementsInterface("IAjaxView");

		// макет нужен
		$isPage = $rc->implementsInterface("IPage");

		if ($view->layout == null && $isPage)
			throw new RuntimeException("Undefined 'layout' property for {$viewName}");

		if ($view->templateFile == null)
			throw new RuntimeException("Undefined 'templateFile' property for '{$viewName}'");

		// Получение данных в Представлении
		$view->preRender();
		$view->render();
		$view->postRender();
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
}
?>