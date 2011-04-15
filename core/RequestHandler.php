<?php
/**
 * Класс для отрисовки представлений и выполнения действий
 * 
 * PHP version 5
 * 
 * @package Framework
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class RequestHandler
{
	/**
	 * Ссылка на текущий экземпляр UIGenerator
	 * 
	 * @var UIGenerator
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
	* @return RequestHandler
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
			$this->handleAction($actionName);
			
			// Действие должно заканчиваться редиректом - иначе ошибка
			die("Action '{$actionName}' has been executed. Redirect?");
		}
		
		// отрисовка представления
		if ($viewName)
			$this->handleView($viewName);
	}
	
	private function handleAction($actionName)
	{
	
	}
	
	
	private function handleView($viewname)
	{
	
	}
}
?>