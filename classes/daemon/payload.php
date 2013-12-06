<?php

namespace server\daemon;

use
	server
;

abstract class payload
{
	protected $infoLogger = null;
	protected $errorLogger = null;

	public function setInfoLogger(server\logger $logger = null)
	{
		$this->infoLogger = $logger ?: new server\logger();

		return $this;
	}

	public function getInfoLogger()
	{
		return $this->infoLogger;
	}

	public function setErrorLogger(server\logger $logger = null)
	{
		$this->errorLogger = $logger ?: new server\logger();

		return $this;
	}

	public function getErrorLogger()
	{
		return $this->errorLogger;
	}

	public abstract function activate();
	public abstract function release();
	public abstract function deactivate();
}
