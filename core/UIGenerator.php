<?php
/**
 * Класс для отрисовки представлений и выполнения действий
 * 
 * PHP version 5
 * 
 * @category 
 * @package  
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: UIGenerator.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

require_once "core/IPage.php";
require_once "core/ITemplateHandler.php";

class UIGenerator
{

	/**
	 * Ссылка на текущий экземпляр UIGenerator
	 * 
	 * @var UIGenerator
	 */
	private static $instance = null;
	
	private $templateHandlerClass = null;

	/**
	 * Экземпляр маршрутизатора
	 *
	 * @var Router
	 */
	private $router = null;

	private function __construct(){}
	
	/**
	* Возвращает экземпляр класса
	* 
	* @return UIGenerator
	*/
	public static function getInstance()
	{	
		if (!isset(self::$instance))
		{
			self::$instance = new UIGenerator();
		}
		return self::$instance;
	}
	
	/**
	 * Выполняет обработку марштуризатора: отрисовывает текущее
	 * преставление или выполняет текущее действие
	 *
	 * @param Router $router
	 * @param string $templateHandlerClass
	 */
	public function execute(Router $router)
	{
		$this->router = $router;
		$templateHandlerClass = $this->getTemplateHandlerClass();
		$moduleName = $router->getModuleName();
	
		// Запрашиваем представление
		if ($router->requestType == Router::TYPE_VIEW)
		{
			$viewName = $router->getViewName();
			// Создаем нужный класс	
			$className = "{$moduleName}_{$viewName}_View";
			$rc = new ReflectionClass($className);
				
			// Нельзя напрямую отображать Компоненты
			if($rc->implementsInterface("IComponent"))
			{
				throw new SystemException("Page not found", "can't display component {$className}", SystemException::HTTP_404);
			}

			// создаем экземпляр
			$view = $rc->newInstance();
			
			// Если имплементирует интерфейс IPage, то
			// нужно загружать макет страницы.
			if ($rc->implementsInterface("IPage"))
			{
				if ($view->layout == null)
					throw new RuntimeException("Undefined 'layout' property for {$className}");

				// Получение данных в Представлении
				$view->preRender();
				$view->render();
				$view->postRender();
				
				// экземпляр обработчика шаблонов
				$rcTh = new ReflectionClass($templateHandlerClass);
				$tplHandler = $rcTh->newInstance();
				
				// переменные Представления отправим в шаблон
				$tplHandler = $this->assignToHandler($view, $tplHandler);
				
				if ($view->templateFile == null)
					throw new RuntimeException("Undefined 'templateFile' property for '{$className}'");
					
				// Магическая переменная шаблона - имя файла запрашиваемого представления
				$tplHandler->assign("CURRENT_VIEW_TEMPLATE", $view->templateFile);
				
				// Вывод HTML
				return $tplHandler->fetch($view->layout);
			}	
		}
		
		// Запрашиваем действие
		if ($router->requestType == Router::TYPE_ACTION)
		{
			$actionName = $router->getActionName();
			$className = "{$moduleName}_{$actionName}_Action";
			$this->executeAction($className);
		}
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
		$rc = new ReflectionClass($className);
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
		if ($view->templateFile == null)
			throw new RuntimeException("Undefined 'templateFile' property file for '{$className}'");
			
		// Вывод HTML
		return $tplHandler->fetch($view->templateFile);
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
		//$th->registerObject();
		$data = $view->getData();
		// Заполняем шаблон данными		
		if (count($data) > 0)
		{
			foreach ($data as $key => $value) 
			{
				$th->assign($key, $value);
			}
		}
		
		// помещаем в шаблон публичные переменные контроллера, 
		// они имеют глобальную область видимости
		$vars = get_object_vars($view);
		foreach ($vars as $key => $value)
		{
			$th->assign($key, $value);
		}

		return $th;
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
		// Создание экземпляра действия
		$rc = new ReflectionClass($className);
		$action = $rc->newInstance();

		// проверим, совпадают ли HTTP методы у запроса и Действия
		if ($action->requestMethod !== $this->router->requestMethod)
			throw new RuntimeException("Can't execute action '{$className}': action request
				method is '{$action->requestMethod}' vs. HTTP request method is '{$this->router->requestMethod}'");

		// выполнение цепочки методов Действия
		$action->preExecute();
		$action->execute();
		$action->postExecute();
		
		echo "Action '{$className}' has been executed. Redirect?";
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
