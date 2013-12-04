<?php

namespace server\unix\user;

class home
{
	protected $path = '';

	public function __construct($path = null)
	{
		if ($path === null)
		{
			$path = getcwd();
		}

		$this->setPath($path);
	}

	public function __toString()
	{
		return $this->path;
	}

	public function setPath($path)
	{
		$this->path = (string) $path;

		return $this;
	}

	public function go()
	{
		if (@chdir($this->path) === false)
		{
			throw $this->getException('Unable to go to directory \'' . $this . '\'');
		}
	}

	protected function getException($message)
	{
		return new home\exception($message);
	}
}
