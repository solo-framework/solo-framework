<?php
/**
 * Пример действия
 * 
 * PHP version 5
 * 
 * @package 
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class TestAction extends Action
{
	/**
	 * Выполнение действия
	 * 
	 * @return void
	 */
	public function execute()
	{
		Validator::check(Request::get("text"), "Поле Text: ")
			->required(true, "обязательное")
			->minLength(3, "длина значения должна быть больше 3 символов");

		Validator::check(Request::get("agree"))
			->required(true, "Не выбран agree");
				
		if (!Validator::isValid())
			Application::getInstance()->redirectBack(Validator::getMessages());
		else 
			Application::getInstance()->redirect("index.php?view=index", "Действие успешно выполнено");	
	}
}
?>