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
			$this->data = null;

			$data = $this->getData($socket);

			if ($data !== null)
			{
				$this($data);

				if ($this->onRead !== null)
				{
					call_user_func_array($this->onRead, array($this));
				}
			}

			return ($this->data !== null);
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

	protected function getData(server\socket $socket)
	{
		$data = $socket->peekData('/^.*' . "\r\n" . '/');

		if ($data !== null)
		{
			$data = $data[0];
		}

		return $data;
	}
}
