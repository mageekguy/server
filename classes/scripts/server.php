<?php

namespace server\scripts;

use
	atoum,
	server\network,
	server\script\configurable\daemon
;

class server extends daemon\server
{
	protected $trackersEndpoint = null;

	public function __construct($name, atoum\adapter $adapter = null)
	{
		parent::__construct($name, $adapter);

		$this->trackersEndpoint = (new daemon\server\endpoint(new network\ip('192.168.0.1'), new network\port(8080)))->onConnect(array($this, 'acceptTracker'));

		$this->addEndpoint($this->trackersEndpoint);
	}

	public function setTrackersIp(network\ip $ip)
	{
		$this->trackersEndpoint->setIp($ip);

		return $this;
	}

	public function getTrackersIp()
	{
		return $this->trackersEndpoint->getIp();
	}

	public function setTrackersPort(network\port $port)
	{
		$this->trackersEndpoint->setPort($port);

		return $this;
	}

	public function getTrackersPort()
	{
		return $this->trackersEndpoint->getPort();
	}

	public function acceptTracker($trackersSocket)
	{
		$this->wait($trackersSocket)->onRead(array($this, __FUNCTION__));
		$this->wait($trackerSocket = $this->acceptSocket($trackersSocket))->onRead(array($this, 'readTracker'));

		return $this->writeInfo('Accept peer ' . $this->getSocketPeer($trackerSocket));
	}

	public function readTracker($socket)
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

						$script->setTrackersIp($ip);
					},
					array('-ti', '--trackers-ip'),
					null,
					$this->locale->_('Define trackers IP')
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

						$script->setTrackersPort($port);
					},
					array('-tp', '--trackers-port'),
					null,
					$this->locale->_('Define trackers port')
				)
		;

		return $this;
	}
}
