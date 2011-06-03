<?php
/**
 * Пример компонента
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class TestComponentView extends View implements IViewComponent
{
	/**
	 * Какое-то значение
	 *
	 * @var string
	 */
	public $compValue = null;

	/**
	 * Получение данных для шаблона
	 *
	 * @return void
	 */
	public function render()
	{
		$this->compValue = "Это значение определено как публичное свойство TestComponentView";
	}
}
?>