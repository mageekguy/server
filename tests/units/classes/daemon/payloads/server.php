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
	public function beforeTestMethod($method)
	{
		$test = $this;

		$this->then = function() use ($test) { $test->getTestAdapterStorage()->resetCalls(); return $test; };
	}

	public function testClass()
	{
		$this->testedClass
			->implements('server\socket\manager\definition')
			->implements('server\socket\poller\definition')
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

	public function testSetSocketPoller()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->setSocketPoller($poller = new socket\poller()))->isIdenticalTo($server)
				->object($server->getSocketPoller())->isIdenticalTo($poller)
				->object($server->setSocketPoller())->isIdenticalTo($server)
				->object($server->getSocketPoller())
					->isNotIdenticalTo($poller)
					->isEqualTo(new socket\poller())
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

	public function testPollSocket()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketPoller($poller = new \mock\server\socket\poller())
			)

			->if($this->calling($poller)->pollSocket = $socketEvents = new socket\events())
			->then
				->object($server->pollSocket($socket = uniqid()))->isIdenticalTo($socketEvents)
				->mock($poller)->call('pollSocket')->withArguments($socket)->once()
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

	public function testIsSocket()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->isSocket = false)
			->then
				->boolean($server->isSocket($var = uniqid()))->isFalse()
				->mock($socketManager)->call('isSocket')->withArguments($var)->once()

			->if($this->calling($socketManager)->isSocket = true)
			->then
				->boolean($server->isSocket($var = uniqid()))->isTrue()
				->mock($socketManager)->call('isSocket')->withArguments($var)->once()
		;
	}

	public function testRelease()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server
					->setSocketPoller($socketPoller = new \mock\server\socket\poller())
					->setSocketManager($socketManager = new \mock\server\socket\manager())
					->setInfoLogger($infoLogger = new \mock\server\logger())
					->setErrorLogger($errorLogger = new \mock\server\logger())
			)
			->then
				->exception(function() use ($server) { $server->release(); })
					->isInstanceOf('server\daemon\payloads\server\exception')
					->hasMessage('Unable to bind endpoints')
				->mock($socketPoller)->wasNotCalled()
				->mock($socketManager)->wasNotCalled()

			->if(
				$server
					->addEndpoint($endpoint1 = (new \mock\server\daemon\payloads\server\endpoint(new network\ip('127.0.0.1'), new network\port(8081)))->onConnect($connectHandler1 = function() {}))
					->addEndpoint($endpoint2 = (new \mock\server\daemon\payloads\server\endpoint(new network\ip('127.0.0.2'), new network\port(8082)))->onConnect($connectHandler2 = function() {})),
				$this->calling($socketManager)->bindSocketTo[1] = $serverSocket1 = uniqid(),
				$this->calling($socketManager)->bindSocketTo[2] = $serverSocket2 = uniqid(),
				$this->calling($socketPoller)->pollSocket[1] = $events1 = new \mock\server\socket\events(),
				$this->calling($socketPoller)->pollSocket[2] = $events2 = new \mock\server\socket\events(),
				$this->calling($socketPoller)->waitSockets->doesNothing()
			)
			->then
				->object($server->release())->isIdenticalTo($server)
				->mock($endpoint1)->call('bindForPayload')->withArguments($server)->once()
				->mock($endpoint2)->call('bindForPayload')->withArguments($server)->once()
				->array($server->getEndpoints())->isEmpty()
				->mock($socketPoller)
					->call('pollSocket')
						->withArguments($serverSocket1)
							->before($this->mock($events1)->call('onRead')->withArguments($connectHandler1)->once())
								->once()
					->call('pollSocket')
						->withArguments($serverSocket2)
							->before($this->mock($events2)->call('onRead')->withArguments($connectHandler2)->once())
								->once()
					->call('waitSockets')->once()
				->mock($events2)->call('onRead')->withArguments($connectHandler2)->once()
				->mock($socketManager)->call('bindSocketTo')
					->withArguments($endpoint1->getIp(), $endpoint1->getPort())->once()
					->withArguments($endpoint2->getIp(), $endpoint2->getPort())->once()
				->mock($infoLogger)
					->call('log')
						->withArguments('Accept connection on ' . $endpoint1 . '…')->once()
						->withArguments('Accept connection on ' . $endpoint2 . '…')->once()

			->if(
				$server
					->addEndpoint((new \mock\server\daemon\payloads\server\endpoint(new network\ip('127.0.0.1'), new network\port(8081)))->onConnect(function() {}))
					->addEndpoint((new \mock\server\daemon\payloads\server\endpoint(new network\ip('227.0.0.2'), new network\port(8082)))->onConnect(function() {})),
				$this->calling($socketPoller)->waitSockets->throw = $exception = new \exception(uniqid(), rand(1, PHP_INT_MAX))
			)
			->then
				->exception(function() use ($server) { $server->release(); })
					->isInstanceOf('server\daemon\payloads\server\exception')
					->hasCode($exception->getCode())
					->hasMessage($exception->getMessage())

			->if(
				$server
					->addEndpoint((new \mock\server\daemon\payloads\server\endpoint(new network\ip('127.0.0.1'), new network\port(8081)))->onConnect(function() {}))
					->addEndpoint((new \mock\server\daemon\payloads\server\endpoint(new network\ip('227.0.0.2'), new network\port(8082)))->onConnect(function() {})),
				$this->calling($socketPoller)->waitSockets->throw = $exception = new socket\poller\exception(uniqid(), rand(1, 3))
			)
			->then
				->exception(function() use ($server) { $server->release(); })
					->isInstanceOf('server\daemon\payloads\server\exception')
					->hasCode($exception->getCode())
					->hasMessage($exception->getMessage())

			->if(
				$server
					->addEndpoint((new \mock\server\daemon\payloads\server\endpoint(new network\ip('127.0.0.1'), new network\port(8081)))->onConnect(function() {}))
					->addEndpoint((new \mock\server\daemon\payloads\server\endpoint(new network\ip('227.0.0.2'), new network\port(8082)))->onConnect(function() {})),
				$this->calling($socketPoller)->waitSockets->throw = $exception = new socket\poller\exception(uniqid(), rand(5, PHP_INT_MAX))
			)
			->then
				->exception(function() use ($server) { $server->release(); })
					->isInstanceOf('server\daemon\payloads\server\exception')
					->hasCode($exception->getCode())
					->hasMessage($exception->getMessage())

			->if(
				$server
					->addEndpoint($endpoint1 = (new \mock\server\daemon\payloads\server\endpoint(new network\ip('127.0.0.1'), new network\port(8081)))->onConnect(function() {}))
					->addEndpoint($endpoint2 = (new \mock\server\daemon\payloads\server\endpoint(new network\ip('227.0.0.2'), new network\port(8082)))->onConnect(function() {})),
				$this->calling($socketPoller)->waitSockets->throw = $exception = new socket\poller\exception(uniqid(), 4 /* When a UNIX signal is received by socket_select(), PHP generate a socket error "Interrupted System Call" with code 4! */)
			)
			->then
				->object($server->release())->isIdenticalTo($server)
				->mock($endpoint1)->call('bindForPayload')->withArguments($server)->once()
				->mock($endpoint2)->call('bindForPayload')->withArguments($server)->once()
				->array($server->getEndpoints())->isEmpty()
				->mock($socketPoller)
					->call('pollSocket')
						->withArguments($serverSocket1)->once()
						->withArguments($serverSocket2)->once()
					->call('waitSockets')->once()
				->mock($socketManager)->call('bindSocketTo')
					->withArguments($endpoint1->getIp(), $endpoint1->getPort())->once()
					->withArguments($endpoint2->getIp(), $endpoint2->getPort())->once()

			->if(
				$server
					->addEndpoint($endpoint1 = (new \mock\server\daemon\payloads\server\endpoint(new network\ip('127.0.0.1'), new network\port(8081)))->onConnect($connectHandler1 = function() {}))
					->addEndpoint($endpoint2 = (new \mock\server\daemon\payloads\server\endpoint(new network\ip('127.0.0.2'), new network\port(8082)))->onConnect($connectHandler2 = function() {})),
				$this->calling($socketManager)->bindSocketTo[1] = $serverSocket1 = uniqid(),
				$this->calling($socketManager)->bindSocketTo[2]->throw = $exception = new \exception(uniqid())
			)
			->then
				->object($server->release())->isIdenticalTo($server)
				->mock($errorLogger)->call('log')->withArguments($exception->getMessage())->once()

		;
	}

	public function testDeactivate()
	{
		$this
			->given(
				$server = new testedClass(uniqid()),
				$server
					->setSocketPoller($socketPoller = new \mock\server\socket\poller())
					->setSocketManager($socketManager = new \mock\server\socket\manager())
					->setInfoLogger($infoLogger = new \mock\server\logger())
			)
			->then
				->object($server->deactivate())->isIdenticalTo($server)
				->mock($socketPoller)->wasNotCalled()
				->mock($socketManager)->wasNotCalled()
				->mock($infoLogger)
					->call('log')
						->withArguments('Stop server…')->once()
						->withArguments('Server stopped')->once()

			->if(
				$server
					->addEndpoint($endpoint1 = (new \mock\server\daemon\payloads\server\endpoint($ip1 = new network\ip('127.0.0.1'), $port1 = new network\port(8081)))->onConnect(function() {}))
					->addEndpoint($endpoint2 = (new \mock\server\daemon\payloads\server\endpoint($ip2 = new network\ip('227.0.0.2'), $port2 = new network\port(8082)))->onConnect(function() {})),
				$this->calling($socketManager)->bindSocketTo[1] = $serverSocket1 = uniqid(),
				$this->calling($socketManager)->bindSocketTo[2] = $serverSocket2 = uniqid(),
				$this->calling($socketPoller)->waitSockets->doesNothing(),
				$server->release()
			)
			->then
				->object($server->deactivate())->isIdenticalTo($server)
				->mock($socketPoller)->wasNotCalled()
				->mock($socketManager)
					->call('closeSocket')
						->withArguments($serverSocket1)->once()
						->withArguments($serverSocket2)->once()
				->mock($infoLogger)
					->call('log')
						->withArguments('Stop server…')->once()
						->withArguments('Connection on ' . (new network\peer($ip1, $port1)) . ' closed!')->once()
						->withArguments('Connection on ' . (new network\peer($ip2, $port2)) . ' closed!')->once()
						->withArguments('Server stopped')->once()
		;
	}
}
