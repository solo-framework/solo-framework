<?php
/**
 * Класс обрабатывает шаблоны Smarty
 * 
 * PHP version 5
 * 
 * @category 
 * @package  
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: SmartyTemplateHandler.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

require_once FRAMEWORK_ROOT_DIR . "/lib/Smarty/Smarty.class.php";

class SmartyTemplateHandler extends Smarty implements ITemplateHandler
{
	public function __construct()
	{
		parent::__construct();

		// Включение безопасного режима 
		if (Configurator::get("smarty:security"))
		{
			$this->enableSecurity();
			$this->security_policy->secure_dir = Configurator::getArray("smarty:secureDirs");
		}
		
		$this->left_delimiter = Configurator::get("smarty:leftDelimiter");
		$this->right_delimiter = Configurator::get("smarty:rightDelimiter");

		// template_dir указываем , чтобы система безопасности Smarty
		// позволяла читать файлы
		$this->template_dir = ".";
		$this->compile_dir = Configurator::get("smarty:compile.dir");
		$this->config_dir = Configurator::get("smarty:config.dir");
		$this->cache_dir = Configurator::get("smarty:cache.dir");
		$this->compile_check = Configurator::get("smarty:compile.check");
		$this->debugging = (bool)Configurator::get("smarty:debugging");
		$this->error_reporting = Configurator::get("smarty:error.reporting");
		$this->compile_check = Configurator::get("smarty:compile.check");
			
		//Загрузка пользовательских плагинов и функций
		$this->plugins_dir[] = Configurator::get("smarty:user.plugins");
	}	

}
?>