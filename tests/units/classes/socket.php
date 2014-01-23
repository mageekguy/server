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
				->string($socket->getData())->isEmpty()
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

	public function testOnReadNotBlock()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$poller = new \mock\server\socket\poller()
			)

			->if($this->calling($poller)->pollSocket = $events = new \mock\server\socket\events())
			->then
				->object($socket->onReadNotBlock($poller, $handler1 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)->call('onReadNotBlock')->withArguments($handler1)->once()
				->object($socket->onReadNotBlock($poller, $handler2 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->twice()
				->mock($events)
					->call('onReadNotBlock')->withArguments($handler2)->once()
					->call('bind')->withArguments($socket)->twice()

			->if($this->calling($events)->__isset = function($event) { return ($event == 'onReadNotBlock'); })
			->then
				->object($socket->onReadNotBlock($poller, $handler3 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->thrice()
				->mock($events)
					->call('onReadNotBlock')->withArguments($handler3)->once()
					->call('bind')->withArguments($socket)->thrice()

			->if($this->calling($events)->__isset = false)
			->then
				->object($socket->onReadNotBlock($poller, $handler4 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->exactly(4)
				->mock($events)
					->call('onReadNotBlock')->withArguments($handler4)->once()
					->call('bind')->withArguments($socket)->exactly(4)

			->if($socket->bind($bind = uniqid()))
			->then
				->object($socket->onReadNotBlock($poller, $handler5 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->exactly(5)
				->mock($events)
					->call('onReadNotBlock')->withArguments($handler5)->once()
					->call('bind')->withArguments($bind)->once()
		;
	}

	public function testOnWriteNotBlock()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$poller = new \mock\server\socket\poller()
			)

			->if($this->calling($poller)->pollSocket = $events = new \mock\server\socket\events())
			->then
				->object($socket->onWriteNotBlock($poller, $handler1 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->once()
				->mock($events)->call('onWriteNotBlock')->withArguments($handler1)->once()
				->object($socket->onWriteNotBlock($poller, $handler2 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->twice()
				->mock($events)
					->call('onWriteNotBlock')->withArguments($handler2)->once()
					->call('bind')->withArguments($socket)->twice()

			->if($this->calling($events)->__isset = function($event) { return ($event == 'onWriteNotBlock'); })
			->then
				->object($socket->onWriteNotBlock($poller, $handler3 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->thrice()
				->mock($events)
					->call('onWriteNotBlock')->withArguments($handler3)->once()
					->call('bind')->withArguments($socket)->thrice()

			->if($this->calling($events)->__isset = false)
			->then
				->object($socket->onWriteNotBlock($poller, $handler4 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->exactly(4)
				->mock($events)
					->call('onWriteNotBlock')->withArguments($handler4)->once()
					->call('bind')->withArguments($socket)->exactly(4)

			->if($socket->bind($bind = uniqid()))
			->then
				->object($socket->onWriteNotBlock($poller, $handler5 = function() {}))->isIdenticalTo($socket)
				->mock($poller)->call('pollSocket')->withArguments($resource)->exactly(5)
				->mock($events)
					->call('onWriteNotBlock')->withArguments($handler5)->once()
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

			->if($this->calling($socketManager)->readSocket = $data1 = uniqid())
			->then
				->string($socket->read($length = rand(1, PHP_INT_MAX), $mode = uniqid()))->isEqualTo($data1)
				->string($socket->getData())->isEqualTo($data1)
				->mock($socketManager)->call('readSocket')->withArguments($resource, $length, $mode)->once()

			->if($this->calling($socketManager)->readSocket = $data2 = uniqid())
			->then
				->string($socket->read($length = rand(1, PHP_INT_MAX), $mode = uniqid()))->isEqualTo($data2)
				->string($socket->getData())->isEqualTo($data1 . $data2)
				->mock($socketManager)->call('readSocket')->withArguments($resource, $length, $mode)->once()

			->if($this->calling($socketManager)->readSocket->throw = $exception = new \exception(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->exception(function() use ($socket) { $socket->read(rand(1, PHP_INT_MAX), uniqid()); })
					->isInstanceOf('server\socket\exception')
					->hasCode($exception->getCode())
					->hasMessage($exception->getMessage())
		;
	}

	public function testGetData()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$socket->setSocketManager($socketManager = new \mock\server\socket\manager())
			)
			->then
				->string($socket->getData())->isEmpty()

			->if(
				$this->calling($socketManager)->readSocket = $data1 = uniqid(),
				$socket->read(rand(1, PHP_INT_MAX), uniqid())
			)
			->then
				->string($socket->getData())->isEqualTo($data1)

			->if(
				$this->calling($socketManager)->readSocket = $data2 = uniqid(),
				$socket->read(rand(1, PHP_INT_MAX), uniqid())
			)
			->then
				->string($socket->getData())->isEqualTo($data1 . $data2)
		;
	}

	public function testPeekData()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$socket->setSocketManager($socketManager = new \mock\server\socket\manager())
			)
			->then
				->variable($socket->peekData('/^.+END/'))->isNull()

			->if(
				$this->calling($socketManager)->readSocket = $data1 = uniqid(),
				$socket->read(rand(1, PHP_INT_MAX), uniqid())
			)
			->then
				->variable($socket->peekData('/^.+END/'))->isNull()

			->if(
				$this->calling($socketManager)->readSocket = 'END',
				$socket->read(rand(1, PHP_INT_MAX), uniqid())
			)
			->then
				->array($socket->peekData('/^.+END/'))->isEqualTo(array($data1 . 'END'))
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

	public function testConnectTo()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$socket->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->connectSocketTo->returnThis())
			->then
				->object($socket->connectTo($ip = new network\ip('127.0.0.1'), $port = new network\port(8080)))->isIdenticalTo($socket)
				->mock($socketManager)->call('connectSocketTo')->withArguments($resource, $ip, $port)->once()
		;
	}

	protected function getSocketInstance($resource = null)
	{
		$socketManager = new \mock\server\socket\manager();
		$this->calling($socketManager)->isSocket = true;

		return new testedClass($resource ?: uniqid(), $socketManager);
	}
}
