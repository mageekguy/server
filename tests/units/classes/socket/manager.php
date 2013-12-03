<?php

namespace server\tests\units\socket;

require __DIR__ . '/../../runner.php';

use
	atoum,
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

	public function test__construct()
	{
		$this
			->given($manager = new testedClass())
			->then
				->variable($manager->getLastErrorCode())->isNull()
				->variable($manager->getLastErrorMessage())->isNull()
		;
	}

	public function testBindTo()
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
				->exception(function() use ($manager) { $manager->bindTo(new network\ip('127.0.0.1'), new network\port(8080)); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_close')->wasCalled()->never()
				->function('socket_last_error')->wasCalledWithArguments(null)->once()
				->integer($manager->getLastErrorCode())->isEqualTo($errorCode)
				->string($manager->getLastErrorMessage())->isEqualTo($errorMessage)

			->if(
				$this->function->socket_create = $socket = uniqid(),
				$this->function->socket_set_option = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->calling($manager)->close->returnThis()
			)
			->then
				->exception(function() use ($manager) { $manager->bindTo(new network\ip('127.0.0.1'), new network\port(8080)); })
					->isInstanceOf('server\socket\manager\exception')
				->mock($manager)->call('close')->withArguments($socket)->once()
				->function('socket_last_error')->wasCalledWithArguments($socket)->once()
				->integer($manager->getLastErrorCode())->isEqualTo($errorCode)
				->string($manager->getLastErrorMessage())->isEqualTo($errorMessage)

			->if(
				$this->function->socket_set_option = true,
				$this->function->socket_bind = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid()
			)
			->then
				->exception(function() use ($manager) { $manager->bindTo(new network\ip('127.0.0.1'), new network\port(8080)); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->mock($manager)->call('close')->withArguments($socket)->once()
				->function('socket_last_error')->wasCalledWithArguments($socket)->once()
				->integer($manager->getLastErrorCode())->isEqualTo($errorCode)
				->string($manager->getLastErrorMessage())->isEqualTo($errorMessage)

			->if(
				$this->function->socket_bind = true,
				$this->function->socket_listen = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid()
			)
			->then
				->exception(function() use ($manager) { $manager->bindTo(new network\ip('127.0.0.1'), new network\port(8080)); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->mock($manager)->call('close')->withArguments($socket)->once()
				->function('socket_last_error')->wasCalledWithArguments($socket)->once()
				->integer($manager->getLastErrorCode())->isEqualTo($errorCode)
				->string($manager->getLastErrorMessage())->isEqualTo($errorMessage)

			->if($this->function->socket_listen = true)
			->then
				->string($manager->bindTo($ip = new network\ip('127.0.0.1'), $port = new network\port(8080)))->isIdenticalTo($socket)
				->mock($manager)->call('close')->withArguments($socket)->never()
				->variable($manager->getLastErrorCode())->isNull()
				->variable($manager->getLastErrorMessage())->isNull()
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

	public function testAccept()
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
				->exception(function() use ($manager, & $socket) { $manager->accept($socket = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_accept')
					->wasCalledWithArguments($socket)
						->before($this->function('socket_last_error')->wasCalledWithArguments($socket)->once())
							->once()
				->integer($manager->getLastErrorCode())->isEqualTo($errorCode)
				->string($manager->getLastErrorMessage())->isEqualTo($errorMessage)

			->if(
				$this->function->socket_accept = $clientSocket = uniqid()
			)
			->then
				->string($manager->accept($socket))->isEqualTo($clientSocket)
				->function('socket_accept')->wasCalledWithArguments($socket)->once()
				->variable($manager->getLastErrorCode())->isNull()
				->variable($manager->getLastErrorMessage())->isNull()
		;
	}

	public function testRead()
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
				->exception(function() use ($manager, & $socket, & $length, & $mode) { $manager->read($socket = uniqid(), $length = rand(1, PHP_INT_MAX), $mode = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_read')
					->wasCalledWithArguments($socket, $length, $mode)
						->before($this->function('socket_last_error')->wasCalledWithArguments($socket)->once())
							->once()
				->integer($manager->getLastErrorCode())->isEqualTo($errorCode)
				->string($manager->getLastErrorMessage())->isEqualTo($errorMessage)

			->if(
				$this->function->socket_read = ''
			)
			->then
				->string($manager->read($socket, $length, $mode))->isEmpty()
				->function('socket_read')->wasCalledWithArguments($socket, $length, $mode)->once()
				->variable($manager->getLastErrorCode())->isNull()
				->variable($manager->getLastErrorMessage())->isNull()

			->if(
				$this->function->socket_read = $data = uniqid()
			)
			->then
				->string($manager->read($socket, $length, $mode))->isEqualTo($data)
				->function('socket_read')->wasCalledWithArguments($socket, $length, $mode)->once()
				->variable($manager->getLastErrorCode())->isNull()
				->variable($manager->getLastErrorMessage())->isNull()
		;
	}

	public function testWrite()
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
				->exception(function() use ($manager, & $socket, & $data) { $manager->write($socket = uniqid(), $data = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_write')
					->wasCalledWithArguments($socket, $data)
						->before($this->function('socket_last_error')->wasCalledWithArguments($socket)->once())
							->once()
				->integer($manager->getLastErrorCode())->isEqualTo($errorCode)
				->string($manager->getLastErrorMessage())->isEqualTo($errorMessage)

			->if(
				$this->function->socket_write = function($socket, $data) { return strlen($data); }
			)
			->then
				->integer($manager->write($socket, $data))->isEqualTo(strlen($data))
				->function('socket_write')->wasCalledWithArguments($socket, $data, strlen($data))->once()
				->variable($manager->getLastErrorCode())->isNull()
				->variable($manager->getLastErrorMessage())->isNull()
				->integer($manager->write($socket, ''))->isEqualTo(0)
				->function('socket_write')->wasCalledWithArguments($socket, '', 0)->once()
				->variable($manager->getLastErrorCode())->isNull()
				->variable($manager->getLastErrorMessage())->isNull()
		;
	}

	public function testGetPeer()
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
				->exception(function() use ($manager, & $socket) { $manager->getPeer($socket = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->function('socket_last_error')
					->wasCalledWithArguments($socket)
						->after($this->function('socket_getpeername')->wasCalledWithArguments($socket)->once())
							->once()
				->integer($manager->getLastErrorCode())->isEqualTo($errorCode)
				->string($manager->getLastErrorMessage())->isEqualTo($errorMessage)

			->if($this->function->socket_getpeername = function($socket, & $ip, & $port) use (& $socketIp, & $socketPort) { $ip = $socketIp = '127.0.0.1'; $port = $socketPort = 8080; return true; })
			->then
				->object($manager->getPeer($socket))->isEqualTo(new network\peer(new network\ip($socketIp), new network\port($socketPort)))
				->function('socket_getpeername')->wasCalledWithArguments($socket)->once()
				->variable($manager->getLastErrorCode())->isNull()
				->variable($manager->getLastErrorMessage())->isNull()
		;
	}

	public function testSelect()
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
				->object($manager->select($read, $write, $except, $timeout = rand(1, PHP_INT_MAX)))->isIdenticalTo($manager)
				->function('socket_select')->wasCalledWithArguments($read, $write, $except, $timeout)->once()
				->variable($manager->getLastErrorCode())->isNull()
				->variable($manager->getLastErrorMessage())->isNull()

			->if(
				$this->function->socket_select = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->function->socket_clear_error->doesNothing()
			)
			->then
				->exception(function() use ($manager, $read, $write, $except, $timeout) { $manager->select($read, $write, $except, $timeout); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->integer($manager->getLastErrorCode())->isEqualTo($errorCode)
				->string($manager->getLastErrorMessage())->isEqualTo($errorMessage)
				->function('socket_last_error')
					->wasCalledWithArguments(null)
						->after($this->function('socket_select')->wasCalledWithArguments($read, $write, $except, $timeout)->once())
							->once()

			->if($this->function->socket_select = 0)
			->then
				->object($manager->select($read, $write, $except, $timeout = rand(1, PHP_INT_MAX)))->isIdenticalTo($manager)
				->function('socket_select')->wasCalledWithArguments($read, $write, $except, $timeout)->once()
				->variable($manager->getLastErrorCode())->isNull()
				->variable($manager->getLastErrorMessage())->isNull()
		;
	}

	public function testClose()
	{
		$this
			->given($manager = new testedClass())

			->if(
				$this->function->socket_set_block = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid(),
				$this->function->socket_clear_error->doesNothing()
			)
			->then
				->exception(function() use ($manager, & $socket) { $manager->close($socket = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->integer($manager->getLastErrorCode())->isEqualTo($errorCode)
				->string($manager->getLastErrorMessage())->isEqualTo($errorMessage)
				->function('socket_close')->wasCalled($socket)->never()
				->function('socket_last_error')->wasCalledWithArguments($socket)->once()

			->if(
				$this->function->socket_set_block = true,
				$this->function->socket_set_option = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid()
			)
			->then
				->exception(function() use ($manager, & $socket) { $manager->close($socket = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->integer($manager->getLastErrorCode())->isEqualTo($errorCode)
				->string($manager->getLastErrorMessage())->isEqualTo($errorMessage)
				->function('socket_close')->wasCalled()->never()
				->function('socket_set_block')->wasCalledWithArguments($socket)
					->before($this->function('socket_set_option')->wasCalledWithArguments($socket, SOL_SOCKET, SO_LINGER, array('l_onoff' => 1, 'l_linger' => 0))->once())
						->once()

			->if(
				$this->function->socket_set_option = true,
				$this->function->socket_shutdown = false,
				$this->function->socket_close = true
			)
			->then
				->object($manager->close($socket = uniqid()))->isIdenticalTo($manager)
				->variable($manager->getLastErrorCode())->isNull()
				->variable($manager->getLastErrorMessage())->isNull()

			->if(
				$this->function->socket_shutdown = true,
				$this->function->socket_close = false,
				$this->function->socket_last_error = $errorCode = rand(1,PHP_INT_MAX),
				$this->function->socket_strerror = $errorMessage = uniqid()
			)
			->then
				->exception(function() use ($manager, & $socket) { $manager->close($socket = uniqid()); })
					->isInstanceOf('server\socket\manager\exception')
					->hasCode($errorCode)
					->hasMessage($errorMessage)
				->integer($manager->getLastErrorCode())->isEqualTo($errorCode)
				->string($manager->getLastErrorMessage())->isEqualTo($errorMessage)
				->function('socket_shutdown')->wasCalledWithArguments($socket)
					->before($this->function('socket_close')->wasCalledWithArguments($socket)->once())
						->once()

			->if($this->function->socket_close = true)
			->then
				->object($manager->close($socket = uniqid()))->isIdenticalTo($manager)
				->variable($manager->getLastErrorCode())->isNull()
				->variable($manager->getLastErrorMessage())->isNull()
		;
	}
}
