<?php

namespace server\tests\units\script\configurable\daemon\controller;

require __DIR__ . '/../../../../../runner.php';

use
	atoum
;

class exception extends atoum
{
	public function testClass()
	{
		$this->testedClass->extends('runtimeException');
	}
}
