<?php

namespace server\script\configurable\daemon\server;

use
	server\network,
	server\script\configurable\daemon
;

class endpoint extends network\peer
{
	protected $connectHandler = null;

	public function setIp(network\ip $ip)
	{
		$this->ip = $ip;

		return $this;
	}

	public function setPort(network\port $port)
	{
		$this->port = $port;

		return $this;
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
