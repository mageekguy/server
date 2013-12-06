<?php

namespace server\daemon\payloads\server;

use
	server\network,
	server\daemon\payloads
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

	public function bindForPayload(payloads\server $payload)
	{
		$socket = $payload->bindSocketTo($this->ip, $this->port);

		if ($this->connectHandler !== null)
		{
			$payload->pollSocket($socket)->onRead($this->connectHandler);
		}

		return $socket;
	}
}
