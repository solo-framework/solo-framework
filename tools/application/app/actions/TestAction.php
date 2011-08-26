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
		// проверяем текстовое поле
		Validator::check(Request::get("text"), "Поле Text: ")
			->required(true, "обязательное")
			->minLength(3, "длина значения должна быть больше 3 символов");

		// проверям, выбрал ли чекбокс
		Validator::check(Request::get("agree"))
			->required(true, "Не выбран agree");

		// В зависимости от результата валидации формы делаем редирект
		if (!Validator::isValid())
		{
			FormRestore::saveData("upload_form");
			Application::getInstance()->redirectBack(Validator::getMessages());
		}
		else
			Application::getInstance()->redirect("index.php?view=index", "Действие успешно выполнено");


	}
}
?>