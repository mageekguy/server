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
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), $server = new server()))
			->then
				->mock($socket)->call('bind')->withArguments($client)->once()
				->object($client->getServer())->isEqualTo($server)
		;
	}

	public function test__toString()
	{
		$this
			->given(
				$socket = new \mock\server\socket(uniqid()),
				$this->calling($socket)->__toString = $string = uniqid(),
				$client = new testedClass($socket, new server())
			)
			->then
				->castToString($client)->isEqualTo($string)
		;
	}

	public function testOnTimeout()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), $server = new server()))
			->then
				->object($client->onTimeout($timer = new socket\timer(rand(1, PHP_INT_MAX)), $handler = function() {}))->isIdenticalTo($client)
				->mock($socket)
					->call('onTimeout')->withArguments($server, $timer, $handler)->once()
		;
	}

	public function testOnError()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), $server = new server()))
			->then
				->object($client->onError(function() {}))->isIdenticalTo($client)
		;
	}

	public function testReadMessage()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), $server = new server()))
			->then
				->object($client->readMessage($message1 = new server\client\message()))->isIdenticalTo($client)
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($client, 'readSocket'))->once()
				->object($client->readMessage($message2 = new server\client\message()))->isIdenticalTo($client)
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($client, 'readSocket'))->once()
		;
	}

	public function testReadSocket()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), $server = new server()))
			->then
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($socket)->call('read')->withArguments(2048, PHP_NORMAL_READ)->once()

			->if(
				$client->readMessage($message1 = new \mock\server\daemon\payloads\server\client\message()),
				$client->readMessage($message2 = new \mock\server\daemon\payloads\server\client\message()),
				$client->readMessage($message3 = new \mock\server\daemon\payloads\server\client\message()),
				$this->calling($message1)->readSocket = false
			)
			->then
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->once()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($client, 'readSocket'))->once()
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->twice()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($client, 'readSocket'))->twice()

			->if(
				$this->calling($message1)->readSocket = true,
				$this->calling($message2)->readSocket = true,
				$this->calling($message3)->readSocket = false
			)
			->then
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->once()
				->mock($message2)->call('readSocket')->withArguments($socket)->never()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($client, 'readSocket'))->once()
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->once()
				->mock($message2)->call('readSocket')->withArguments($socket)->once()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($client, 'readSocket'))->twice()
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->once()
				->mock($message2)->call('readSocket')->withArguments($socket)->once()
				->mock($message3)->call('readSocket')->withArguments($socket)->once()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($client, 'readSocket'))->thrice()
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->once()
				->mock($message2)->call('readSocket')->withArguments($socket)->once()
				->mock($message3)->call('readSocket')->withArguments($socket)->twice()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($client, 'readSocket'))->exactly(4)
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->once()
				->mock($message2)->call('readSocket')->withArguments($socket)->once()
				->mock($message3)->call('readSocket')->withArguments($socket)->thrice()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($client, 'readSocket'))->exactly(5)

			->if(
				$this->calling($message3)->readSocket = true
			)
			->then
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message3)->call('readSocket')->withArguments($socket)->once()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($client, 'readSocket'))->never()
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message3)->call('readSocket')->withArguments($socket)->once()
				->mock($socket)->call('onReadNotBlock')->withArguments($server, array($client, 'readSocket'))->never()

			->if($this->calling($socket)->read = '')
			->then
				->exception(function() use ($client) { $client->readSocket(); })
					->isInstanceOf('server\daemon\payloads\server\client\exception')
					->hasMessage('Socket is closed')

			->if($this->calling($socket)->read->throw = $exception = new \exception(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->exception(function() use ($client) { $client->readSocket(); })
					->isInstanceOf('server\daemon\payloads\server\client\exception')
					->hasMessage($exception->getMessage())
					->hasCode($exception->getCode())

			->if($client->onError(function() use (& $onError) { $onError = true; }))
			->then
				->object($client->readSocket())->isIdenticalTo($client)
				->boolean($onError)->isTrue()
		;
	}

	public function testWriteMessage()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), $server = new server()))
			->then
				->object($client->writeMessage($message1 = new server\client\message()))->isIdenticalTo($client)
				->mock($socket)->call('onWriteNotBlock')->withArguments($server, array($client, 'writeSocket'))->once()
				->object($client->writeMessage($message2 = new server\client\message()))->isIdenticalTo($client)
				->mock($socket)->call('onWriteNotBlock')->withArguments($server, array($client, 'writeSocket'))->once()
		;
	}

	public function testWriteSocket()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), $server = new server()))
			->then
				->object($client->writeSocket())->isIdenticalTo($client)
				->mock($socket)->call('write')->never()

			->if(
				$client->writeMessage($message1 = new \mock\server\daemon\payloads\server\client\message()),
				$client->writeMessage($message2 = new \mock\server\daemon\payloads\server\client\message()),
				$client->writeMessage($message3 = new \mock\server\daemon\payloads\server\client\message()),
				$this->calling($message1)->writeSocket = false
			)
			->then
				->object($client->writeSocket())->isIdenticalTo($client)
				->mock($message1)->call('writeSocket')->withArguments($socket)->once()
				->object($client->writeSocket())->isIdenticalTo($client)
				->mock($message1)->call('writeSocket')->withArguments($socket)->twice()

			->if(
				$this->calling($message1)->writeSocket = true,
				$this->calling($message2)->writeSocket = true,
				$this->calling($message3)->writeSocket = false
			)
			->then
				->object($client->writeSocket())->isIdenticalTo($client)
				->mock($message1)->call('writeSocket')->withArguments($socket)->once()
				->mock($message2)->call('writeSocket')->withArguments($socket)->never()
				->object($client->writeSocket())->isIdenticalTo($client)
				->mock($message1)->call('writeSocket')->withArguments($socket)->once()
				->mock($message2)->call('writeSocket')->withArguments($socket)->once()
				->mock($message3)->call('writeSocket')->withArguments($socket)->never()
				->object($client->writeSocket())->isIdenticalTo($client)
				->mock($message1)->call('writeSocket')->withArguments($socket)->once()
				->mock($message2)->call('writeSocket')->withArguments($socket)->once()
				->mock($message3)->call('writeSocket')->withArguments($socket)->once()
				->object($client->writeSocket())->isIdenticalTo($client)
				->mock($message1)->call('writeSocket')->withArguments($socket)->once()
				->mock($message2)->call('writeSocket')->withArguments($socket)->once()
				->mock($message3)->call('writeSocket')->withArguments($socket)->twice()

			->if($this->calling($message3)->writeSocket = true)
			->then
				->object($client->writeSocket())->isIdenticalTo($client)
				->mock($message3)->call('writeSocket')->withArguments($socket)->once()
				->object($client->writeSocket())->isIdenticalTo($client)
				->mock($message3)->call('writeSocket')->withArguments($socket)->once()

			->if(
				$client->writeMessage($message1 = new \mock\server\daemon\payloads\server\client\message()),
				$this->calling($message1)->writeSocket->throw = $exception = new \exception(uniqid(), rand(1, PHP_INT_MAX))
			)
			->then
				->exception(function() use ($client) { $client->writeSocket(); })
					->isInstanceOf('server\daemon\payloads\server\client\exception')
					->hasMessage($exception->getMessage())
					->hasCode($exception->getCode())

			->if($client->onError(function() use (& $onError) { $onError = true; }))
			->then
				->object($client->writeSocket())->isIdenticalTo($client)
				->boolean($onError)->isTrue()
		;
	}

	public function testCloseSocket()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), new server()))
			->then
				->object($client->closeSocket())->isIdenticalTo($client)
				->mock($socket)->call('close')->once()
		;
	}
}
