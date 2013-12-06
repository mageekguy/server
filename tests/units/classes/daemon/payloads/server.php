<?php

namespace server\tests\units\daemon\payloads;

require __DIR__ . '/../../../runner.php';

use
	atoum,
	server\socket,
	server\network,
	server\daemon\payloads,
	server\daemon\payloads\server as testedClass
;

class server extends atoum
{
	public function testClass()
	{
		$this->testedClass->implements('server\socket\manager\definition');
	}

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
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->setInfoLogger($logger = new \server\logger()))->isIdenticalTo($server)
				->object($server->getInfoLogger())->isIdenticalTo($logger)
				->object($server->setInfoLogger())->isIdenticalTo($server)
				->object($server->getInfoLogger())
					->isNotIdenticalTo($logger)
					->isEqualTo(new \server\logger())
		;
	}

	public function testSetErrorLogger()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->setErrorLogger($logger = new \server\logger()))->isIdenticalTo($server)
				->object($server->getErrorLogger())->isIdenticalTo($logger)
				->object($server->setErrorLogger())->isIdenticalTo($server)
				->object($server->getErrorLogger())
					->isNotIdenticalTo($logger)
					->isEqualTo(new \server\logger())
		;
	}

	public function testAddEndpoint()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->addEndpoint($endpoint = new payloads\server\endpoint(new network\ip('127.0.0.1'), new network\port(8080))))->isIdenticalTo($server)
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

			->if($this->calling($socketManager)->readSocket = $data = uniqid())
			->then
				->string($server->readSocket($socket = uniqid(), $length = rand(1, PHP_INT_MAX), $mode = uniqid()))->isEqualTo($data)
				->mock($socketManager)->call('readSocket')->withArguments($socket, $length, $mode)->once()
		;
	}

	public function testWriteSocket()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->writeSocket = $bytesWritten = rand(1, PHP_INT_MAX))
			->then
				->integer($server->writeSocket($socket = uniqid(), $data = uniqid()))->isEqualTo($bytesWritten)
				->mock($socketManager)->call('writeSocket')->withArguments($socket, $data)->once()
		;
	}

	public function testCloseSocket()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->closeSocket->returnThis())
			->then
				->object($server->closeSocket($socket = uniqid()))->isEqualTo($server)
				->mock($socketManager)->call('closeSocket')->withArguments($socket)->once()
		;
	}

	public function testGetSocketPeer()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->getSocketPeer = $peer = new network\peer(new network\ip('127.0.0.1'), new network\port(8080)))
			->then
				->object($server->getSocketPeer($socket = uniqid()))->isIdenticalTo($peer)
				->mock($socketManager)->call('getSocketPeer')->withArguments($socket)->once()
		;
	}

	public function testGetLastSocketErrorCode()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->getLastSocketErrorCode = $errorCode = rand(1, PHP_INT_MAX))
			->then
				->integer($server->getLastSocketErrorCode())->isEqualTo($errorCode)
		;
	}

	public function testGetLastSocketErrorMessage()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->getLastSocketErrorMessage = $errorMessage = uniqid())
			->then
				->string($server->getLastSocketErrorMessage())->isEqualTo($errorMessage)
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
				$this->calling($socketManager)->acceptSocket = $clientSocket = uniqid(),
				$this->calling($socketManager)->getSocketPeer = new network\peer(new network\ip('127.0.0.1'), new network\port(8080))
			)
			->then
				->string($server->acceptSocket($serverSocket = uniqid()))->isEqualTo($clientSocket)
				->mock($socketManager)->call('acceptSocket')->withArguments($serverSocket)->once()
		;
	}

	public function testBindSocketTo()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->bindSocketTo = $serverSocket = uniqid())
			->then
				->string($server->bindSocketTo($ip = new network\ip('127.0.0.1'), $port = new network\port(8080)))->isEqualTo($serverSocket)
				->mock($socketManager)->call('bindSocketTo')->withArguments($ip, $port)->once()
		;
	}
}
