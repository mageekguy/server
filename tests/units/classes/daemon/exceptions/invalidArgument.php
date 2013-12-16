<?php

namespace server\tests\units\daemon\exceptions;

require __DIR__ . '/../../../runner.php';

use
	atoum
;

class invalidArgument extends atoum
{
	public function testClass()
	{
		$this->testedClass
			->extends('atoum\exceptions\logic\invalidArgument')
			->implements('server\exception')
		;
	}
}
