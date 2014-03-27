<?php
/**
 * Обработчик шаблонов Twig
 *
 * PHP version 5
 *
 * @package Render
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

//      "controller" => array(
//			"@class" => "Solo\\Core\\Controller2",
//			"rendererClass" => "Solo\\Core\\Render\\TwigRenderer",
//			"templateExtension" => ".html",
//			"isDebug" => true,
//
//			"config" => array
//			(
//					"templateDirs" => array(
//							BASE_DIRECTORY ."/src/apps/App/templates/layouts/twig",
//							BASE_DIRECTORY ."/src/apps/App/templates/twig"
//					),
//					"env" => array(
//							"cache" => BASE_DIRECTORY . "/var/cache",
//							"debug" => true
//					),
//					"functions" => array(),
//					"extensions" => array("App\\Twig\\Extensions\\Link"),
//
//			)
//      )

namespace Solo\Core\Render;


class TwigRenderer extends Base implements IRender
{

	/**
	 * Список переменных шаблона и их значений
	 *
	 * @var array|null
	 */
	protected $data = null;

	/**
	 * Ctor
	 *
	 * @param array $config Список настроек
	 * @param array $extraData Дополнительные данные из View
	 */
	public function __construct($config, $extraData)
	{
		$loader = new \Twig_Loader_Filesystem($config["templateDirs"]);
		$this->render = new \Twig_Environment($loader, $config["env"]);

		foreach ($config["extensions"] as $ext)
			$this->render->addExtension(new $ext());
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
		$tpl = $this->render->loadTemplate($template);
		return $tpl->render($this->data);
	}
}

