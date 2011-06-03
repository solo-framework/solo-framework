<?php
/**
 * Компонент отображает ошибку из контекста
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class DisplayErrorView extends View implements IViewComponent
{
	/**
	 * Путь к каталогу, где находится шаблон представления
	 *
	 * @var string
	 */
	public $templateFolder = "components";

	/**
	 * Описание ошибки
	 *
	 * @var mixed
	 */
	public $message = null;

	/**
	 * Идентификатор сообщения
	 *
	 * @var string
	 */
	public $id = null;

	/**
	 * Получение данных для шаблона
	 *
	 * @return void
	 */
	public function render()
	{
		$error = Context::getFlashMessage();
		if ($error !== null)
		{
			$this->id = $error["id"];

			if ($error instanceof Exception)
				$this->message[] = $error->getMessage();
			if (!is_array($error))
				$this->message[] = $error["message"];

			$this->message = $error["message"];
		}
	}
}
?>