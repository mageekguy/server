<?php

namespace server\socket;

class manager
{
	protected $lastErrorCode = null;
	protected $lastErrorMessage = null;

	public function getLastErrorCode()
	{
		return $this->lastErrorCode;
	}

	public function getLastErrorMessage()
	{
		return $this->lastErrorMessage;
	}

	public function getPeerName($socket)
	{
		$this->resetLastError();

		if (@socket_getpeername($socket, $ip, $port) === false)
		{
			throw $this->getException($socket);
		}

		return array($ip, $port);
	}

	public function bindTo($ip, $port)
	{
		$this->resetLastError();

		$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		if ($socket === false)
		{
			throw $this->getException();
		}

		try
		{
			if (
				@socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1) === false
				||
				@socket_bind($resource, $ip, $port) === false
				||
				@socket_listen($resource) === false
			)
			{
				throw $this->getException($socket);
			}

			return $socket;
		}
		catch (\exception $exception)
		{
			$this->close($socket);

			throw $exception;
		}
	}

	public function accept($serverSocket)
	{
		$this->resetLastError();

		$socket = socket_accept($serverSocket);

		if ($socket === false)
		{
			throw $this->getException($serverSocket);
		}

		return $socket;
	}

	public function read($socket, $length, $mode)
	{
		$this->resetLastError();

		$data = socket_read($socket, $length, $mode);

		if ($data === false)
		{
			throw $this->getException($socket);
		}

		return $data;
	}

	public function write($socket, $data)
	{
		$this->resetLastError();

		$bytesWritten = socket_write($socket, $data, strlen($data));

		if ($bytesWritten === false)
		{
			throw $this->getException($socket);
		}

		return $bytesWritten;
	}

	public function select(array & $read, array & $write, array & $except, $timeout)
	{
		$this->resetLastError();

		if (@socket_select($read, $write, $except, $timeout) === false)
		{
			throw $this->getException();
		}

		return $this;
	}

	public function close($socket)
	{
		$this->resetLastError();

		if (
			@socket_set_block($socket) === false
			||
			@socket_set_option($socket, SOL_SOCKET, SO_LINGER, array('l_onoff' => 1, 'l_linger' => 0)) === false
			||
			@socket_shutdown($socket, 2) === false
			||
			@socket_close($socket) === false
		)
		{
			throw $this->getException($socket);
		}

		return $this;
	}

	private function getException($socket = null)
	{
		$this->lastErrorCode = socket_last_error($socket);
		$this->lastErrorMessage = socket_strerror($this->lastErrorCode);

		socket_clear_error();

		return new manager\exception($this->lastErrorMessage, $this->lastErrorCode);
	}

	protected function resetLastError()
	{
		$this->lastErrorCode = $this->lastErrorMessage = null;

		return $this;
	}
}
