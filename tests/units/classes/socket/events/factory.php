<?php

namespace server\tests\units\socket\events;

require __DIR__ . '/../../../runner.php';

use
	atoum,
	server\socket,
	server\socket\events\factory as testedClass
;

class factory extends atoum
{
	public function testBuild()
	{
		$this
			->given($factory = new testedClass())
			->then
				->object($factory->build())->isEqualTo(new socket\events())
		;
	}
}
