<?php

namespace server\socket;

use
	server\fs,
	server\network
;

class manager implements manager\definition
{
	const resourceType = 'Socket';

	public function getSocketPeer($socket)
	{
		if (@socket_getpeername($socket, $address, $port) === false)
		{
			throw $this->getException($socket);
		}

		return ($port === null ? new fs\path($address) : new network\peer(new network\ip($address), new network\port($port)));
	}

	public function getSocketName($socket)
	{
		if (@socket_getsockname($socket, $address, $port) === false)
		{
			throw $this->getException($socket);
		}

		return ($port === null ? new fs\path($address) : new network\peer(new network\ip($address), new network\port($port)));
	}

	public function createSocket($domain, $type, $protocol)
	{
		$socket = socket_create($domain, $type, $protocol);

		if ($socket === false)
		{
			throw $this->getException();
		}

		return $socket;
	}

	public function bindSocketTo(network\ip $ip, network\port $port)
	{
		$socket = $this->createSocket(AF_INET, SOCK_STREAM, SOL_TCP);

		try
		{
			switch (true)
			{
				case socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1) === false:
				case socket_bind($socket, (string) $ip, (string) $port) === false:
				case socket_listen($socket) === false:
					throw $this->getException($socket);

				default:
					return $socket;
			}
		}
		catch (\exception $exception)
		{
			$this->closeSocket($socket);

			throw $exception;
		}
	}

	public function connectSocketTo($socket, network\ip $ip, network\port $port)
	{
		if (socket_connect($socket, (string) $ip, (string) $port) === false)
		{
			throw $this->getException($socket);
		}

		return $this;
	}

	public function acceptSocket($serverSocket)
	{
		$socket = socket_accept($serverSocket);

		if ($socket === false)
		{
			throw $this->getException($serverSocket);
		}

		return $socket;
	}

	public function readSocket($socket, $length, $mode)
	{
		$data = socket_read($socket, $length, $mode);

		if ($data === false)
		{
			throw $this->getException($socket);
		}

		return $data;
	}

	public function writeSocket($socket, $data)
	{
		$data = (string) $data;

		$bytesWritten = socket_write($socket, $data, strlen($data));

		if ($bytesWritten === false)
		{
			throw $this->getException($socket);
		}

		return $bytesWritten;
	}

	public function pollSockets(array & $read, array & $write, array & $except, $timeout)
	{
		if (@socket_select($read, $write, $except, $timeout) === false)
		{
			throw $this->getException();
		}

		return $this;
	}

	public function closeSocket($socket)
	{
		if ($this->isSocket($socket) === true)
		{
			@socket_set_block($socket);
			@socket_set_option($socket, SOL_SOCKET, SO_LINGER, array('l_onoff' => 1, 'l_linger' => 0));
			@socket_shutdown($socket, 2);

			if (@socket_close($socket) === false)
			{
				throw $this->getException($socket);
			}
		}

		return $this;
	}

	public function isSocket($var)
	{
		return (is_resource($var) === true && @get_resource_type($var) === self::resourceType);
	}

	private function getException($socket = null)
	{
		$lastErrorCode = ($socket === null ? socket_last_error() : socket_last_error($socket));
		$lastErrorMessage = socket_strerror($lastErrorCode);

		socket_clear_error();

		return new manager\exception($lastErrorMessage, $lastErrorCode);
	}
}
