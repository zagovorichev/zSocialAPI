<?php
/**
*	Фабрика соц. сетей
*
*	Alexander Zagovorichev <zagovorichev@gmail.com>
*/

require_once("vk.class.php");
require_once("ok.class.php");
require_once("fb.class.php");

class Social{
	
	public function factory($sid){
		$name = strtoupper($sid);
		return new $name();
	}

}
?>
