<?php

namespace server\demo\scripts\server;

use
	server,
	server\socket,
	server\network,
	server\daemon\payloads
;

class payload extends payloads\server
{
	protected $clientsEndpoint = null;

	public function __construct()
	{
		parent::__construct();

		$this->clientsEndpoint = (new payloads\server\endpoint(new network\ip('192.168.0.1'), new network\port(8080)))->onConnect(array($this, 'acceptClient'));

		$this->addEndpoint($this->clientsEndpoint);
	}

	public function setClientsIp(network\ip $ip)
	{
		$this->clientsEndpoint->setIp($ip);

		return $this;
	}

	public function getClientsIp()
	{
		return $this->clientsEndpoint->getIp();
	}

	public function setClientsPort(network\port $port)
	{
		$this->clientsEndpoint->setPort($port);

		return $this;
	}

	public function getClientsPort()
	{
		return $this->clientsEndpoint->getPort();
	}

	public function acceptClient($clientsSocket)
	{
		$this->pollSocket($clientsSocket)->onRead(array($this, __FUNCTION__));

		$clientSocket = new server\socket($resource = $this->acceptSocket($clientsSocket), $this);

		$timeoutHandler = function($clientSocket) {
				$this->writeInfo('Client ' . $clientSocket . ' timeout!');

				$clientSocket->close();
			}
		;

		$readHandler = function($clientSocket) use ($timeoutHandler, & $readHandler) {
				$data = $clientSocket->read(2048, PHP_BINARY_READ);

				$this->writeInfo('Receive \'' . trim($data) . '\' from peer ' . $clientSocket);

				if ($data === '')
				{
					$this->writeInfo('Client ' . $clientSocket . ' disconnected!');

					$clientSocket->close();
				}
				else
				{
					$clientSocket
						->onRead($this, $readHandler)
						->onWrite($this, function($clientSocket) use ($data) { $clientSocket->write(str_rot13($data)); })
						->onTimeout($this, new socket\timer(60), $timeoutHandler)
					;
				}
			}
		;

		$clientSocket
			->onRead($this, $readHandler)
			->onTimeout($this, new socket\timer(60), $timeoutHandler)
		;

		return $this->writeInfo('Accept peer ' . $clientSocket);
	}
}
