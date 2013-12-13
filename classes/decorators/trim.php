<?php

namespace server\decorators;

use
	server\logger
;

class trim implements logger\decorator
{
	public function prepareToDecorateLog()
	{
		return $this;
	}

	public function decorateLog($log)
	{
		return trim($log);
	}
}
