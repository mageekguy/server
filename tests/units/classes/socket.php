<?php

namespace server\tests\units;

require __DIR__ . '/../runner.php';

use
	atoum,
	server,
	server\network,
	server\socket as testedClass
;

class socket extends atoum
{
	public function test__construct()
	{
		$this
			->if($socket = new testedClass($resource = uniqid()))
			->then
				->object($socket->getSocketManager())->isEqualTo(new server\socket\manager())
			->if($socket = new testedClass($resource = uniqid(), $socketManager = new server\socket\manager()))
			->then
				->object($socket->getSocketManager())->isIdenticalTo($socketManager)
		;
	}

	public function testSetSocketManager()
	{
		$this
			->if($socket = new testedClass($resource = uniqid()))
			->then
				->object($socket->setSocketManager($socketManager = new server\socket\manager()))->isIdenticalTo($socket)
				->object($socket->getSocketManager())->isIdenticalTo($socketManager)
				->object($socket->setSocketManager())->isIdenticalTo($socket)
				->object($socket->getSocketManager())
					->isNotIdenticalTo($socketManager)
					->isEqualTo(new server\socket\manager())
		;
	}

	public function testOnRead()
	{
		$this
			->given(
				$socket = new testedClass($resource = uniqid()),
				$poller = new \mock\server\socket\poller()
			)

			->if($this->calling($poller)->pollSocket = $events = new \mock\server\socket\events())
			->then
				->object($socket->onRead($poller, $handler1 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)->call('onRead')->withArguments($handler1)->once()
				->object($socket->onRead($poller, $handler2 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)->call('onRead')->withArguments($handler2)->once()

			->if($this->calling($events)->__isset = function($event) { return ($event == 'onRead'); })
			->then
				->object($socket->onRead($poller, $handler3 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)->call('onRead')->withArguments($handler3)->once()

			->if($this->calling($events)->__isset = false)
			->then
				->object($socket->onRead($poller, $handler4 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->twice()
				->mock($events)->call('onRead')->withArguments($handler4)->once()
		;
	}

	public function testOnWrite()
	{
		$this
			->given(
				$socket = new testedClass($resource = uniqid()),
				$poller = new \mock\server\socket\poller()
			)

			->if($this->calling($poller)->pollSocket = $events = new \mock\server\socket\events())
			->then
				->object($socket->onWrite($poller, $handler1 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)->call('onWrite')->withArguments($handler1)->once()
				->object($socket->onWrite($poller, $handler2 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)->call('onWrite')->withArguments($handler2)->once()

			->if($this->calling($events)->__isset = function($event) { return ($event == 'onWrite'); })
			->then
				->object($socket->onWrite($poller, $handler3 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)->call('onWrite')->withArguments($handler3)->once()

			->if($this->calling($events)->__isset = false)
			->then
				->object($socket->onWrite($poller, $handler4 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->twice()
				->mock($events)->call('onWrite')->withArguments($handler4)->once()
		;
	}

	public function testOnTimeout()
	{
		$this
			->given(
				$socket = new testedClass($resource = uniqid()),
				$poller = new \mock\server\socket\poller()
			)

			->if($this->calling($poller)->pollSocket = $events = new \mock\server\socket\events())
			->then
				->object($socket->onTimeout($poller, $timer = new server\socket\timer(60), $handler1 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)->call('onTimeout')->withArguments($timer, $handler1)->once()
				->object($socket->onTimeout($poller, $timer, $handler2 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->twice()
				->mock($events)->call('onTimeout')->withArguments($timer, $handler2)->once()
		;
	}

	public function testGetPeer()
	{
		$this
			->given(
				$socket = new testedClass($resource = uniqid()),
				$socket->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->getSocketPeer = $peer = new network\peer(new network\ip('127.0.0.1'), new network\port(8080)))
			->then
				->object($socket->getPeer())->isIdenticalTo($peer)
				->mock($socketManager)->call('getSocketPeer')->withArguments($resource)->once()
		;
	}

	public function testRead()
	{
		$this
			->given(
				$socket = new testedClass($resource = uniqid()),
				$socket->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->readSocket = $data = uniqid())
			->then
				->string($socket->read($length = rand(1, PHP_INT_MAX), $mode = uniqid()))->isEqualTo($data)
				->mock($socketManager)->call('readSocket')->withArguments($resource, $length, $mode)->once()
		;
	}

	public function testWrite()
	{
		$this
			->given(
				$socket = new testedClass($resource = uniqid()),
				$socket->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->writeSocket = $bytesWritten = rand(1, PHP_INT_MAX))
			->then
				->integer($socket->write($data = uniqid()))->isEqualTo($bytesWritten)
				->mock($socketManager)->call('writeSocket')->withArguments($resource, $data)->once()
		;
	}

	public function testClose()
	{
		$this
			->given(
				$socket = new testedClass($resource = uniqid()),
				$socket->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->closeSocket->returnThis())
			->then
				->object($socket->close())->isIdenticalTo($socket)
				->mock($socketManager)->call('closeSocket')->withArguments($resource)->once()
		;
	}

	public function testIsClosed()
	{
		$this
			->given(
				$socket = new testedClass($resource = uniqid()),
				$socket->setSocketManager($socketManager = new \mock\server\socket\manager())
			)
			->then
				->if($this->calling($socketManager)->isSocket = true)
				->then
					->boolean($socket->isClosed())->isFalse()

				->if($this->calling($socketManager)->isSocket = false)
				->then
					->boolean($socket->isClosed())->isTrue()
		;
	}
}
