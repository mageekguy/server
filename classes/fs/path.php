<?php

namespace server\fs;

use
	server\socket
;

class path implements socket\name
{
	private $value = '';

	public function __construct($value)
	{
		$this->value = $value;
	}

	public function __toString()
	{
		return (string) $this->value;
	}
}
