<?php

namespace server\tests\units\socket\manager;

require __DIR__ . '/../../../runner.php';

use
	atoum
;

class exception extends atoum
{
	public function testClass()
	{
		$this->testedClass
			->extends('runtimeException')
			->implements('server\exception')
		;
	}
}
