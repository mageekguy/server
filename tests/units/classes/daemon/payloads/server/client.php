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
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), new \mock\server\socket\poller\definition()))
			->then
				->mock($socket)->call('bind')->withArguments($client)->once()
		;
	}

	public function test__toString()
	{
		$this
			->given(
				$socket = new \mock\server\socket(uniqid()),
				$this->calling($socket)->__toString = $string = uniqid(),
				$client = new testedClass($socket, new \mock\server\socket\poller\definition())
			)
			->then
				->castToString($client)->isEqualTo($string)
		;
	}

	public function testOnTimeout()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), $poller = new \mock\server\socket\poller\definition()))
			->then
				->object($client->onTimeout($timer = new socket\timer(rand(1, PHP_INT_MAX)), $handler = function() {}))->isIdenticalTo($client)
				->mock($socket)
					->call('onTimeout')->withArguments($poller, $timer, $handler)->once()
		;
	}

	public function testReadMessage()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), $poller = new \mock\server\socket\poller\definition()))
			->then
				->object($client->readMessage($message1 = new server\client\message()))->isIdenticalTo($client)
				->mock($socket)->call('onRead')->withArguments($poller, array($client, 'readSocket'))->once()
				->object($client->readMessage($message2 = new server\client\message()))->isIdenticalTo($client)
				->mock($socket)->call('onRead')->withArguments($poller, array($client, 'readSocket'))->once()
		;
	}

	public function testReadSocket()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), $poller = new \mock\server\socket\poller\definition()))
			->then
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($socket)->call('read')->never()

			->if(
				$client->readMessage($message1 = new \mock\server\daemon\payloads\server\client\message()),
				$client->readMessage($message2 = new \mock\server\daemon\payloads\server\client\message()),
				$client->readMessage($message3 = new \mock\server\daemon\payloads\server\client\message()),
				$this->calling($message1)->readSocket = false
			)
			->then
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->once()
				->mock($socket)->call('onRead')->withArguments($poller, array($client, 'readSocket'))->once()
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->twice()
				->mock($socket)->call('onRead')->withArguments($poller, array($client, 'readSocket'))->twice()

			->if(
				$this->calling($message1)->readSocket = true,
				$this->calling($message2)->readSocket = true,
				$this->calling($message3)->readSocket = false
			)
			->then
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->once()
				->mock($message2)->call('readSocket')->withArguments($socket)->never()
				->mock($socket)->call('onRead')->withArguments($poller, array($client, 'readSocket'))->once()
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->once()
				->mock($message2)->call('readSocket')->withArguments($socket)->once()
				->mock($socket)->call('onRead')->withArguments($poller, array($client, 'readSocket'))->twice()
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->once()
				->mock($message2)->call('readSocket')->withArguments($socket)->once()
				->mock($message3)->call('readSocket')->withArguments($socket)->once()
				->mock($socket)->call('onRead')->withArguments($poller, array($client, 'readSocket'))->thrice()
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->once()
				->mock($message2)->call('readSocket')->withArguments($socket)->once()
				->mock($message3)->call('readSocket')->withArguments($socket)->twice()
				->mock($socket)->call('onRead')->withArguments($poller, array($client, 'readSocket'))->exactly(4)
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message1)->call('readSocket')->withArguments($socket)->once()
				->mock($message2)->call('readSocket')->withArguments($socket)->once()
				->mock($message3)->call('readSocket')->withArguments($socket)->thrice()
				->mock($socket)->call('onRead')->withArguments($poller, array($client, 'readSocket'))->exactly(5)

			->if(
				$this->calling($message3)->readSocket = true
			)
			->then
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message3)->call('readSocket')->withArguments($socket)->once()
				->mock($socket)->call('onRead')->withArguments($poller, array($client, 'readSocket'))->never()
				->object($client->readSocket())->isIdenticalTo($client)
				->mock($message3)->call('readSocket')->withArguments($socket)->once()
				->mock($socket)->call('onRead')->withArguments($poller, array($client, 'readSocket'))->never()
		;
	}

	public function testWriteMessage()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), $poller = new \mock\server\socket\poller\definition()))
			->then
				->object($client->writeMessage($message1 = new server\client\message()))->isIdenticalTo($client)
				->mock($socket)->call('onWrite')->withArguments($poller, array($client, 'writeSocket'))->once()
				->object($client->writeMessage($message2 = new server\client\message()))->isIdenticalTo($client)
				->mock($socket)->call('onWrite')->withArguments($poller, array($client, 'writeSocket'))->once()
		;
	}

	public function testWriteSocket()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), $poller = new \mock\server\socket\poller\definition()))
			->then
				->object($client->writeSocket())->isIdenticalTo($client)
				->mock($socket)->call('read')->never()

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

			->if(
				$this->calling($message3)->writeSocket = true
			)
			->then
				->object($client->writeSocket())->isIdenticalTo($client)
				->mock($message3)->call('writeSocket')->withArguments($socket)->once()
				->object($client->writeSocket())->isIdenticalTo($client)
				->mock($message3)->call('writeSocket')->withArguments($socket)->once()
		;
	}

	public function testCloseSocket()
	{
		$this
			->given($client = new testedClass($socket = new \mock\server\socket(uniqid()), new \mock\server\socket\poller\definition()))
			->then
				->object($client->closeSocket())->isIdenticalTo($client)
				->mock($socket)->call('close')->once()
		;
	}
}
