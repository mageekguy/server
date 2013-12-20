<?php

namespace server\socket;

use
	server
;

class unix extends server\socket
{
	public function __toString()
	{
		try
		{
			return (string) $this->getName();
		}
		catch (\exception $exception)
		{
			return '';
		}
	}
}
