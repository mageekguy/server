<?php

namespace server\tests\units\fs;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\fs\path as testedClass
;

class path extends atoum
{
	public function testClass()
	{
		$this->testedClass->implements('server\socket\name');
	}

	public function test__construct()
	{
		$this
			->if($path = new testedClass($value = uniqid()))
			->then
				->castToString($path)->isEqualTo($value)
		;
	}
}
