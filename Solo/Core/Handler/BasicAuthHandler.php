<?php
/**
 * Basic-авторизация в приложении
 *
 * PHP version 5
 *
 * @example
 *			"Solo\\Core\\Handler\\BasicAuthHandler" => array(
 *				"cridentials" => array("user:password", "user2:passw2"),
 *				"realmName" => "Secret zone!",
 *				"failText" => "You are not valid user!"
 *			),
 * @package Handler
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core\Handler;

class BasicAuthHandler extends Handler
{
	/**
	 * Список логинов и паролей для аутентификации
	 * @var array
	 */
	public $cridentials = array();

	/**
	 * realm сообщение
	 *
	 * @var string
	 */
	public $realmName = "Default realm name";

	/**
	 * Текст, отображаемый при неудачной попытке
	 * аутентификации
	 *
	 * @var string
	 */
	public $failText = "";

	/**
	 * Выполнение действия перед обработкой представления
	 *
	 * @return void
	 */
	public function onBegin()
	{
		if (isset($_SESSION["__basic_auth"]))
			return;

		if (!isset($_SERVER['PHP_AUTH_USER']))
		{
			header("WWW-Authenticate: Basic realm=\"{$this->realmName}\"");
			header('HTTP/1.0 401 Unauthorized');
			echo $this->failText;
			exit;
		}
		else
		{
			$user = $_SERVER['PHP_AUTH_USER'];
			$password = $_SERVER['PHP_AUTH_PW'];

			$isValid = false;
			foreach ($this->cridentials as $item)
			{
				list($u, $p) = explode(":", $item);
				if ($user == $u && $password == $p)
				{
					$isValid = true;
					$_SESSION["__basic_auth"] = 1;
				}
			}

			if (!$isValid)
			{
				header("WWW-Authenticate: Basic realm=\"{$this->realmName}\"");
				header('HTTP/1.0 401 Unauthorized');
				echo $this->failText;
				exit();
			}
		}
	}

}

