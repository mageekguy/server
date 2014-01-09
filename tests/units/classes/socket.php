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
			->exception(function() { new testedClass(uniqid()); })
				->isInstanceOf('server\socket\exception')
				->hasMessage('Resource is invalid')

			->if(
				$socketManager = new \mock\server\socket\manager(),
				$this->calling($socketManager)->isSocket = false
			)
			->then
				->exception(function() use ($socketManager) { new testedClass(uniqid(), $socketManager); })
					->isInstanceOf('server\socket\exception')
					->hasMessage('Resource is invalid')

			->if(
				$this->calling($socketManager)->isSocket = true,
				$socket = new testedClass(uniqid(), $socketManager)
			)
			->then
				->object($socket->getSocketManager())->isIdenticalTo($socketManager)
				->string($socket->getBuffer())->isEmpty()
		;
	}

	public function test__toString()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$socket->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->getSocketPeer = $peer = uniqid())
			->then
				->castToString($socket)->isEqualTo($peer)

			->if($this->calling($socketManager)->getSocketPeer->throw = $exception = new \exception())
			->then
				->castToString($socket)->isEmpty()
		;
	}

	public function testBufferize()
	{
		$this
			->given($socket = $this->getSocketInstance($resource = uniqid()))
			->then
				->object($socket->bufferize($data = uniqid()))->isIdenticalTo($socket)
				->string($socket->getBuffer())->isEqualTo($data)
		;
	}

	public function testSetSocketManager()
	{
		$this
			->if($socket = $this->getSocketInstance())
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
				$socket = $this->getSocketInstance($resource = uniqid()),
				$poller = new \mock\server\socket\poller()
			)

			->if($this->calling($poller)->pollSocket = $events = new \mock\server\socket\events())
			->then
				->object($socket->onRead($poller, $handler1 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)->call('onRead')->withArguments($handler1)->once()
				->object($socket->onRead($poller, $handler2 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)
					->call('onRead')->withArguments($handler2)->once()
					->call('bind')->withArguments($socket)->once()

			->if($this->calling($events)->__isset = function($event) { return ($event == 'onRead'); })
			->then
				->object($socket->onRead($poller, $handler3 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)
					->call('onRead')->withArguments($handler3)->once()
					->call('bind')->withArguments($socket)->once()

			->if($this->calling($events)->__isset = false)
			->then
				->object($socket->onRead($poller, $handler4 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->twice()
				->mock($events)
					->call('onRead')->withArguments($handler4)->once()
					->call('bind')->withArguments($socket)->twice()

			->if($socket->bind($bind = uniqid()))
			->then
				->object($socket->onRead($poller, $handler5 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->thrice()
				->mock($events)
					->call('onRead')->withArguments($handler5)->once()
					->call('bind')->withArguments($bind)->once()
		;
	}

	public function testOnWrite()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$poller = new \mock\server\socket\poller()
			)

			->if($this->calling($poller)->pollSocket = $events = new \mock\server\socket\events())
			->then
				->object($socket->onWrite($poller, $handler1 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)->call('onWrite')->withArguments($handler1)->once()
				->object($socket->onWrite($poller, $handler2 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)
					->call('onWrite')->withArguments($handler2)->once()
					->call('bind')->withArguments($socket)->once()

			->if($this->calling($events)->__isset = function($event) { return ($event == 'onWrite'); })
			->then
				->object($socket->onWrite($poller, $handler3 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)
					->call('onWrite')->withArguments($handler3)->once()
					->call('bind')->withArguments($socket)->once()

			->if($this->calling($events)->__isset = false)
			->then
				->object($socket->onWrite($poller, $handler4 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->twice()
				->mock($events)
					->call('onWrite')->withArguments($handler4)->once()
					->call('bind')->withArguments($socket)->twice()

			->if($socket->bind($bind = uniqid()))
			->then
				->object($socket->onWrite($poller, $handler5 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->thrice()
				->mock($events)
					->call('onWrite')->withArguments($handler5)->once()
					->call('bind')->withArguments($bind)->once()
		;
	}

	public function testOnTimeout()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$poller = new \mock\server\socket\poller()
			)

			->if($this->calling($poller)->pollSocket = $events = new \mock\server\socket\events())
			->then
				->object($socket->onTimeout($poller, $timer = new server\socket\timer(60), $handler1 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)
					->call('onTimeout')->withArguments($timer, $handler1)->once()
					->call('bind')->withArguments($socket)->once()
				->object($socket->onTimeout($poller, $timer, $handler2 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->twice()
				->mock($events)
					->call('onTimeout')->withArguments($timer, $handler2)->once()
					->call('bind')->withArguments($socket)->twice()
		;
	}

	public function testGetPeer()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$socketManager = $socket->getSocketManager()
			)

			->if($this->calling($socketManager)->getSocketPeer = $peer = new network\peer(new network\ip('127.0.0.1'), new network\port(8080)))
			->then
				->object($socket->getPeer())->isIdenticalTo($peer)
				->mock($socketManager)->call('getSocketPeer')->withArguments($resource)->once()

			->if($this->calling($socketManager)->getSocketPeer->throw = $exception = new \exception(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->exception(function() use ($socket) { $socket->getPeer(); })
					->isInstanceOf('server\socket\exception')
					->hasCode($exception->getCode())
					->hasMessage($exception->getMessage())
		;
	}

	public function testGetName()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$socketManager = $socket->getSocketManager()
			)

			->if($this->calling($socketManager)->getSocketName = $peer = new network\peer(new network\ip('127.0.0.1'), new network\port(8080)))
			->then
				->object($socket->getName())->isIdenticalTo($peer)
				->mock($socketManager)->call('getSocketName')->withArguments($resource)->once()

			->if($this->calling($socketManager)->getSocketName->throw = $exception = new \exception(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->exception(function() use ($socket) { $socket->getName(); })
					->isInstanceOf('server\socket\exception')
					->hasCode($exception->getCode())
					->hasMessage($exception->getMessage())
		;
	}

	public function testRead()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$socket->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->readSocket = $data = uniqid())
			->then
				->string($socket->read($length = rand(1, PHP_INT_MAX), $mode = uniqid()))->isEqualTo($data)
				->mock($socketManager)->call('readSocket')->withArguments($resource, $length, $mode)->once()

			->if(
				$this->calling($socketManager)->readSocket = $data = uniqid(),
				$socket->bufferize($bufferizedData = uniqid())
			)
			->then
				->string($socket->read($length = rand(1, PHP_INT_MAX), $mode = uniqid()))->isEqualTo($bufferizedData . $data)
				->mock($socketManager)->call('readSocket')->withArguments($resource, $length, $mode)->once()
				->string($socket->getBuffer())->isEmpty()

			->if($this->calling($socketManager)->readSocket->throw = $exception = new \exception(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->exception(function() use ($socket) { $socket->read(rand(1, PHP_INT_MAX), uniqid()); })
					->isInstanceOf('server\socket\exception')
					->hasCode($exception->getCode())
					->hasMessage($exception->getMessage())
		;
	}

	public function testWrite()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$socketManager = $socket->getSocketManager()
			)

			->if($this->calling($socketManager)->writeSocket = $bytesWritten = rand(1, PHP_INT_MAX))
			->then
				->integer($socket->write($data = uniqid()))->isEqualTo($bytesWritten)
				->mock($socketManager)->call('writeSocket')->withArguments($resource, $data)->once()

			->if($this->calling($socketManager)->writeSocket->throw = $exception = new \exception(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->exception(function() use ($socket) { $socket->write(uniqid()); })
					->isInstanceOf('server\socket\exception')
					->hasCode($exception->getCode())
					->hasMessage($exception->getMessage())
		;
	}

	public function testClose()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$socketManager = $socket->getSocketManager()
			)

			->if($this->calling($socketManager)->closeSocket->returnThis())
			->then
				->object($socket->close())->isIdenticalTo($socket)
				->mock($socketManager)->call('closeSocket')->withArguments($resource)->once()

			->if($this->calling($socketManager)->closeSocket->throw = $exception = new \exception(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->exception(function() use ($socket) { $socket->close(); })
					->isInstanceOf('server\socket\exception')
					->hasCode($exception->getCode())
					->hasMessage($exception->getMessage())
		;
	}

	public function testIsClosed()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$socketManager = $socket->getSocketManager()
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

	public function testBind()
	{
		$this
			->given($socket = $this->getSocketInstance(uniqid()))
			->then
				->object($socket->bind($this))->isIdenticalTo($socket)
		;
	}

	protected function getSocketInstance($resource = null)
	{
		$socketManager = new \mock\server\socket\manager();
		$this->calling($socketManager)->isSocket = true;

		return new testedClass($resource ?: uniqid(), $socketManager);
	}
}
