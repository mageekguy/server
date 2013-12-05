<?php

namespace server\demo\payloads;

use
	server\network,
	server\script\configurable\daemon\server\payload
;

class server extends payload
{
	protected $clientsEndpoint = null;

	public function __construct()
	{
		parent::__construct();

		$this->clientsEndpoint = (new payload\endpoint(new network\ip('192.168.0.1'), new network\port(8080)))->onConnect(array($this, 'acceptClient'));

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
		$this->wait($clientSocket = $this->acceptSocket($clientsSocket))->onRead(array($this, 'readClient'));

		return $this->writeInfo('Accept peer ' . $this->getSocketPeer($clientSocket));
	}

	public function readClient($socket)
	{
		$data = $this->readSocket($socket, 2048, PHP_BINARY_READ);

		$this->writeInfo('Receive \'' . $data . '\' from peer ' . $this->getSocketPeer($socket));

		if ($data !== '')
		{
			$this->wait($socket)->onRead(array($this, __FUNCTION__));
		}
		else
		{
			$this
				->writeInfo('Close connection with ' . $this->getSocketPeer($socket))
				->closeSocket($socket)
			;
		}

		return $this;
	}
}