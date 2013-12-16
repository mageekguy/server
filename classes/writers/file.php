<?php

namespace server\writers;

use
	server\logger,
	server\writers\file\exception
;

class file implements logger\writer
{
	protected $resource = null;
	protected $path = '';

	public function __construct($path)
	{
		$this->path = (string) $path;
	}

	public function __toString()
	{
		return (string) $this->path;
	}

	public function __destruct()
	{
		$this->closeFile();
	}

	public function getPath()
	{
		return $this->path;
	}

	public function log($log)
	{
		$this->openFile();

		while (strlen($log) > 0)
		{
			$bytesWritten = @fwrite($this->resource, $log);

			if ($bytesWritten === false)
			{
				throw new exception('Unable to write log \'' . $log . '\' in \'' . $this . '\'');
			}

			$log = substr($log, $bytesWritten);
		}

		return $this;
	}

	public function openFile()
	{
		if ($this->resource === null)
		{
			$resource = @fopen($this->path, 'a');

			if ($resource === false)
			{
				throw new exception('Unable to open \'' . $this . '\'');
			}

			$this->resource = $resource;
		}

		return $this;
	}

	public function closeFile()
	{
		if ($this->resource !== null)
		{
			if (@fclose($this->resource) === false)
			{
				throw new exception('Unable to close \'' . $this . '\'');
			}

			$this->resource = null;
		}

		return $this;
	}
}
