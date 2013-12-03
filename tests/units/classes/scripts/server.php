<?php

namespace server\tests\units\scripts;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\network,
	server\scripts\server as testedClass
;

class server extends atoum
{
	public function testSetTrackersIp()
	{
		$this
			->if(
				$server = new testedClass(uniqid())
			)
			->then
				->object($server->setTrackersIp($ip = new network\ip('127.0.0.1')))->isIdenticalTo($server)
				->object($server->getTrackersIp())->isEqualTo($ip)
		;
	}

	public function testSetTrackersPort()
	{
		$this
			->if(
				$server = new testedClass(uniqid())
			)
			->then
				->object($server->setTrackersPort($port = new network\port(8080)))->isIdenticalTo($server)
				->object($server->getTrackersPort())->isEqualTo($port)
		;
	}
}
