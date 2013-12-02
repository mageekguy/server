<?php

namespace server\tests\units\socket;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\socket\events as testedClass
;

class events extends atoum
{
	public function test__construct()
	{
		$this
			->given($events = new testedClass())
			->then
				->boolean(isset($events->onRead))->isFalse()
				->boolean(isset($events->onWrite))->isFalse()
				->boolean(isset($events->{uniqid()}))->isFalse()
		;
	}

	public function test__unset()
	{
		$this
			->given($events = new testedClass())

			->when(function() use ($events) { unset($events->onRead); })
			->then
				->boolean(isset($events->onRead))->isFalse()
				->boolean(isset($events->onWrite))->isFalse()

			->when(function() use ($events) { unset($events->onWrite); })
			->then
				->boolean(isset($events->onRead))->isFalse()
				->boolean(isset($events->onWrite))->isFalse()

			->if($events->onRead(function() {}))
			->when(function() use ($events) { unset($events->onRead); })
			->then
				->boolean(isset($events->onRead))->isFalse()
				->boolean(isset($events->onWrite))->isFalse()

			->if($events->onWrite(function() {}))
			->when(function() use ($events) { unset($events->onWrite); })
			->then
				->boolean(isset($events->onRead))->isFalse()
				->boolean(isset($events->onWrite))->isFalse()
			->when(function() use ($events) { unset($events->{uniqid()}); })
			->then
				->boolean(isset($events->onRead))->isFalse()
				->boolean(isset($events->onWrite))->isFalse()
		;
	}

	public function testOnRead()
	{
		$this
			->given($events = new testedClass())
			->then
				->object($events->onRead($callable = function() {}))->isIdenticalTo($events)
				->boolean(isset($events->onRead))->isTrue()
		;
	}

	public function testTriggerOnRead()
	{
		$this
			->given($events = new testedClass())
			->then
				->object($events->triggerOnRead($socket = uniqid()))->isIdenticalTo($events)

			->if($events->onRead(function($socket) use (& $socketUsed) { $socketUsed = $socket; }))
			->then
				->object($events->triggerOnRead($socket))->isIdenticalTo($events)
				->string($socketUsed)->isEqualTo($socket)
		;
	}

	public function testOnWrite()
	{
		$this
			->given($events = new testedClass())
			->then
				->object($events->onWrite($callable = function() {}))->isIdenticalTo($events)
				->boolean(isset($events->onWrite))->isTrue()
		;
	}

	public function testTriggerOnWrite()
	{
		$this
			->given($events = new testedClass())
			->then
				->object($events->triggerOnWrite($socket = uniqid()))->isIdenticalTo($events)

			->if($events->onWrite(function($socket) use (& $socketUsed) { $socketUsed = $socket; }))
			->then
				->object($events->triggerOnWrite($socket))->isIdenticalTo($events)
				->string($socketUsed)->isEqualTo($socket)
		;
	}
}
