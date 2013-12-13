<?php

namespace server\tests\units\decorators;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\decorators\prefix as testedClass
;

class prefix extends atoum
{
	public function testClass()
	{
		$this->testedClass->implements('server\logger\decorator');
	}

	public function testPrepareToDecorateLog()
	{
		$this
			->if($decorator = new testedClass(uniqid()))
			->then
				->object($decorator->prepareToDecorateLog())->isIdenticalTo($decorator)
		;
	}

	public function testDecorateLog()
	{
		$this
			->if($decorator = new testedClass($prefix = uniqid()))
			->then
				->string($decorator->decorateLog($log = uniqid()))->isEqualTo($prefix . $log)
		;
	}
}
