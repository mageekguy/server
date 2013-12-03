<?php

namespace server\script\configurable\daemon\server;

use
	server\network,
	server\script\configurable\daemon
;

class endpoint
{
	protected $ip = null;
	protected $port = null;
	protected $connectHandler = null;

	public function __construct(network\ip $ip, network\port $port)
	{
		$this
			->setIp($ip)
			->setPort($port)
		;
	}

	public function __toString()
	{
		return $this->ip . ':' . $this->port;
	}

	public function setIp(network\ip $ip)
	{
		$this->ip = $ip;

		return $this;
	}

	public function getIp()
	{
		return $this->ip;
	}

	public function setPort(network\port $port)
	{
		$this->port = $port;

		return $this;
	}

	public function getPort()
	{
		return $this->port;
	}

	public function onConnect(callable $handler)
	{
		$this->connectHandler = $handler;

		return $this;
	}

	public function getConnectHandler()
	{
		return $this->connectHandler;
	}

	public function bindForServer(daemon\server $server)
	{
		$socket = $server->bindSocketTo($this->ip, $this->port);

		if ($this->connectHandler !== null)
		{
			$server->wait($socket)->onRead($this->connectHandler);
		}

		return $socket;
	}
}
