<?php

namespace server\tests\units\decorators;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\decorators\date as testedClass
;

class date extends atoum
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
				$this->function->date[1] = $date1 = '1976-10-06 14:25:01',
				$this->function->date[2] = '1976-10-06 14:25:02'
			)
			->then
				->object($decorator->prepareToDecorateLog())->isIdenticalTo($decorator)
				->string($decorator->decorateLog($log1 = uniqid()))->isEqualTo($date1 . $log1)
				->string($decorator->decorateLog($log2 = uniqid()))->isEqualTo($date1 . $log2)
				->function('date')->wasCalledWithArguments('Y-m-d H:i:s')->once()
		;
	}

	public function testDecorateLog()
	{
		$this
			->given($decorator = new testedClass())

			->if(
				$this->function->date[1] = $date1 = '1976-10-06 14:25:01',
				$this->function->date[2] = $date2 = '1976-10-06 14:25:02'
			)
			->then
				->string($decorator->decorateLog($log1 = uniqid()))->isEqualTo($date1 . $log1)
				->string($decorator->decorateLog($log2 = uniqid()))->isEqualTo($date2 . $log2)
				->function('date')->wasCalledWithArguments('Y-m-d H:i:s')->twice()
		;
	}
}
