<?php

namespace server\tests\units\socket;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\fs,
	server\network,
	server\socket\manager as testedClass,
	mock\server\socket\manager as mockedTestedClass
;

class manager extends atoum
{
	public function beforeTestMethod($method)
	{
		$test = $this;

		$this->then = function() use ($test) { $test->getTestAdapterStorage()->resetCalls(); return $test; };
	}

	public function testClass()
	{
		$this->testedClass->implements('server\socket\manager\definition');
	}

	public function testCreateSocket()
	{
		$this
			->given($manager = new mockedTestedClass())

			->if(
				$this->function->socket_create = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->function->socket_clear_error->doesNothing()
			)
			->then
				->exception(function() use ($manager, & $domain, & $type, & $protocol) { $manager->createSocket($domain = uniqid(), $type = uniqid(), $protocol = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_create')->wasCalledWithArguments($domain, $type, $protocol)->once()
				->function('socket_close')->wasCalled()->never()
				->function('socket_close')->wasCalled()->never()
				->function('socket_last_error')->wasCalledWithArguments(null)->once()

			->if($this->function->socket_create = $resource = uniqid())
			->then
				->string($manager->createSocket($domain = uniqid(), $type = uniqid(), $protocol = uniqid()))->isEqualTo($resource)
				->function('socket_create')->wasCalledWithArguments($domain, $type, $protocol)->once()
		;
	}

	public function testBindSocketTo()
	{
		$this
			->given($manager = new mockedTestedClass())

			->if(
				$this->function->socket_create = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->function->socket_clear_error->doesNothing()
			)
			->then
				->exception(function() use ($manager) { $manager->bindSocketTo(new network\ip('127.0.0.1'), new network\port(8080)); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_close')->wasCalled()->never()
				->function('socket_last_error')->wasCalledWithArguments(null)->once()

			->if(
				$this->function->socket_create = $socket = uniqid(),
				$this->function->socket_set_option = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->calling($manager)->closeSocket->returnThis()
			)
			->then
				->exception(function() use ($manager) { $manager->bindSocketTo(new network\ip('127.0.0.1'), new network\port(8080)); })
					->isInstanceOf('server\socket\manager\exception')
				->mock($manager)->call('closeSocket')->withArguments($socket)->once()
				->function('socket_last_error')->wasCalledWithArguments($socket)->once()

			->if(
				$this->function->socket_set_option = true,
				$this->function->socket_bind = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid()
			)
			->then
				->exception(function() use ($manager) { $manager->bindSocketTo(new network\ip('127.0.0.1'), new network\port(8080)); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->mock($manager)->call('closeSocket')->withArguments($socket)->once()
				->function('socket_last_error')->wasCalledWithArguments($socket)->once()

			->if(
				$this->function->socket_bind = true,
				$this->function->socket_listen = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid()
			)
			->then
				->exception(function() use ($manager) { $manager->bindSocketTo(new network\ip('127.0.0.1'), new network\port(8080)); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->mock($manager)->call('closeSocket')->withArguments($socket)->once()
				->function('socket_last_error')->wasCalledWithArguments($socket)->once()

			->if($this->function->socket_listen = true)
			->then
				->string($manager->bindSocketTo($ip = new network\ip('127.0.0.1'), $port = new network\port(8080)))->isIdenticalTo($socket)
				->mock($manager)->call('closeSocket')->withArguments($socket)->never()
				->function('socket_listen')
					->wasCalledWithArguments($socket)
						->after($this->function('socket_bind')
							->wasCalledWithArguments($socket, $ip, $port)
								->after($this->function('socket_set_option')
									->wasCalledWithArguments($socket, SOL_SOCKET, SO_REUSEADDR, 1)
										->once()
								)
						)
							->once()
		;
	}

	public function testAcceptSocket()
	{
		$this
			->given($manager = new mockedTestedClass())

			->if(
				$this->function->socket_accept = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->function->socket_clear_error->doesNothing()
			)
			->then
				->exception(function() use ($manager, & $socket) { $manager->acceptSocket($socket = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_accept')
					->wasCalledWithArguments($socket)
						->before($this->function('socket_last_error')->wasCalledWithArguments($socket)->once())
							->once()

			->if(
				$this->function->socket_accept = $clientSocket = uniqid()
			)
			->then
				->string($manager->acceptSocket($socket))->isEqualTo($clientSocket)
				->function('socket_accept')->wasCalledWithArguments($socket)->once()
		;
	}

	public function testReadSocket()
	{
		$this
			->given($manager = new mockedTestedClass())

			->if(
				$this->function->socket_read = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->function->socket_clear_error->doesNothing()
			)
			->then
				->exception(function() use ($manager, & $socket, & $length, & $mode) { $manager->readSocket($socket = uniqid(), $length = rand(1, PHP_INT_MAX), $mode = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_read')
					->wasCalledWithArguments($socket, $length, $mode)
						->before($this->function('socket_last_error')->wasCalledWithArguments($socket)->once())
							->once()

			->if(
				$this->function->socket_read = ''
			)
			->then
				->string($manager->readSocket($socket, $length, $mode))->isEmpty()
				->function('socket_read')->wasCalledWithArguments($socket, $length, $mode)->once()

			->if(
				$this->function->socket_read = $data = uniqid()
			)
			->then
				->string($manager->readSocket($socket, $length, $mode))->isEqualTo($data)
				->function('socket_read')->wasCalledWithArguments($socket, $length, $mode)->once()
		;
	}

	public function testWriteSocket()
	{
		$this
			->given($manager = new mockedTestedClass())

			->if(
				$this->function->socket_write = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->function->socket_clear_error->doesNothing()
			)
			->then
				->exception(function() use ($manager, & $socket, & $data) { $manager->writeSocket($socket = uniqid(), $data = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_write')
					->wasCalledWithArguments($socket, $data)
						->before($this->function('socket_last_error')->wasCalledWithArguments($socket)->once())
							->once()

			->if(
				$this->function->socket_write = function($socket, $data) { return strlen($data); }
			)
			->then
				->integer($manager->writeSocket($socket, $data))->isEqualTo(strlen($data))
				->function('socket_write')->wasCalledWithArguments($socket, $data, strlen($data))->once()
				->integer($manager->writeSocket($socket, ''))->isEqualTo(0)
				->function('socket_write')->wasCalledWithArguments($socket, '', 0)->once()
		;
	}

	public function testGetSocketPeer()
	{
		$this
			->given($manager = new testedClass())

			->if(
				$this->function->socket_getpeername = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->function->socket_clear_error->doesNothing()
			)
			->then
				->exception(function() use ($manager, & $socket) { $manager->getSocketPeer($socket = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_last_error')
					->wasCalledWithArguments($socket)
						->after($this->function('socket_getpeername')->wasCalledWithArguments($socket)->once())
							->once()

			->if($this->function->socket_getpeername = function($socket, & $ip, & $port) use (& $socketIp, & $socketPort) { $ip = $socketIp = '127.0.0.1'; $port = $socketPort = 8080; return true; })
			->then
				->object($manager->getSocketPeer($socket))->isEqualTo(new network\peer(new network\ip($socketIp), new network\port($socketPort)))
				->function('socket_getpeername')->wasCalledWithArguments($socket)->once()

			->if($this->function->socket_getpeername = function($socket, & $path, & $port) use (& $socketPath) { $path = $socketPath = uniqid(); $port = null; return true; })
			->then
				->object($manager->getSocketPeer($socket))->isEqualTo(new fs\path($socketPath))
				->function('socket_getpeername')->wasCalledWithArguments($socket)->once()
		;
	}

	public function testGetSocketName()
	{
		$this
			->given($manager = new testedClass())

			->if(
				$this->function->socket_getsockname = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->function->socket_clear_error->doesNothing()
			)
			->then
				->exception(function() use ($manager, & $socket) { $manager->getSocketName($socket = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_last_error')
					->wasCalledWithArguments($socket)
						->after($this->function('socket_getsockname')->wasCalledWithArguments($socket)->once())
							->once()

			->if($this->function->socket_getsockname = function($socket, & $ip, & $port) use (& $socketIp, & $socketPort) { $ip = $socketIp = '127.0.0.1'; $port = $socketPort = 8080; return true; })
			->then
				->object($manager->getSocketName($socket))->isEqualTo(new network\peer(new network\ip($socketIp), new network\port($socketPort)))
				->function('socket_getsockname')->wasCalledWithArguments($socket)->once()

			->if($this->function->socket_getsockname = function($socket, & $path, & $port) use (& $socketPath) { $path = $socketPath = uniqid(); $port = null; return true; })
			->then
				->object($manager->getSocketName($socket))->isEqualTo(new fs\path($socketPath))
				->function('socket_getsockname')->wasCalledWithArguments($socket)->once()
		;
	}

	public function testPollSockets()
	{
		$this
			->given(
				$manager = new testedClass(),
				$this->function->socket_select = 0
			)

			->if(
				$read = range(1, 5),
				$write = range(6, 10),
				$except = range(11, 15)
			)
			->then
				->object($manager->pollSockets($read, $write, $except, $timeout = rand(1, PHP_INT_MAX)))->isIdenticalTo($manager)
				->function('socket_select')->wasCalledWithArguments($read, $write, $except, $timeout)->once()

			->if(
				$this->function->socket_select = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->function->socket_clear_error->doesNothing()
			)
			->then
				->exception(function() use ($manager, $read, $write, $except, $timeout) { $manager->pollSockets($read, $write, $except, $timeout); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_last_error')
					->wasCalledWithArguments(null)
						->after($this->function('socket_select')->wasCalledWithArguments($read, $write, $except, $timeout)->once())
							->once()

			->if($this->function->socket_select = 0)
			->then
				->object($manager->pollSockets($read, $write, $except, $timeout = rand(1, PHP_INT_MAX)))->isIdenticalTo($manager)
				->function('socket_select')->wasCalledWithArguments($read, $write, $except, $timeout)->once()
		;
	}

	public function testCloseSocket()
	{
		$this
			->given($manager = new testedClass())

			->if(
				$this->function->is_resource = true,
				$this->function->get_resource_type = testedClass::resourceType,
				$this->function->socket_set_block->doesNothing(),
				$this->function->socket_last_error->doesNothing(),
				$this->function->socket_set_option->doesNothing(),
				$this->function->socket_shutdown->doesNothing(),
				$this->function->socket_close->doesNothing(),
				$this->function->socket_strerror->doesNothing(),
				$this->function->socket_clear_error->doesNothing()
			)
			->then
				->object($manager->closeSocket($socket = uniqid()))->isIdenticalTo($manager)
				->function('socket_set_block')
					->wasCalledWithArguments($socket)
					->before($this->function('socket_set_option')
						->wasCalledWithArguments($socket, SOL_SOCKET, SO_LINGER, array('l_onoff' => 1, 'l_linger' => 0))
						->before($this->function('socket_shutdown')
							->wasCalledWithArguments($socket, 2)
							->once()
						)
						->once()
					)
					->once()

			->if(
				$this->function->socket_close = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid()
			)
			->then
				->exception(function() use ($manager, & $socket) { $manager->closeSocket($socket = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
		;
	}

	public function testIsSocket()
	{
		$this
			->given($socket = new testedClass($resource = uniqid()))
			->then

				->if(
					$this->function->is_resource = true,
					$this->function->get_resource_type = testedClass::resourceType
				)
				->then
					->boolean($socket->isSocket($var = uniqid()))->isTrue()
					->function('is_resource')->wasCalledWithArguments($var)->once()
					->function('get_resource_type')->wasCalledWithArguments($var)->once()

				->if(
					$this->function->get_resource_type = uniqid()
				)
				->then
					->boolean($socket->isSocket($var = uniqid()))->isFalse()
					->function('is_resource')->wasCalledWithArguments($var)->once()
					->function('get_resource_type')->wasCalledWithArguments($var)->once()

				->if(
					$this->function->is_resource = false
				)
				->then
					->boolean($socket->isSocket($var = uniqid()))->isFalse()
					->function('is_resource')->wasCalledWithArguments($var)->once()
					->function('get_resource_type')->never()
		;
	}

	public function testConnectSocketTo()
	{
		$this
			->given($manager = new mockedTestedClass())

			->if(
				$this->function->socket_connect = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->function->socket_clear_error->doesNothing()
			)
			->then
				->exception(function() use ($manager, & $resource, & $ip, & $port) { $manager->connectSocketTo($resource, $ip = new network\ip('127.0.0.1'), $port = new network\port(8080)); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_connect')->wasCalledWithArguments($resource, (string) $ip, (string) $port)->once()
				->function('socket_last_error')->wasCalledWithArguments($resource)->once()

			->if($this->function->socket_connect = true)
			->then
				->object($manager->connectSocketTo($resource, $ip, $port))->isIdenticalTo($manager)
				->function('socket_connect')->wasCalledWithArguments($resource, (string) $ip, (string) $port)->once()
		;
	}
}
