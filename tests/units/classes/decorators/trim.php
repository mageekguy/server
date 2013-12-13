<?php

namespace server\tests\units\decorators;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\decorators\trim as testedClass
;

class trim extends atoum
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
			->if($decorator = new testedClass())
			->then
				->string($decorator->decorateLog($log = uniqid()))->isEqualTo($log)
				->string($decorator->decorateLog(PHP_EOL . $log . PHP_EOL))->isEqualTo($log)
				->string($decorator->decorateLog(' ' . PHP_EOL . ' ' . $log . ' ' . PHP_EOL . ' '))->isEqualTo($log)
				->string($decorator->decorateLog("\t" . ' ' . PHP_EOL . ' ' . $log . "\t" . ' ' . PHP_EOL . ' '))->isEqualTo($log)
		;
	}
}
