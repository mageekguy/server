<?php

namespace server\tests\units\decorators;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\decorators\id as testedClass
;

class id extends atoum
{
	public function testClass()
	{
		$this->testedClass->implements('server\logger\decorator');
	}

	public function testPrepareToDecorateLog()
	{
		$this
			->given($decorator = new testedClass())

			->if(
				$this->function->uniqid[1] = $id1 = uniqid(),
				$this->function->uniqid[2] = uniqid()
			)
			->then
				->object($decorator->prepareToDecorateLog())->isIdenticalTo($decorator)
				->string($decorator->decorateLog($log1 = uniqid()))->isEqualTo($id1 . $log1)
				->string($decorator->decorateLog($log2 = uniqid()))->isEqualTo($id1 . $log2)
				->function('uniqid')->wasCalledWithArguments('', true)->once()
		;
	}

	public function testDecorateLog()
	{
		$this
			->given($decorator = new testedClass())

			->if(
				$this->function->uniqid[1] = $id1 = uniqid(),
				$this->function->uniqid[2] = $id2 = uniqid()
			)
			->then
				->string($decorator->decorateLog($log1 = uniqid()))->isEqualTo($id1 . $log1)
				->string($decorator->decorateLog($log2 = uniqid()))->isEqualTo($id2 . $log2)
				->function('uniqid')->wasCalledWithArguments('', true)->twice()
		;
	}
}
