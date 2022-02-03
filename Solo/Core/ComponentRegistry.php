<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core;

class ComponentRegistry
{
	private static ?ComponentRegistry $instance = null;

	private array $registry = [];

	private function __construct()
	{
	}  // Защищаем от создания через new

	private function __clone()
	{
	}  // Защищаем от создания через клонирование

	public function __wakeup()
	{
	}

	public static function getInstance(): ?ComponentRegistry
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function setObject($key, $object)
	{
		$this->registry[$key] = $object;
	}

	public function getObject($key)
	{
		if (array_key_exists($key, $this->registry))
			return $this->registry[$key];
		else
			return null;
	}

	/**
	 * Возвращает экземпляр компонента
	 * При создании компонента можно передать в его конструктор
	 * дополнительные параметры
	 * например: ComponentRegistry::getInstance()->getComponent("comp_name", $param1, $param2);
	 *
	 * @param string $componentName Имя компонента, соотвествующее записи в конфигураторе
	 *
	 * @return object
	 * @throws \ReflectionException
	 * @throws \RuntimeException
	 */
	public function getComponent($componentName): object
	{
		if (isset($this->registry[$componentName]))
			return $this->registry[$componentName];

		$config = Configurator::get("components:{$componentName}");
		if (!isset($config["@class"]))
			throw new \RuntimeException("Component configuration must have a 'class' option");

		// имя класса создаваемого компонента
		$className = $config["@class"];
		unset($config["@class"]);

		// параметры, передаваемые в конструктор
		$ctor = $config["@constructor"] ?? null;
		unset($config["@constructor"]);

		if ($ctor !== null)
		{
			$object = new \ReflectionClass($className);
			$component = $object->newInstanceArgs($ctor);
		}
		else
		{
			// Если переданы доп. параметры, то передаем их в конструктор
			if (func_num_args() > 1)
			{
				$args = func_get_args();
				unset($args[0]);
				$object = new \ReflectionClass($className);
				$component = $object->newInstanceArgs($args);
			}
			else
			{
				$component = new $className();
			}
		}

		// теперь публичным свойствам экземпляра назначим значения из конфига
		foreach($config as $key => $value)
		{
			if (!property_exists($component, $key))
				continue;
			$component->$key = $value;
		}

		// инициализация компонента
		$component->initComponent();

		$this->registry[$componentName] = $component;
		return $component;
	}
}

