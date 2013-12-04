<?php

namespace server\tests\units\script\configurable\daemon\server;

require __DIR__ . '/../../../../../runner.php';

use
	atoum
;

class exception extends atoum
{
	public function testClass()
	{
		$this->testedClass->extends('server\script\configurable\daemon\exception');
	}
}
