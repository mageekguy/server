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
	public function __construct($name, atoum\adapter $adapter = null)
	{
		parent::__construct($name, $adapter);

		$this->setPayload(new server\payload());
	}
}
