<?php

namespace server\daemon\payloads;

use
	server\socket,
	server\network,
	server\daemon
;

class server extends daemon\payload implements socket\manager\definition
{
	protected $socketManager = null;
	protected $socketSelect = null;
	protected $endpoints = array();

	private $sockets = array();

	public function __construct()
	{
		$this
			->setInfoLogger()
			->setErrorLogger()
			->setSocketManager()
			->setSocketSelect()
		;
	}

	public function setSocketManager(socket\manager\definition $manager = null)
	{
		$this->socketManager = $manager ?: new socket\manager();

		return $this;
	}

	public function getSocketManager()
	{
		return $this->socketManager;
	}

	public function setSocketSelect(socket\select $select = null)
	{
		$this->socketSelect = $select ?: new socket\select();

		return $this;
	}

	public function getSocketSelect()
	{
		return $this->socketSelect;
	}

	public function addEndpoint(server\endpoint $endpoint)
	{
		$this->endpoints[(string) $endpoint] = $endpoint;

		return $this;
	}

	public function getEndpoints()
	{
		return array_values($this->endpoints);
	}

	public function wait($socket)
	{
		return $this->socketSelect->socket($socket);
	}

	public function getLastSocketErrorCode()
	{
		return $this->socketManager->getLastSocketErrorCode();
	}

	public function getLastSocketErrorMessage()
	{
		return $this->socketManager->getLastSocketErrorMessage();
	}

	public function getSocketPeer($socket)
	{
		return $this->socketManager->getSocketPeer($socket);
	}

	public function pollSockets(array & $read, array & $write, array & $except, $timeout)
	{
		$this->socketManager->pollSockets($read, $write, $except, $timeout);

		return $this;
	}

	public function readSocket($socket, $length, $mode)
	{
		return $this->socketManager->readSocket($socket, $length, $mode);
	}

	public function writeSocket($socket, $data)
	{
		return $this->socketManager->writeSocket($socket, $data);
	}

	public function closeSocket($socket)
	{
		$this->socketManager->closeSocket($socket);

		$this->sockets = array_filter($this->sockets, function($serverSocket) use ($socket) { return ($serverSocket[0] !== $socket); });

		return $this;
	}

	public function bindSocketTo(network\ip $ip, network\port $port)
	{
		return $this->addSocket($this->socketManager->bindSocketTo($ip, $port), new network\peer($ip, $port));
	}

	public function acceptSocket($serverSocket)
	{
		return $this->addSocket($this->socketManager->acceptSocket($serverSocket));
	}

	public function execute()
	{
		foreach ($this->endpoints as $endpoint)
		{
			try
			{
				$endpoint->bindForPayload($this);
			}
			catch (\exception $exception)
			{
				$this->writeError($exception->getMessage());
			}

			$this->writeInfo('Accept connection on ' . $endpoint . '…');
		}

		$this->endpoints = array();

		if (sizeof($this->sockets) > 0)
		{
			try
			{
				$this->socketSelect->wait();
			}
			catch (socket\manager\exception $exception)
			{
				if ($exception->getCode() != 4)
				{
					throw $exception;
				}
			}
		}

		return $this;
	}

	public function destruct()
	{
		$this->writeInfo('Stop server…');

		foreach ($this->sockets as $socket)
		{
			list($socket, $peer) = $socket;

			$this
				->closeSocket($socket)
				->writeInfo('Connection on ' . $peer . ' closed!');
			;
		}

		$this->sockets = array();

		return $this->writeInfo('Server stopped');
	}

	public function writeInfo($info)
	{
		$this->infoLogger->log($info);

		return $this;
	}

	public function writeError($error)
	{
		$this->errorLogger->log($error);

		return $this;
	}

	protected function addSocket($socket, network\peer $peer = null)
	{
		$this->sockets[] = array($socket, $peer ?: $this->getSocketPeer($socket));

		return $socket;
	}
}
