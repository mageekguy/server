<?php

namespace server\daemon\payloads\server\client;

use
	server
;

class message
{
	private $data = null;
	private $buffer = '';
	private $onRead = null;
	private $onWrite = null;
	private $onError = null;
	private $onSocketClosed = null;

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
		try
		{
			if ($this->data !== null)
			{
				$this->data = null;
			}

			$data = $this->readData($socket);

			if ($data === '')
			{
				throw new message\exception('Socket is closed');
			}

			$this->buffer .= $data;

			if ($this->dataAreRead($this->buffer) === true)
			{
				$this($this->buffer);
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
		catch (\exception $exception)
		{
			if ($this->onError === null)
			{
				throw new message\exception($exception->getMessage(), $exception->getCode());
			}
			else
			{
				call_user_func_array($this->onError, array($this, $exception));

				return false;
			}
		}
	}

	public function getBytesRead()
	{
		return strlen($this->data === null ? $this->buffer : $this->data);
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

	public function getBytesWritten()
	{
		return ($this->data === null ? 0 : strlen($this->data) - strlen($this->buffer));
	}

	public function onError(callable $handler)
	{
		$this->onError = $handler;

		return $this;
	}

	public function onSocketClosed(callable $handler)
	{
		$this->onSocketClosed = $handler;

		return $this;
	}

	protected function dataAreRead($data)
	{
		return (substr($data, -2) === "\r\n");
	}

	protected function readData(server\socket $socket)
	{
		return $socket->read(2048, PHP_NORMAL_READ);
	}
}
