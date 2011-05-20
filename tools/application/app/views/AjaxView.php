<?php
/**
 * Created by JetBrains PhpStorm.
 * User: afi
 * Date: 05.05.11
 * Time: 13:36
 * To change this template use File | Settings | File Templates.
 */

class AjaxView extends View implements IAjaxView
{
	public $value = null;

	/**
	 * Получение данных для шаблона
	 *
	 * @return void
	 */
	public function render()
	{
		$this->value = "Эти данные получены из AjaxView";
	}
}
?>
 
