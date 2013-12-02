<?php

namespace server\decorators;

use
	server\logger
;

class prefix implements logger\decorator
{
	protected $prefix = '';

	public function __construct($prefix)
	{
		$this->prefix = $prefix;
	}

	public function decorateLog($log)
	{
		return $this->prefix . $log;
	}
}
