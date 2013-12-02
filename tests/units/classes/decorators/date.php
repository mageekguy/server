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

	public function testDecorateLog()
	{
		$this
			->given($decorator = new testedClass())

			->if($this->function->date = $date = '1976-10-06 14:25:01')
			->then
				->string($decorator->decorateLog($log = uniqid()))->isEqualTo($date . $log)
				->function('date')->wasCalledWithArguments('Y-m-d H:i:s')->once()
		;
	}
}
