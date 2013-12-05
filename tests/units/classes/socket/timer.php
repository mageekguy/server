<?php

namespace server\tests\units\socket;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\socket\timer as testedClass
;

class timer extends atoum
{
	public function test__construct()
	{
		$this
			->if($timer = new testedClass($duration = 10))
			->then
				->variable($timer->getDuration())->isEqualTo($duration)
				->variable($timer->getStart())->isNull()
				->variable($timer->getRemaining())->isNull()
		;
	}

	public function testStart()
	{
		$this
			->if(
				$timer = new testedClass($duration = 10),
				$this->function->time = $time = 20
			)
			->then
				->object($timer->start())->isEqualTo($timer)
				->integer($timer->getStart())->isEqualTo($time)
				->integer($timer->getRemaining())->isEqualTo($duration)
		;
	}

	public function testGetReamining()
	{
		$this
			->if($timer = new testedClass($duration = 10))
			->then
				->variable($timer->getRemaining())->isNull()

			->if(
				$this->function->time = $time = 20,
				$timer->start()
			)
			->then
				->integer($timer->getRemaining())->isEqualTo($duration)

			->if($this->function->time = $time = 25)
			->then
				->integer($timer->getRemaining())->isEqualTo(5)

			->if($this->function->time = $time = 29)
			->then
				->integer($timer->getRemaining())->isEqualTo(1)

			->if($this->function->time = $time = 30)
			->then
				->integer($timer->getRemaining())->isZero()

			->if($this->function->time = $time = 34)
			->then
				->integer($timer->getRemaining())->isZero()

			->if(
				$this->function->time = $time = 40,
				$timer->start()
			)
			->then
				->integer($timer->getRemaining())->isEqualTo($duration)

			->if($this->function->time = $time = 45)
			->then
				->integer($timer->getRemaining())->isEqualTo(5)
		;
	}
}
