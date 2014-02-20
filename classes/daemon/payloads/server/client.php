<?php

namespace server\daemon\payloads\server;

use
	server,
	server\daemon\payloads
;

class client
{
	const readLength = 2048;

	protected $server = null;
	protected $socket = null;
	protected $onError = array();
	protected $onPush = array();
	protected $readMessages = null;
	protected $currentReadMessage = null;
	protected $writeMessages = null;
	protected $currentWriteMessage = null;

	public function __construct(server\socket $socket, payloads\server $server)
	{
		$this->socket = $socket;
		$this->server = $server;
		$this->readMessages = new client\queue();
		$this->writeMessages = new client\queue();
		$this->socket->bind($this);
	}

	public function __toString()
	{
		return (string) $this->socket;
	}

	public function getServer()
	{
		return $this->server;
	}

	public function readMessage(client\message $message)
	{
		$this->readMessages->addMessage($message);

		if (sizeof($this->readMessages) == 1)
		{
			$this->socket->onReadNotBlock($this->server, array($this, 'readSocket'));
		}

		return $this;
	}

	public function readSocket()
	{
		try
		{
			$data = @$this->socket->read(static::readLength, PHP_BINARY_READ);

			if ($data == '')
			{
				$this->closeSocket();
			}
			else
			{
				foreach ($this->onPush as $pushMessage)
				{
					$pushMessage->readData($this->socket);
				}

				if ($this->currentReadMessage === null)
				{
					$this->currentReadMessage = $this->readMessages->shiftMessage();
				}

				while ($this->currentReadMessage !== null && $this->currentReadMessage->readData($this->socket) === true)
				{
					$this->currentReadMessage = $this->readMessages->shiftMessage();
				}

				if (sizeof($this->onPush) > 0 || $this->currentReadMessage !== null || sizeof($this->readMessages) > 0)
				{
					$this->socket->onReadNotBlock($this->server, array($this, __FUNCTION__));
				}
			}

			return $this;
		}
		catch (\exception $exception)
		{
			return $this->manageException($exception);
		}
	}

	public function writeMessage(client\message $message)
	{
		$this->writeMessages->addMessage($message);

		if (sizeof($this->writeMessages) == 1)
		{
			$this->socket->onWriteNotBlock($this->server, array($this, 'writeSocket'));
		}

		return $this;
	}

	public function writeSocket()
	{
		if ($this->currentWriteMessage === null)
		{
			$this->currentWriteMessage = $this->writeMessages->shiftMessage();
		}

		if ($this->currentWriteMessage !== null)
		{
			try
			{
				if ($this->currentWriteMessage->writeData($this->socket) === false)
				{
					$this->socket->onWriteNotBlock($this->server, array($this, __FUNCTION__));
				}
				else
				{
					$this->currentWriteMessage = null;

					if (sizeof($this->writeMessages) > 0)
					{
						$this->socket->onWriteNotBlock($this->server, array($this, __FUNCTION__));
					}
				}
			}
			catch (\exception $exception)
			{
				return $this->manageException($exception);
			}
		}

		return $this;
	}

	public function closeSocket()
	{
		$this->socket->close();

		return $this;
	}

	public function onPush(client\message $message)
	{
		$this->onPush[] = $message;

		if (sizeof($this->onPush) === 1)
		{
			$this->socket->onReadNotBlock($this->server, array($this, 'readSocket'));
		}

		return $this;
	}

	public function onTimeout(server\socket\timer $timer, callable $handler)
	{
		$this->socket->onTimeout($this->server, $timer, $handler);

		return $this;
	}

	public function onError(callable $handler)
	{
		$this->onError[] = $handler;

		return $this;
	}

	public function removeOnError(callable $handler)
	{
		$onError = $this->onError;
		$this->onError = array();

		foreach ($onError as $onErrorHandler)
		{
			if ($onErrorHandler !== $handler)
			{
				$this->onError($onErrorHandler);
			}
		}

		return $this;
	}

	protected function manageException(\exception $exception)
	{
		if (sizeof($this->onError) <= 0)
		{
			throw new client\exception($exception->getMessage(), $exception->getCode());
		}

		try
		{
			foreach ($this->onError as $onErrorHandler)
			{
				call_user_func_array($onErrorHandler, array($exception));
			}
		}
		catch (\exception $exception)
		{
			throw new client\exception($exception->getMessage(), $exception->getCode());
		}

		return $this;
	}
}
