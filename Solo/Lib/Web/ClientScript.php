<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

namespace Solo\Lib\Web;

use Solo\Core\IApplicationComponent;

class ClientScript implements IApplicationComponent
{
	/**
	 * Версия файла
	 *
	 * @var string
	 */
	public $revision = "";

	/**
	 * Суффикс файла.
	 * Применяется для подключения минимизированных скриптов
	 * Только для Javascript
	 *
	 * @var string
	 */
	public $fileSuffix = null;

	/**
	 * Список загруженных скриптов
	 *
	 * @var array
	 */
	private $scripts = array();

	public function __construct()
	{

	}


	/**
	 * Осуществляет контроль загрузки JS и CSS на страницу
	 *
	 * @param string $fileName Имя скрипта
	 *
	 * @return bool
	 */
	public function load($fileName)
	{
		$hash = md5($fileName);

		if (!in_array($hash, $this->scripts))
		{
			$this->scripts[] = $hash;
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Инициализация компонента
	 *
	 * @see IApplicationComponent::initComponent()
	 *
	 * @return void
	 **/
	public function initComponent()
	{
		return true;
	}

}
?>
