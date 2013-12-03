<?php

namespace server\tests\units\network;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\network\port as testedClass
;

class port extends atoum
{
	public function testClassConstants()
	{
		$this
			->integer(testedClass::min)->isEqualTo(1)
			->integer(testedClass::max)->isEqualTo(65536)
		;
	}

	public function test__construct()
	{
		$this
			->if($port = new testedClass($value = rand(testedClass::min, testedClass::max)))
			->then
				->castToString($port)->isEqualTo($value)
				->exception(function() use (& $value) { new testedClass($value = rand(- PHP_INT_MAX, 0)); })
					->isInstanceOf('server\network\port\exception')
					->hasMessage('\'' . $value . '\' is not a valid port')
		;
	}
}
