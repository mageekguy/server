<?php

namespace server\script\configurable\daemon;

use
	atoum,
	server\socket,
	server\network,
	server\script\configurable
;

class server extends configurable\daemon
{
	protected $socketManager = null;
	protected $socketSelect = null;
	protected $endpoints = array();

	private $sockets = array();

	public function __construct($name, atoum\adapter $adapter = null)
	{
		parent::__construct($name, $adapter);

		$this
			->setSocketManager()
			->setSocketSelect()
		;
	}

	public function setSocketManager(socket\manager $manager = null)
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

	public function getSocketPeer($socket)
	{
		return $this->socketManager->getPeer($socket);
	}

	public function readSocket($socket, $length, $mode)
	{
		return $this->socketManager->read($socket, $length, $mode);
	}

	public function closeSocket($socket)
	{
		$this->socketManager->close($socket);

		$this->sockets = array_filter($this->sockets, function($serverSocket) use ($socket) { return ($serverSocket !== $socket[0]); });

		return $this;
	}

	public function bindSocketTo(network\ip $ip, network\port $port)
	{
		return $this->addSocket($this->socketManager->bindTo($ip, $port), new network\peer($ip, $port));
	}

	public function acceptSocket($serverSocket)
	{
		return $this->addSocket($this->socketManager->accept($serverSocket));
	}

	protected function runDaemon()
	{
		foreach ($this->endpoints as $endpoint)
		{
			try
			{
				$endpoint->bindForServer($this);
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
				$this->socketSelect->wait(null);
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

	protected function stopDaemon()
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

	protected function addSocket($socket, network\peer $peer = null)
	{
		$this->sockets[] = array($socket, $peer ?: $this->getSocketPeer($socket));

		return $socket;
	}

	protected function getException($message)
	{
		return new server\exception($message);
	}
}
