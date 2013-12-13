<?php

namespace server\decorators;

use
	server\logger
;

class id implements logger\decorator
{
	protected $currentId = null;

	public function prepareToDecorateLog()
	{
		$this->currentId = static::getId();

		return $this;
	}

	public function decorateLog($log)
	{
		return ($this->currentId ?: static::getId()) . $log;
	}

	private static function getId()
	{
		return uniqid('', true);
	}
}
