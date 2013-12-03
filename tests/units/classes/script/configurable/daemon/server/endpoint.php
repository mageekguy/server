<?php

namespace server\tests\units\script\configurable\daemon\server;

require __DIR__ . '/../../../../../runner.php';

use
	atoum,
	server\network,
	server\script\configurable\daemon\server\endpoint as testedClass
;

class endpoint extends atoum
{
	public function beforeTestMethod($method)
	{
		$test = $this;

		$this->then = function() use ($test) { $test->getTestAdapterStorage()->resetCalls(); return $test; };
	}

	public function test__construct()
	{
		$this
			->if($endpoint = new testedClass($ip = new network\ip('127.0.0.1'), $port = new network\port(8080)))
			->then
				->object($endpoint->getIp())->isIdenticalTo($ip)
				->object($endpoint->getPort())->isIdenticalTo($port)
				->variable($endpoint->getConnectHandler())->isNull()
		;
	}

	public function test__toString()
	{
		$this
			->if($endpoint = new testedClass($ip = new network\ip('127.0.0.1'), $port = new network\port(8080)))
			->then
				->castToString($endpoint)->isEqualTo($ip . ':' . $port)
		;
	}

	public function testSetIp()
	{
		$this
			->if($endpoint = new testedClass(new network\ip('127.0.0.1'), new network\port(8080)))
			->then
				->object($endpoint->setIp($ip = new network\ip('192.168.0.1')))->isIdenticalTo($endpoint)
				->object($endpoint->getIp())->isIdenticalTo($ip)
		;
	}

	public function testSetPort()
	{
		$this
			->if($endpoint = new testedClass(new network\ip('127.0.0.1'), new network\port(8080)))
			->then
				->object($endpoint->setPort($port = new network\port(8081)))->isIdenticalTo($endpoint)
				->object($endpoint->getPort())->isIdenticalTo($port)
		;
	}

	public function testOnConnect()
	{
		$this
			->if($endpoint = new testedClass($ip = new network\ip('127.0.0.1'), $port = new network\port(8080)))
			->then
				->object($endpoint->onConnect($handler = function() {}))->isIdenticalTo($endpoint)
				->object($endpoint->getConnectHandler())->isIdenticalTo($handler)
		;
	}

	public function testBindForServer()
	{
		$this
			->given(
				$endpoint = new testedClass(new network\ip('127.0.0.1'), new network\port(8080)),
				$server = new \mock\server\script\configurable\daemon\server(uniqid())
			)

			->if(
				$this->calling($server)->bindSocketTo = $socket = uniqid()
			)
			->then
				->string($endpoint->bindForServer($server))->isEqualTo($socket)
				->mock($server)
					->call('bindSocketTo')
						->withArguments($endpoint->getIp(), $endpoint->getPort())
								->once()
					->call('wait')->withArguments($socket)->never()

			->if(
				$endpoint->onConnect($handler = function() {}),
				$this->calling($server)->wait = $socketEvents = new \mock\server\socket\events()
			)
			->then
				->string($endpoint->bindForServer($server))->isEqualTo($socket)
				->mock($server)
					->call('bindSocketTo')
						->withArguments($endpoint->getIp(), $endpoint->getPort())
								->once()
					->call('wait')->withArguments($socket)->once()
				->mock($socketEvents)->call('onRead')->withArguments($handler)->once()
		;
	}
}
