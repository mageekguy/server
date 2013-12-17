<?php

namespace server\readers;

use
	server\readers\file\exception
;

class file
{
	protected $resource = null;
	protected $path = '';

	public function __construct($path)
	{
		$this->path = (string) $path;
	}

	public function __destruct()
	{
		$this->closeFile();
	}

	public function __toString()
	{
		return (string) $this->path;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function openFile()
	{
		if ($this->resource === null)
		{
			$resource = @fopen($this->path, 'r');

			if ($resource === false)
			{
				throw new exception('Unable to open \'' . $this . '\'');
			}

			$this->resource = $resource;
		}

		return $this;
	}

	public function readFromFile($length)
	{
		$data = fread($this->openFile()->resource, $length);

		if ($data === false)
		{
			throw new exception('Unable to read ' . $length . ' bytes from \'' . $this . '\'');
		}

		return $data;
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
