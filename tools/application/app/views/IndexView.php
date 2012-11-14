<?php
/**
 * 
 * 
 * PHP version 5
 * 
 * @package 
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

class IndexView extends View
{
	
	/**
	 * Файл с макетом страницы
	 * Этот шаблон будет отрисован его контексте
	 * 
	 * @var string
	 */
	public $layout = "index.html";
	
	/**
	 * Публичное свойство представления, оно
	 * будет видно в шаблоне
	 * 
	 * @var string
	 */
	public $myVar = null;
	
	/**
	 * Публичное свойство представления, оно
	 * будет видно в шаблоне
	 * 
	 * @var string
	 */
	public $title = "";
	
	
	/**
	 * Приватное свойство представления, оно
	 * НЕ будет видно в шаблоне
	 * 
	 * @var string
	 */
	private $privateVar = null;
	
	
	/**
	 * Получение данных для шаблона
	 * 
	 * @return void
	 */
	public function render()
	{
		$this->myVar = "Это значение из IndexView";
		$this->title = "Заголовок страницы определен в IndexView и находится в layout/index.html";
		$this->privateVar = "Это значение приватного свойства, оно не будет видно в шаблоне";
	}


}
?>