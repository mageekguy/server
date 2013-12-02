<?php

namespace server\tests\units\decorators;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\decorators\eol as testedClass
;

class eol extends atoum
{
	public function testClass()
	{
		$this->testedClass->implements('server\logger\decorator');
	}

	public function testDecorateLog()
	{
		$this
			->if($decorator = new testedClass())
			->then
				->string($decorator->decorateLog($log = uniqid()))->isEqualTo($log . PHP_EOL)
		;
	}
}
