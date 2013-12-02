<?php

namespace server\decorators;

use
	server\logger
;

class eol implements logger\decorator
{
	public function decorateLog($log)
	{
		return $log . PHP_EOL;
	}
}
