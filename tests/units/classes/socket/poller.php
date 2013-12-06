<?php

namespace server\tests\units\socket;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\socket,
	mock\server as mock,
	server\socket\poller as testedClass
;

class poller extends atoum
{
	public function beforeTestMethod($method)
	{
		$test = $this;

		$this->then = function() use ($test) { $test->getTestAdapterStorage()->resetCalls(); return $test; };
	}

	public function testClass()
	{
		$this->testedClass->implements('server\socket\poller\definition');
	}

	public function test__construct()
	{
		$this
			->given($poller = new testedClass())
			->then
				->object($poller->getSocketManager())->isEqualTo(new socket\manager())
				->object($poller->getSocketEventsFactory())->isEqualTo(new socket\events\factory())
		;
	}

	public function testSetSocketManager()
	{
		$this
			->given($poller = new testedClass())
			->then
				->object($poller->setSocketManager($socketManager = new socket\manager()))->isIdenticalTo($poller)
				->object($poller->getSocketManager())->isIdenticalTo($socketManager)
				->object($poller->setSocketManager())->isIdenticalTo($poller)
				->object($poller->getSocketManager())
					->isNotIdenticalTo($socketManager)
					->isEqualTo(new socket\manager())
		;
	}

	public function testSetSocketEventsFactory()
	{
		$this
			->given($poller = new testedClass())
			->then
				->object($poller->setSocketEventsFactory($socketEventsFactory = new socket\events\factory()))->isIdenticalTo($poller)
				->object($poller->getSocketEventsFactory())->isIdenticalTo($socketEventsFactory)
				->object($poller->setSocketEventsFactory())->isIdenticalTo($poller)
				->object($poller->getSocketEventsFactory())
					->isNotIdenticalTo($socketEventsFactory)
					->isEqualTo(new socket\events\factory())
		;
	}

	public function testPollSocket()
	{
		$this
			->given(
				$poller = new testedClass(),
				$poller->setSocketEventsFactory($socketEventsFactory = new mock\socket\events\factory()),
				$this->calling($socketEventsFactory)->build = $socketEvents = new socket\events()
			)
			->then
				->object($poller->pollSocket($socket1 = uniqid()))->isIdenticalTo($socketEvents)
		;
	}

	public function testPollSockets()
	{
		$this
			->given(
				$poller = new testedClass(),
				$poller
					->setSocketManager($socketManager = new mock\socket\manager())
					->setSocketEventsFactory($socketEventsFactory = new mock\socket\events\factory()),
				$this->function->is_resource = true
			)
			->then
				->object($poller->waitSockets($timeout = rand(1, PHP_INT_MAX)))->isIdenticalTo($poller)

			->given(
				$this->calling($socketEventsFactory)->build = $socketEvents = new \mock\server\socket\events(),
				$this->calling($socketEvents)->__isset = false
			)

			->if($poller->pollSocket($socket1 = uniqid()))
			->then
				->object($poller->waitSockets($timeout))->isIdenticalTo($poller)
				->mock($socketManager)->call('select')->withArguments(array($socket1), array(), array(), $timeout)->never()
				->mock($socketEvents)->call('triggerOnRead')->withArguments($socket1)->never()
				->mock($socketEvents)->call('triggerOnWrite')->withArguments($socket1)->never()

			->if(
				$this->calling($socketEvents)->__isset = function($event) { return ($event == 'onRead' || $event == 'onWrite'); },
				$this->calling($socketManager)->pollSockets = function(& $read, & $write) { $read = $write = array(); }
			)
			->then
				->object($poller->waitSockets($timeout))->isIdenticalTo($poller)
				->mock($socketManager)->call('pollSockets')->withArguments(array($socket1), array($socket1), array(), $timeout)->once()
				->mock($socketEvents)->call('triggerOnRead')->withArguments($socket1)->never()
				->mock($socketEvents)->call('triggerOnWrite')->withArguments($socket1)->never()

			->if(
				$this->calling($socketManager)->pollSockets = function(& $read) use ($socket1) { $read = array($socket1); $write = array($socket1); },
				$this->calling($socketEvents)->triggerOnRead->returnThis(),
				$this->calling($socketEvents)->triggerOnWrite->returnThis()
			)
			->then
				->object($poller->waitSockets($timeout))->isIdenticalTo($poller)
				->mock($socketManager)->call('pollSockets')->withArguments(array($socket1), array($socket1), array(), $timeout)->once()
				->mock($socketEvents)
					->call('triggerOnRead')
						->withArguments($socket1)
							->once()
				->mock($socketEvents)
					->call('triggerOnWrite')
						->withArguments($socket1)
							->once()

			->if($this->calling($socketManager)->pollSockets->throw = $exception = new \exception(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->exception(function() use ($poller) { $poller->waitSockets(rand(1, PHP_INT_MAX)); })
					->isInstanceOf('server\socket\poller\exception')
					->hasCode($exception->getCode())
					->hasMessage($exception->getMessage())
		;
	}
}
