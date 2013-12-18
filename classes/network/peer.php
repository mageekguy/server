<?php

namespace server\network;

use
	server\socket,
	server\network
;

class peer implements socket\name
{
	protected $ip = null;
	protected $port = null;

	public function __construct(network\ip $ip, network\port $port)
	{
		$this->ip = $ip;
		$this->port = $port;
	}

	public function __toString()
	{
		return $this->ip . ':' . $this->port;
	}

	public function getIp()
	{
		return $this->ip;
	}

	public function getPort()
	{
		return $this->port;
	}
}
