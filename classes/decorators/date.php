<?php

namespace server\decorators;

use
	server\logger
;

class date implements logger\decorator
{
	public function decorateLog($log)
	{
		return date('Y-m-d H:i:s') . $log;
	}
}
