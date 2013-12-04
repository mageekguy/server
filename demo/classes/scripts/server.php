<?php

namespace server\demo\scripts;

use
	atoum,
	server\network,
	server\script\configurable\daemon
;

class server extends daemon\server
{
	protected $clientsEndpoint = null;

	public function __construct($name, atoum\adapter $adapter = null)
	{
		parent::__construct($name, $adapter);

		$this->clientsEndpoint = (new daemon\server\endpoint(new network\ip('192.168.0.1'), new network\port(8080)))->onConnect(array($this, 'acceptClient'));

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

	protected function setArgumentHandlers()
	{
		parent::setArgumentHandlers()
			->addArgumentHandler(
					function($script, $argument, $values) {
						if (sizeof($values) !== 1)
						{
							throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
						}

						try
						{
							$ip = new network\ip(reset($values));
						}
						catch (network\ip\exception $exception)
						{
							throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('IP \'%s\' is invalid'), reset($values)));
						}

						$script->setClientsIp($ip);
					},
					array('-ci', '--clients-ip'),
					null,
					$this->locale->_('Define clients IP')
				)
			->addArgumentHandler(
					function($script, $argument, $values) {
						if (sizeof($values) !== 1)
						{
							throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
						}

						try
						{
							$port = new network\port(reset($values));
						}
						catch (network\ip\exception $exception)
						{
							throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Port \'%s\' is invalid'), reset($values)));
						}

						$script->setClientsPort($port);
					},
					array('-cp', '--clients-port'),
					null,
					$this->locale->_('Define clients port')
				)
		;

		return $this;
	}
}
