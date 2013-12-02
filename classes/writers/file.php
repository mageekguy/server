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

	public function __destruct()
	{
		if ($this->resource !== null)
		{
			fclose($this->resource);

			$this->resource = null;
		}
	}

	public function getPath()
	{
		return $this->path;
	}

	public function log($log)
	{
		if ($this->resource === null)
		{
			$resource = @fopen($this->path, 'a');

			if ($resource === false)
			{
				throw new exception('Unable to write log \'' . $log . '\' in \'' . $this->path . '\'');
			}

			$this->resource = $resource;
		}

		while (strlen($log) > 0)
		{
			$bytesWritten = @fwrite($this->resource, $log);

			if ($bytesWritten === false)
			{
				throw new exception('Unable to write log \'' . $log . '\' in \'' . $this->path . '\'');
			}

			$log = substr($log, $bytesWritten);
		}

		return $this;
	}
}
