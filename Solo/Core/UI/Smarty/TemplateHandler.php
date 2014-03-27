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

use Solo\Core\UI\BaseTemplateHandler;
use Solo\Core\UI\ITemplateHandler;

class TemplateHandler extends BaseTemplateHandler implements ITemplateHandler
{
	/**
	 * @param array $config Список настроек Smarty
	 * @param $extraData
	 */
	public function __construct($config, $extraData)
	{
		$this->render = new \Smarty();
		$this->extraData = $extraData;

		foreach ($config["properties"] as $k => $v)
			$this->render->$k = $v;

		// Настройки безопасного режима
		if ($config["security"]["enabled"])
		{
			// если задано имя класса для конфигурации безопасности
			$securityClass = $config["security"]["securityClass"];
			if ($securityClass)
			{
				$this->render->enableSecurity($securityClass);
			}
			else
			{
				// иначе, заполняем дефолтный класс значениями
				$this->render->enableSecurity();
				$options = $config["security"]["securityOptions"];
				foreach ($options as $k => $v)
					$this->render->security_policy->{$k} = $v;
			}
		}

		$this->render->setTemplateDir($config["folders"]["templates"]);
		$this->render->setAutoloadFilters($config["folders"]["filters"]);
		$this->render->setCacheDir($config["folders"]["cache"]);
		$this->render->setCompileDir($config["folders"]["compile"]);
		$this->render->setConfigDir($config["folders"]["config"]);
		$this->render->setPluginsDir($config["folders"]["plugins"]);

		$this->render->setCaching($this->getExtra("caching"));
		$this->render->setCacheLifetime($this->getExtra("cacheLifetime"));

		// также можно загружать плагины из конфига
		foreach($config["plugins"] as $plugin)
		{
			$plug = new $plugin();
			$this->render->registerPlugin($plug->getType(), $plug->getTag(), array($plug, "execute"), $plug->getCacheable(), $plug->getCahceAttributes());
		}
	}

	/**
	 * Возвращает результат обработки шаблона
	 *
	 * @param string $template Путь к шаблону
	 *
	 * @return string
	 */
	public function fetch($template)
	{
		$compileId = $this->getExtra("compileId");
		$cacheId = $this->getExtra("cacheId");
		$parent = $this->getExtra("parent");
		$mergeTplVars = $this->getExtra("mergeTplVars", true);
		$noOutputFilter = $this->getExtra("noOutputFilter", false);

		return $this->render->fetch($template, $cacheId, $compileId, $parent, false, $mergeTplVars, $noOutputFilter);
	}

	/**
	 * @param string $name Имя переменной шаблона
	 * @param mixed $value Значение переменной шаблона
	 *
	 * @return mixed
	 */
	public function assign($name, $value)
	{
		$this->render->assign($name, $value);
	}
}

