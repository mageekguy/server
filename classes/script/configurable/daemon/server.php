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
	protected $sockets = array();
	protected $socketManager = null;
	protected $socketSelect = null;
	protected $endpoints = array();

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

		$this->sockets = array_filter($this->sockets, function($serverSocket) use ($socket) { return ($serverSocket !== $socket); });

		return $this;
	}

	public function bindSocketTo(network\ip $ip, network\port $port)
	{
		$this->sockets[] = $socket =  $this->socketManager->bindTo($ip, $port);

		return $socket;
	}

	public function acceptSocket($serverSocket)
	{
		$this->sockets[] = $socket = $this->socketManager->accept($serverSocket);

		return $socket;
	}

	protected function runDaemon()
	{
		foreach ($this->endpoints as $endpoint)
		{
			try
			{
				$this->sockets[] = $endpoint->bindForServer($this);
			}
			catch (\exception $exception)
			{
				$this->writeError($exception->getMessage());
			}

			$this->writeInfo('Accept connection on ' . $endpoint);
		}

		$this->endpoints = array();

		$this->socketSelect->wait(null);
	}

	protected function stopDaemon()
	{
		foreach ($this->sockets as $socket)
		{
			$this->closeSocket($socket);
		}

		$this->sockets = array();

		return $this;
	}

	protected function setArgumentHandlers()
	{
		parent::setArgumentHandlers()
			->addArgumentHandler(
					function($script, $argument, $values) {
						if (sizeof($values) !== 1)
						{
							throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
						}

						$script->setUid(reset($values));
					},
					array('-u', '--uid'),
					null,
					$this->locale->_('Define UID')
				)
			->addArgumentHandler(
					function($script, $argument, $values) {
						if (sizeof($values) !== 1)
						{
							throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
						}

						$script->setHome(reset($values));
					},
					array('-H', '--home'),
					null,
					$this->locale->_('Define home')
				)
		;

		return $this;
	}
}
