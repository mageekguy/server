<?php

namespace server\daemon\payloads\server\client;

use
	server
;

class message
{
	protected $data = null;
	protected $buffer = '';
	protected $onRead = null;
	protected $onWrite = null;

	public function __construct($data = null)
	{
		if ($data !== null)
		{
			$this($data);
		}
	}

	public function __toString()
	{
		return (string) $this->data;
	}

	public function __invoke($data)
	{
		$this->data = $data;

		return $this;
	}

	public function onRead(callable $handler)
	{
		$this->onRead = $handler;

		return $this;
	}

	public function readSocket(server\socket $socket)
	{
		if ($this->data !== null)
		{
			$this->data = null;
		}

		$this->buffer .= $socket->read(2048, PHP_NORMAL_READ);

		if ($this->isRead() === true)
		{
			$this->data = $this->buffer;
			$this->buffer = '';
		}

		if ($this->data === null)
		{
			return false;
		}
		else
		{
			if ($this->onRead !== null)
			{
				call_user_func_array($this->onRead, array($this));
			}

			return true;
		}
	}

	public function onWrite(callable $handler)
	{
		$this->onWrite = $handler;

		return $this;
	}

	public function writeSocket(server\socket $socket)
	{
		if ($this->buffer === '' && $this->data !== null)
		{
			$this->buffer = $this->data;
		}

		if ($this->buffer !== '')
		{
			$bytesWritten = $socket->write($this->buffer);

			if ($bytesWritten > 0)
			{
				$this->buffer = (string) substr($this->buffer, $bytesWritten);
			}
		}

		if ($this->buffer !== '')
		{
			return false;
		}
		else
		{
			if ($this->onWrite !== null)
			{
				call_user_func_array($this->onWrite, array($this));
			}

			return true;
		}
	}

	protected function isRead()
	{
		return (substr($this->buffer, -2) === "\r\n");
	}
}
