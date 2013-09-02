<?php
namespace Solo\Core;

/**
 * Класс обрабатывает шаблоны Smarty
 *
 * PHP version 5
 *
 * @package Solo\Core
 * @author   Andrey Filippov <afi.work@gmail.com>
 */

class SmartyTemplateHandler extends \Smarty implements ITemplateHandler
{
	public function __construct()
	{
		parent::__construct();

		// Включение безопасного режима
		if (Configurator::get("smarty:security"))
		{
			// если задано имя класса для конфигурации безопасности
			$securityClass = Configurator::get("smarty:securityClass");
			if ($securityClass)
			{
				$this->enableSecurity($securityClass);
			}
			else
			{
				// иначе, заполняем дефолтный класс значениями
				$this->enableSecurity();
				$options = Configurator::getArray("smarty:securityOptions");
				foreach ($options as $k => $v)
					$this->security_policy->{$k} = $v;
			}
		}

		$this->left_delimiter = Configurator::get("smarty:leftDelimiter");
		$this->right_delimiter = Configurator::get("smarty:rightDelimiter");

		$this->compile_check = Configurator::get("smarty:compile.check");
		$this->debugging = (bool)Configurator::get("smarty:debugging");
		$this->caching = Configurator::get("smarty:caching");
		$this->force_compile = (bool)Configurator::get("smarty:forceCompile");
		$this->error_reporting = Configurator::get("smarty:error.reporting");
		$this->compile_check = Configurator::get("smarty:compile.check");

		$this->setCompileDir(Configurator::get("smarty:compile.dir"));
		$this->setConfigDir(Configurator::get("smarty:config.dir"));
		$this->setCacheDir(Configurator::get("smarty:cache.dir"));

		//Загрузка пользовательских плагинов и функций
		$this->addPluginsDir(Configurator::get("smarty:user.plugins"));
	}
}
?>
