<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core\Handler;

class BasicAuthHandler extends Handler
{
	public $cridentials = array();

	public $realmName = "Default realm name";

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
				echo $this->failText;
				exit();
			}
		}
	}

}

