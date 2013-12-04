<?php

namespace server\demo\tests\units\scripts;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\network,
	server\demo\scripts\server as testedClass
;

class server extends atoum
{
	public function testSetClientsIp()
	{
		$this
			->if(
				$server = new testedClass(uniqid())
			)
			->then
				->object($server->setClientsIp($ip = new network\ip('127.0.0.1')))->isIdenticalTo($server)
				->object($server->getClientsIp())->isEqualTo($ip)
		;
	}

	public function testSetClientsPort()
	{
		$this
			->if(
				$server = new testedClass(uniqid())
			)
			->then
				->object($server->setClientsPort($port = new network\port(8080)))->isIdenticalTo($server)
				->object($server->getClientsPort())->isEqualTo($port)
		;
	}
}
