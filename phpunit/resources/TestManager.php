<?php
require_once 'core/EntityManager.php';

class TestManager extends BaseTestEntityManager
{

	public function getDefineClass()
	{
		return $this->defineClass();
	}


}
?>