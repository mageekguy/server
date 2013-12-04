<?php

namespace server\tests\units\script\configurable\daemon;

require __DIR__ . '/../../../../runner.php';

use
	atoum,
	server\unix,
	server\socket,
	server\network,
	server\script\configurable\daemon,
	server\script\configurable\daemon\server as testedClass
;

class server extends atoum
{
	public function testClass()
	{
		$this->testedClass->extends('server\script\configurable\daemon');
	}

	public function test__construct()
	{
		$this
			->if($server = new testedClass($name = uniqid(), $adapter = new atoum\adapter()))
			->then
				->string($server->getName())->isEqualTo($name)
				->object($server->getAdapter())->isIdenticalTo($adapter)
				->object($server->getUnixUser())->isEqualTo(new unix\user())
				->variable($server->getHome())->isNull()
				->object($server->getSocketManager())->isEqualTo(new socket\manager())
				->object($server->getSocketSelect())->isEqualTo(new socket\select())
				->array($server->getEndpoints())->isEmpty();
		;
	}

	public function testSetUnixUser()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->setUnixUser($user = new unix\user()))->isIdenticalTo($server)
				->object($server->getUnixUser())->isIdenticalTo($user)
				->object($server->setUnixUser())->isIdenticalTo($server)
				->object($server->getUnixUser())
					->isNotIdenticalTo($user)
					->isEqualTo(new unix\user())
		;
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

	public function testAddEndpoint()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->addEndpoint($endpoint = new daemon\server\endpoint(new network\ip('127.0.0.1'), new network\port(8080))))->isIdenticalTo($server)
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

	public function testRun()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setUnixUser($unixUser = new \mock\server\unix\user()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)
			->then
				->exception(function() use ($server) { $server->run(); })
					->isInstanceOf('server\script\configurable\daemon\server\exception')
					->hasMessage('UID is undefined')

			->if($this->calling($unixUser)->getUid = $uid = rand(1, PHP_INT_MAX))
			->then
				->exception(function() use ($server) { $server->run(); })
					->isInstanceOf('server\script\configurable\daemon\server\exception')
					->hasMessage('Home is undefined')
		;
	}
}
