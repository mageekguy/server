<?php

namespace server\demo\scripts\server;

use
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
		$this->wait($clientsSocket)->onRead(array($this, __FUNCTION__));
		$this->wait($clientSocket = $this->acceptSocket($clientsSocket))
			->onRead(array($this, 'readClient'))
			->onTimeout(new socket\timer(60), array($this, 'clientTimeout'))
		;

		return $this->writeInfo('Accept peer ' . $this->getSocketPeer($clientSocket));
	}

	public function readClient($socket)
	{
		$data = $this->readSocket($socket, 2048, PHP_BINARY_READ);

		$this->writeInfo('Receive \'' . trim($data) . '\' from peer ' . $this->getSocketPeer($socket));

		if ($data !== '')
		{
			$this->wait($socket)
				->onRead(array($this, __FUNCTION__))
				->onTimeout(new socket\timer(60), array($this, 'clientTimeout'))
			;
		}
		else
		{
			$this->closeClient($socket);
		}

		return $this;
	}


	public function clientTimeout($socket)
	{
		return $this
			->writeInfo('Client ' . $this->getSocketPeer($socket) . ' timeout!')
			->closeClient($socket)
		;
	}

	public function closeClient($socket)
	{
		$this
			->writeInfo('Close connection with ' . $this->getSocketPeer($socket))
			->closeSocket($socket)
		;

		return $this;
	}
}
