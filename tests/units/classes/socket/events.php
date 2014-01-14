<?php

namespace server\tests\units\socket;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\socket,
	server\socket\events as testedClass
;

class events extends atoum
{
	public function beforeTestMethod($method)
	{
		$test = $this;

		$this->then = function() use ($test) { $test->getTestAdapterStorage()->resetCalls(); return $test; };
	}

	public function test__construct()
	{
		$this
			->given($events = new testedClass())
			->then
				->boolean(isset($events->onReadNotBlock))->isFalse()
				->boolean(isset($events->onWriteNotBlock))->isFalse()
				->boolean(isset($events->onTimeout))->isFalse()
				->boolean(isset($events->{uniqid()}))->isFalse()
		;
	}

	public function test__unset()
	{
		$this
			->given($events = new testedClass())

			->when(function() use ($events) { unset($events->onReadNotBlock); })
			->then
				->boolean(isset($events->onReadNotBlock))->isFalse()
				->boolean(isset($events->onWriteNotBlock))->isFalse()

			->when(function() use ($events) { unset($events->onWriteNotBlock); })
			->then
				->boolean(isset($events->onReadNotBlock))->isFalse()
				->boolean(isset($events->onWriteNotBlock))->isFalse()

			->if($events->onReadNotBlock(function() {}))
			->when(function() use ($events) { unset($events->onReadNotBlock); })
			->then
				->boolean(isset($events->onReadNotBlock))->isFalse()
				->boolean(isset($events->onWriteNotBlock))->isFalse()

			->if($events->onWriteNotBlock(function() {}))
			->when(function() use ($events) { unset($events->onWriteNotBlock); })
			->then
				->boolean(isset($events->onReadNotBlock))->isFalse()
				->boolean(isset($events->onWriteNotBlock))->isFalse()
			->when(function() use ($events) { unset($events->{uniqid()}); })
			->then
				->boolean(isset($events->onReadNotBlock))->isFalse()
				->boolean(isset($events->onWriteNotBlock))->isFalse()
		;
	}

	public function testBind()
	{
		$this
			->given($events = new testedClass())
			->then
				->object($events->bind($this))->isIdenticalTo($events)
		;
	}

	public function testOnReadNotBlock()
	{
		$this
			->given($events = new testedClass())
			->then
				->object($events->onReadNotBlock($callable = function() {}))->isIdenticalTo($events)
				->boolean(isset($events->onReadNotBlock))->isTrue()
		;
	}

	public function testTriggerOnReadNotBlock()
	{
		$this
			->given($events = new testedClass())
			->then
				->object($events->triggerOnReadNotBlock($socket = uniqid()))->isIdenticalTo($events)
				->boolean(isset($events->onReadNotBlock))->isFalse()

			->if($events->onReadNotBlock(function($socket) use (& $socketUsed) { $socketUsed = $socket; }))
			->then
				->object($events->triggerOnReadNotBlock($socket))->isIdenticalTo($events)
				->string($socketUsed)->isEqualTo($socket)
				->boolean(isset($events->onReadNotBlock))->isFalse()

			->if(
				$events->onReadNotBlock(function($socket) use (& $socketUsed) { $socketUsed = $socket; }),
				$events->bind($this)
			)
			->then
				->object($events->triggerOnReadNotBlock($socket))->isIdenticalTo($events)
				->object($socketUsed)->isIdenticalTo($this)
				->boolean(isset($events->onReadNotBlock))->isFalse()
		;
	}

	public function testOnWriteNotBlock()
	{
		$this
			->given($events = new testedClass())
			->then
				->object($events->onWriteNotBlock($callable = function() {}))->isIdenticalTo($events)
				->boolean(isset($events->onWriteNotBlock))->isTrue()
		;
	}

	public function testTriggerOnWriteNotBlock()
	{
		$this
			->given($events = new testedClass())
			->then
				->object($events->triggerOnWriteNotBlock($socket = uniqid()))->isIdenticalTo($events)
				->boolean(isset($events->onWriteNotBlock))->isFalse()

			->if($events->onWriteNotBlock(function($socket) use (& $socketUsed) { $socketUsed = $socket; }))
			->then
				->object($events->triggerOnWriteNotBlock($socket))->isIdenticalTo($events)
				->string($socketUsed)->isEqualTo($socket)
				->boolean(isset($events->onWriteNotBlock))->isFalse()

			->if(
				$events->onWriteNotBlock(function($socket) use (& $socketUsed) { $socketUsed = $socket; }),
				$events->bind($this)
			)
			->then
				->object($events->triggerOnWriteNotBlock($socket))->isIdenticalTo($events)
				->object($socketUsed)->isIdenticalTo($this)
				->boolean(isset($events->onWriteNotBlock))->isFalse()
		;
	}

	public function testOnTimeout()
	{
		$this
			->given($events = new testedClass())
			->then
				->object($events->onTimeout(new socket\timer(10), $callable = function() {}))->isIdenticalTo($events)
				->boolean(isset($events->onTimeout))->isTrue()
		;
	}

	public function testTriggerOnTimeout()
	{
		$this
			->given($events = new testedClass())
			->then
				->variable($events->triggerOnTimeout($socket = uniqid()))->isNull()

			->if(
				$events->onTimeout($timer = new \mock\server\socket\timer(10), function($socket) use (& $socketTimeout) { $socketTimeout = $socket; })
			)
			->then
				->integer($events->triggerOnTimeout($socket))->isEqualTo(10)
				->variable($socketTimeout)->isNull()

			->if($this->calling($timer)->getRemaining = 5)
			->then
				->integer($events->triggerOnTimeout($socket))->isEqualTo(5)
				->variable($socketTimeout)->isNull()

			->if($this->calling($timer)->getRemaining = 1)
			->then
				->integer($events->triggerOnTimeout($socket))->isEqualTo(1)
				->variable($socketTimeout)->isNull()

			->if($this->calling($timer)->getRemaining = 0)
			->then
				->integer($events->triggerOnTimeout($socket))->isZero()
				->string($socketTimeout)->isEqualTo($socket)
				->integer($events->triggerOnTimeout($socket))->isZero()
				->string($socketTimeout)->isEqualTo($socket)

			->if(
				$events->onWriteNotBlock(function($socket) use (& $socketUsed) { $socketUsed = $socket; }),
				$events->bind($this)
			)
			->then
				->integer($events->triggerOnTimeout($socket))->isZero()
				->object($socketTimeout)->isIdenticalTo($this)
		;
	}
}
