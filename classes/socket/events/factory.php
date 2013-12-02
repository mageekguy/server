<?php

namespace server\socket\events;

use
	server\socket
;

class factory
{
	public function build()
	{
		return new socket\events();
	}
}
