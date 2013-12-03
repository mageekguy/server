<?php

namespace server\tests\units\network;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\network,
	server\network\peer as testedClass
;

class peer extends atoum
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
}
