<?php

namespace server\decorators;

use
	server\logger
;

class date implements logger\decorator
{
	protected $currentDate = null;

	public function prepareToDecorateLog()
	{
		$this->currentDate = static::getDate();

		return $this;
	}

	public function decorateLog($log)
	{
		return ($this->currentDate ?: static::getDate()) . $log;
	}

	private static function getDate()
	{
		return date('Y-m-d H:i:s');
	}
}
