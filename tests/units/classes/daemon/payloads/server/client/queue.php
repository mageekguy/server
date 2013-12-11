<?php

namespace server\tests\units\daemon\payloads\server\client;

require __DIR__ . '/../../../../../runner.php';

use
	atoum,
	server\daemon\payloads\server\client
;

class queue extends atoum
{
	public function beforeTestMethod($method)
	{
		$testedClassName = $this->getTestedClassName();

		$this->testedClassInstance = function() use ($testedClassName) { return new $testedClassName(); };
	}

	public function test__construct()
	{
		$this
			->if($queue = $this->testedClassInstance())
			->then
				->sizeof($queue)->isZero()
		;
	}

	public function testAddMessage()
	{
		$this
			->if($queue = $this->testedClassInstance())
			->then
				->object($queue->addMessage($message1 = new client\message()))->isIdenticalTo($queue)
				->sizeof($queue)->isEqualTo(1)
				->object($queue->addMessage($message2 = new client\message()))->isIdenticalTo($queue)
				->sizeof($queue)->isEqualTo(2)
		;
	}

	public function testShiftMessage()
	{
		$this
			->if($queue = $this->testedClassInstance())
			->then
				->variable($queue->shiftMessage())->isNull()

			->if(
				$queue->addMessage($message1 = new client\message()),
				$queue->addMessage($message2 = new client\message()),
				$queue->addMessage($message3 = new client\message())
			)
			->then
				->object($queue->shiftMessage())->isIdenticalTo($message1)
				->sizeof($queue)->isEqualTo(2)
				->object($queue->shiftMessage())->isIdenticalTo($message2)
				->sizeof($queue)->isEqualTo(1)
				->object($queue->shiftMessage())->isIdenticalTo($message3)
				->sizeof($queue)->isEqualTo(0)
				->variable($queue->shiftMessage())->isNull()
				->sizeof($queue)->isEqualTo(0)
		;
	}
}
