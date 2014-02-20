<?php

namespace server\tests\units\daemon\payloads\server;

require __DIR__ . '/../../../../runner.php';

use
	atoum,
	server\socket,
	server\daemon\payloads\server,
	server\daemon\payloads\server\client as testedClass
;

class client extends atoum
{
	public function beforeTestMethod($method)
	{
		$test = $this;

		if ($method !== 'test__construct')
		{
			$this->then = function() use ($test) { $test->getTestAdapterStorage()->resetCalls(); return $test; };
		}

		$this->mockGenerator->shuntParentClassCalls();
	}

	public function test__construct()
	{
		$this
			->given($this->newTestedInstance($socket = $this->getMockedSocket(), $server = new server()))
			->then
				->mock($socket)->call('bind')->withArguments($this->testedInstance)->once()
				->object($this->testedInstance->getServer())->isEqualTo($server)
		;
	}

	public function test__toString()
	{
		$this
			->given(
				$socket = $this->getMockedSocket(),
				$this->calling($socket)->__toString = $string = uniqid()
			)

			->if($this->newTestedInstance($socket, new server()))
			->then
				->castToString($this->testedInstance)->isEqualTo($string)
		;
	}

	public function testOnTimeout()
	{
		$this
			->given($this->newTestedInstance($socket = $this->getMockedSocket(), $server = new server()))
			->then
				->object($this->testedInstance->onTimeout($timer = new socket\timer(rand(1, PHP_INT_MAX)), $handler = function() {}))->isTestedInstance
				->mock($socket)
					->call('onTimeout')->withArguments($server, $timer, $handler)->once()
		;
	}

	public function testOnError()
	{
		$this
			->given($this->newTestedInstance($socket = $this->getMockedSocket(), $server = new server()))
			->then
				->object($this->testedInstance->onError(function() {}))->isTestedInstance
		;
	}

	public function testRemoveOnError()
	{
		$this
			->given($this->newTestedInstance($socket = $this->getMockedSocket(), $server = new server()))
			->then
				->object($this->testedInstance->removeOnError(function() {}))->isTestedInstance
		;
	}

	public function testOnPush()
	{
		$this
			->given($this->newTestedInstance($socket = $this->getMockedSocket(), $server = new server()))
			->then
				->object($this->testedInstance->onPush(new server\client\message()))->isTestedInstance
		;
	}

	public function testReadMessage()
	{
		$this
			->given($this->newTestedInstance($socket = $this->getMockedSocket(), $server = new server()))
			->then
				->object($this->testedInstance->readMessage($message1 = new server\client\message()))->isTestedInstance
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($this->testedInstance, 'readSocket'))->once()
				->object($this->testedInstance->readMessage($message2 = new server\client\message()))->isTestedInstance
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($this->testedInstance, 'readSocket'))->once()
		;
	}

	public function testReadSocket()
	{
		$this
			->given($this->newTestedInstance($socket = $this->getMockedSocket(), $server = new server()))

			->if($this->calling($socket)->read = '')
			->then
				->object($this->testedInstance->readSocket())->isTestedInstance
				->mock($socket)
					->call('read')->withArguments(2048, PHP_BINARY_READ)
						->before($this->mock($socket)->call('close')->once())
							->once()

			->if(
				$this->calling($socket)->read = uniqid(),
				$this->testedInstance->readMessage($message1 = new \mock\server\daemon\payloads\server\client\message()),
				$this->testedInstance->readMessage($message2 = new \mock\server\daemon\payloads\server\client\message()),
				$this->testedInstance->readMessage($message3 = new \mock\server\daemon\payloads\server\client\message()),
				$this->calling($message1)->readData = false
			)
			->then
				->object($this->testedInstance->readSocket())->isTestedInstance
				->mock($message1)->call('readData')->withArguments($socket)->once()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($this->testedInstance, 'readSocket'))->once()
				->object($this->testedInstance->readSocket())->isTestedInstance
				->mock($message1)->call('readData')->withArguments($socket)->twice()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($this->testedInstance, 'readSocket'))->twice()

			->if(
				$this->calling($message1)->readData = true,
				$this->calling($message2)->readData = true,
				$this->calling($message3)->readData = false
			)
			->then
				->object($this->testedInstance->readSocket())->isTestedInstance
				->mock($message1)->call('readData')->withArguments($socket)->once()
				->mock($message2)->call('readData')->withArguments($socket)->once()
				->mock($message3)->call('readData')->withArguments($socket)->once()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($this->testedInstance, 'readSocket'))->once()
				->object($this->testedInstance->readSocket())->isTestedInstance
				->mock($message1)->call('readData')->withArguments($socket)->once()
				->mock($message2)->call('readData')->withArguments($socket)->once()
				->mock($message3)->call('readData')->withArguments($socket)->twice()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($this->testedInstance, 'readSocket'))->twice()
				->object($this->testedInstance->readSocket())->isTestedInstance
				->mock($message1)->call('readData')->withArguments($socket)->once()
				->mock($message2)->call('readData')->withArguments($socket)->once()
				->mock($message3)->call('readData')->withArguments($socket)->thrice()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($this->testedInstance, 'readSocket'))->thrice()
				->object($this->testedInstance->readSocket())->isTestedInstance
				->mock($message1)->call('readData')->withArguments($socket)->once()
				->mock($message2)->call('readData')->withArguments($socket)->once()
				->mock($message3)->call('readData')->withArguments($socket)->exactly(4)
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($this->testedInstance, 'readSocket'))->exactly(4)
				->object($this->testedInstance->readSocket())->isTestedInstance
				->mock($message1)->call('readData')->withArguments($socket)->once()
				->mock($message2)->call('readData')->withArguments($socket)->once()
				->mock($message3)->call('readData')->withArguments($socket)->exactly(5)
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($this->testedInstance, 'readSocket'))->exactly(5)

			->if(
				$this->calling($message3)->readData = true
			)
			->then
				->object($this->testedInstance->readSocket())->isTestedInstance
				->mock($message3)->call('readData')->withArguments($socket)->once()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($this->testedInstance, 'readSocket'))->never()
				->object($this->testedInstance->readSocket())->isTestedInstance
				->mock($message3)->call('readData')->withArguments($socket)->once()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($this->testedInstance, 'readSocket'))->never()

			->if(
				$this->testedInstance
					->onPush($message1)
					->onPush($message3)
					->readMessage($message2 = new \mock\server\daemon\payloads\server\client\message())
			)
			->then
				->object($this->testedInstance->readSocket())->isTestedInstance
				->mock($message1)->call('readData')->withArguments($socket)->once()
				->mock($message2)->call('readData')->withArguments($socket)->once()
				->mock($message3)->call('readData')->withArguments($socket)->once()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($this->testedInstance, 'readSocket'))->once()

			->if($this->calling($socket)->read->throw = $exception = new \exception(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->exception(function() { $this->testedInstance->readSocket(); })
					->isInstanceOf('server\daemon\payloads\server\client\exception')
					->hasMessage($exception->getMessage())
					->hasCode($exception->getCode())

			->if($this->testedInstance->onError(function() use (& $onError) { $onError = true; }))
			->then
				->object($this->testedInstance->readSocket())->isTestedInstance
				->boolean($onError)->isTrue()

			->if($this->testedInstance->onError($throwException = function() use (& $exception) { throw ($exception = new \exception(uniqid(), rand(1, PHP_INT_MAX))); }))
				->exception(function() { $this->testedInstance->readSocket(); })
					->isInstanceOf('server\daemon\payloads\server\client\exception')
					->hasMessage($exception->getMessage())
					->hasCode($exception->getCode())

			->if(
				$this->testedInstance->removeOnError($throwException),
				$onError = false
			)
			->then
				->object($this->testedInstance->readSocket())->isTestedInstance
				->boolean($onError)->isTrue()
		;
	}

	public function testWriteMessage()
	{
		$this
			->given($this->newTestedInstance($socket = $this->getMockedSocket(), $server = new server()))
			->then
				->object($this->testedInstance->writeMessage($message1 = new server\client\message()))->isTestedInstance
				->mock($socket)->call('onWriteNotBlock')->withArguments($server, array($this->testedInstance, 'writeSocket'))->once()
				->object($this->testedInstance->writeMessage($message2 = new server\client\message()))->isTestedInstance
				->mock($socket)->call('onWriteNotBlock')->withArguments($server, array($this->testedInstance, 'writeSocket'))->once()
		;
	}

	public function testWriteSocket()
	{
		$this
			->given($this->newTestedInstance($socket = $this->getMockedSocket(), $server = new server()))
			->then
				->object($this->testedInstance->writeSocket())->isTestedInstance
				->mock($socket)->call('write')->never()

			->if(
				$this->testedInstance->writeMessage($message1 = new \mock\server\daemon\payloads\server\client\message()),
				$this->testedInstance->writeMessage($message2 = new \mock\server\daemon\payloads\server\client\message()),
				$this->testedInstance->writeMessage($message3 = new \mock\server\daemon\payloads\server\client\message()),
				$this->calling($message1)->writeData = false
			)
			->then
				->object($this->testedInstance->writeSocket())->isTestedInstance
				->mock($message1)->call('writeData')->withArguments($socket)->once()
				->object($this->testedInstance->writeSocket())->isTestedInstance
				->mock($message1)->call('writeData')->withArguments($socket)->twice()

			->if(
				$this->calling($message1)->writeData = true,
				$this->calling($message2)->writeData = true,
				$this->calling($message3)->writeData = false
			)
			->then
				->object($this->testedInstance->writeSocket())->isTestedInstance
				->mock($message1)->call('writeData')->withArguments($socket)->once()
				->mock($message2)->call('writeData')->withArguments($socket)->never()
				->object($this->testedInstance->writeSocket())->isTestedInstance
				->mock($message1)->call('writeData')->withArguments($socket)->once()
				->mock($message2)->call('writeData')->withArguments($socket)->once()
				->mock($message3)->call('writeData')->withArguments($socket)->never()
				->object($this->testedInstance->writeSocket())->isTestedInstance
				->mock($message1)->call('writeData')->withArguments($socket)->once()
				->mock($message2)->call('writeData')->withArguments($socket)->once()
				->mock($message3)->call('writeData')->withArguments($socket)->once()
				->object($this->testedInstance->writeSocket())->isTestedInstance
				->mock($message1)->call('writeData')->withArguments($socket)->once()
				->mock($message2)->call('writeData')->withArguments($socket)->once()
				->mock($message3)->call('writeData')->withArguments($socket)->twice()

			->if($this->calling($message3)->writeData = true)
			->then
				->object($this->testedInstance->writeSocket())->isTestedInstance
				->mock($message3)->call('writeData')->withArguments($socket)->once()
				->object($this->testedInstance->writeSocket())->isTestedInstance
				->mock($message3)->call('writeData')->withArguments($socket)->once()

			->if(
				$this->testedInstance->writeMessage($message1 = new \mock\server\daemon\payloads\server\client\message()),
				$this->calling($message1)->writeData->throw = $exception = new \exception(uniqid(), rand(1, PHP_INT_MAX))
			)
			->then
				->exception(function() { $this->testedInstance->writeSocket(); })
					->isInstanceOf('server\daemon\payloads\server\client\exception')
					->hasMessage($exception->getMessage())
					->hasCode($exception->getCode())

			->if($this->testedInstance->onError(function() use (& $onError) { $onError = true; }))
			->then
				->object($this->testedInstance->writeSocket())->isTestedInstance
				->boolean($onError)->isTrue()

			->if($this->testedInstance->onError(function() use (& $exception) { throw ($exception = new \exception(uniqid(), rand(1, PHP_INT_MAX))); }))
				->exception(function() { $this->testedInstance->writeSocket(); })
					->isInstanceOf('server\daemon\payloads\server\client\exception')
					->hasMessage($exception->getMessage())
					->hasCode($exception->getCode())
		;
	}

	public function testCloseSocket()
	{
		$this
			->given($this->newTestedInstance($socket = $this->getMockedSocket(), new server()))
			->then
				->object($this->testedInstance->closeSocket())->isTestedInstance
				->mock($socket)->call('close')->once()
		;
	}

	protected function getMockedSocket($resource = null)
	{
		$this->mockGenerator->shuntParentClassCalls();

		return new \mock\server\socket($resource ?: uniqid());
	}
}
