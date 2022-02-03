<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core\UI\Smarty;

abstract class View extends \Solo\Core\UI\View
{
	protected $cacheId = null;
	protected $compileId = null;
	protected $caching = \Smarty::CACHING_OFF;
	protected $cacheLifetime = 3600;
	protected $parent = null;
	protected $mergeTplVars = true;
	protected $noOutputFilter = false;

	/**
	 * Дополнительные данные для обработчика шаблонов
	 *
	 * @return array
	 */
	public function getExtraData(): array
	{

		return array(

			// здесь описаны настройки кэширования
			"cacheId" => $this->cacheId,
			"compileId" => $this->compileId,
			"caching" => $this->caching,
			"cacheLifetime" => $this->cacheLifetime,

			// доп. данные для метода fetch
			"parent" => $this->parent,
			"mergeTplVars" => $this->mergeTplVars,
			"noOutputFilter" => $this->noOutputFilter
		);
	}
}

