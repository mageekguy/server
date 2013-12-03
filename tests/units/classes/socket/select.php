<?php

namespace server\tests\units\socket;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\socket,
	mock\server as mock,
	server\socket\select as testedClass
;

class select extends atoum
{
	public function beforeTestMethod($method)
	{
		$test = $this;

		$this->then = function() use ($test) { $test->getTestAdapterStorage()->resetCalls(); return $test; };
	}

	public function test__construct()
	{
		$this
			->given($select = new testedClass())
			->then
				->array($select->getSockets())->isEmpty()
				->object($select->getSocketManager())->isEqualTo(new socket\manager())
				->object($select->getSocketEventsFactory())->isEqualTo(new socket\events\factory())
		;
	}

	public function testSetSocketManager()
	{
		$this
			->given($select = new testedClass())
			->then
				->object($select->setSocketManager($socketManager = new socket\manager()))->isIdenticalTo($select)
				->object($select->getSocketManager())->isIdenticalTo($socketManager)
				->object($select->setSocketManager())->isIdenticalTo($select)
				->object($select->getSocketManager())
					->isNotIdenticalTo($socketManager)
					->isEqualTo(new socket\manager())
		;
	}

	public function testSetSocketEventsFactory()
	{
		$this
			->given($select = new testedClass())
			->then
				->object($select->setSocketEventsFactory($socketEventsFactory = new socket\events\factory()))->isIdenticalTo($select)
				->object($select->getSocketEventsFactory())->isIdenticalTo($socketEventsFactory)
				->object($select->setSocketEventsFactory())->isIdenticalTo($select)
				->object($select->getSocketEventsFactory())
					->isNotIdenticalTo($socketEventsFactory)
					->isEqualTo(new socket\events\factory())
		;
	}

	public function testSocket()
	{
		$this
			->given(
				$select = new testedClass(),
				$select->setSocketEventsFactory($socketEventsFactory = new mock\socket\events\factory()),
				$this->calling($socketEventsFactory)->build = $socketEvents = new socket\events()
			)
			->then
				->object($select->socket($socket1 = uniqid()))->isIdenticalTo($socketEvents)
				->array($select->getSockets())->isEqualTo(array($socket1))
		;
	}

	public function testWait()
	{
		$this
			->given(
				$select = new testedClass(),
				$select
					->setSocketManager($socketManager = new mock\socket\manager())
					->setSocketEventsFactory($socketEventsFactory = new mock\socket\events\factory()),
				$this->function->is_resource = true
			)
			->then
				->object($select->wait($timeout = rand(1, PHP_INT_MAX)))->isIdenticalTo($select)

			->given(
				$this->calling($socketEventsFactory)->build = $socketEvents = new \mock\server\socket\events(),
				$this->calling($socketEvents)->__isset = false
			)

			->if($select->socket($socket1 = uniqid()))
			->then
				->object($select->wait($timeout))->isIdenticalTo($select)
				->mock($socketManager)->call('select')->withArguments(array($socket1), array(), array(), $timeout)->never()
				->mock($socketEvents)->call('triggerOnRead')->withArguments($socket1)->never()
				->mock($socketEvents)->call('triggerOnWrite')->withArguments($socket1)->never()

			->if(
				$this->calling($socketEvents)->__isset = true,
				$this->calling($socketManager)->select = function(& $read, & $write) { $read = $write = array(); }
			)
			->then
				->object($select->wait($timeout))->isIdenticalTo($select)
				->mock($socketManager)->call('select')->withArguments(array($socket1), array($socket1), array(), $timeout)->once()
				->mock($socketEvents)->call('triggerOnRead')->withArguments($socket1)->never()
				->mock($socketEvents)->call('triggerOnWrite')->withArguments($socket1)->never()

			->if(
				$this->calling($socketManager)->select = function(& $read) use ($socket1) { $read = array($socket1); $write = array($socket1); },
				$this->calling($socketEvents)->triggerOnRead->returnThis(),
				$this->calling($socketEvents)->triggerOnWrite->returnThis()
			)
			->then
				->object($select->wait($timeout))->isIdenticalTo($select)
				->mock($socketManager)->call('select')->withArguments(array($socket1), array($socket1), array(), $timeout)->once()
				->mock($socketEvents)
					->call('triggerOnRead')
						->withArguments($socket1)
							->before($this->mock($socketEvents)->call('__unset')->withArguments('onRead')->once())
								->once()
				->mock($socketEvents)
					->call('triggerOnWrite')
						->withArguments($socket1)
							->before($this->mock($socketEvents)->call('__unset')->withArguments('onWrite')->once())
								->once()

			->if($this->calling($socketManager)->select->throw = $exception = new \exception())
			->then
				->exception(function() use ($select) { $select->wait(rand(1, PHP_INT_MAX)); })
					->isIdenticalTo($exception)
		;
	}
}
