<?php

namespace server\tests\units\script\configurable\daemon\server;

require __DIR__ . '/../../../../../runner.php';

use
	atoum,
	server\socket,
	server\network,
	server\script\configurable\daemon\server,
	server\script\configurable\daemon\server\payload as testedClass
;

class payload extends atoum
{
	public function testSetSocketManager()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->setSocketManager($manager = new socket\manager()))->isIdenticalTo($server)
				->object($server->getSocketManager())->isIdenticalTo($manager)
				->object($server->setSocketManager())->isIdenticalTo($server)
				->object($server->getSocketManager())
					->isNotIdenticalTo($manager)
					->isEqualTo(new socket\manager())
		;
	}

	public function testSetSocketSelect()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->setSocketSelect($select = new socket\select()))->isIdenticalTo($server)
				->object($server->getSocketSelect())->isIdenticalTo($select)
				->object($server->setSocketSelect())->isIdenticalTo($server)
				->object($server->getSocketSelect())
					->isNotIdenticalTo($select)
					->isEqualTo(new socket\select())
		;
	}

	public function testSetInfoLogger()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->setInfoLogger($logger = new \server\logger()))->isIdenticalTo($daemon)
				->object($daemon->getInfoLogger())->isIdenticalTo($logger)
				->object($daemon->setInfoLogger())->isIdenticalTo($daemon)
				->object($daemon->getInfoLogger())
					->isNotIdenticalTo($logger)
					->isEqualTo(new \server\logger())
		;
	}

	public function testSetErrorLogger()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->setErrorLogger($logger = new \server\logger()))->isIdenticalTo($daemon)
				->object($daemon->getErrorLogger())->isIdenticalTo($logger)
				->object($daemon->setErrorLogger())->isIdenticalTo($daemon)
				->object($daemon->getErrorLogger())
					->isNotIdenticalTo($logger)
					->isEqualTo(new \server\logger())
		;
	}

	public function testAddEndpoint()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->addEndpoint($endpoint = new server\payload\endpoint(new network\ip('127.0.0.1'), new network\port(8080))))->isIdenticalTo($server)
				->array($server->getEndpoints())->isEqualTo(array($endpoint))
		;
	}

	public function testWait()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketSelect($select = new \mock\server\socket\select())
			)

			->if($this->calling($select)->socket = $socketEvents = new socket\events())
			->then
				->object($server->wait($socket = uniqid()))->isIdenticalTo($socketEvents)
				->mock($select)->call('socket')->withArguments($socket)->once()
		;
	}

	public function testReadSocket()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->read = $data = uniqid())
			->then
				->string($server->readSocket($socket = uniqid(), $length = rand(1, PHP_INT_MAX), $mode = uniqid()))->isEqualTo($data)
				->mock($socketManager)->call('read')->withArguments($socket, $length, $mode)->once()
		;
	}

	public function testCloseSocket()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->close->returnThis())
			->then
				->object($server->closeSocket($socket = uniqid()))->isEqualTo($server)
				->mock($socketManager)->call('close')->withArguments($socket)->once()
		;
	}

	public function testGetSocketPeer()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->getPeer = $peer = new network\peer(new network\ip('127.0.0.1'), new network\port(8080)))
			->then
				->object($server->getSocketPeer($socket = uniqid()))->isIdenticalTo($peer)
				->mock($socketManager)->call('getPeer')->withArguments($socket)->once()
		;
	}

	public function testAcceptSocket()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if(
				$this->calling($socketManager)->accept = $clientSocket = uniqid(),
				$this->calling($socketManager)->getPeer = new network\peer(new network\ip('127.0.0.1'), new network\port(8080))
			)
			->then
				->string($server->acceptSocket($serverSocket = uniqid()))->isEqualTo($clientSocket)
				->mock($socketManager)->call('accept')->withArguments($serverSocket)->once()
		;
	}

	public function testBindSocketTo()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->bindTo = $serverSocket = uniqid())
			->then
				->string($server->bindSocketTo($ip = new network\ip('127.0.0.1'), $port = new network\port(8080)))->isEqualTo($serverSocket)
				->mock($socketManager)->call('bindTo')->withArguments($ip, $port)->once()
		;
	}
}
