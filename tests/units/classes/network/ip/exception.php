<?php

namespace server\tests\units\network\ip;

require __DIR__ . '/../../../runner.php';

use
	atoum
;

class exception extends atoum
{
	public function testClass()
	{
		$this->testedClass
			->extends('outOfBoundsException')
			->implements('server\exception')
		;
	}
}
