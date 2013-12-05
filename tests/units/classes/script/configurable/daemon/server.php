<?php

namespace server\tests\units\script\configurable\daemon;

require __DIR__ . '/../../../../runner.php';

use
	atoum,
	server\unix,
	server\socket,
	server\network,
	server\script\configurable\daemon,
	server\script\configurable\daemon\server as testedClass
;

class server extends atoum
{
	public function testClass()
	{
		$this->testedClass->extends('server\script\configurable\daemon');
	}
}
