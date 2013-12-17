<?php

namespace server\daemon\payloads\server;

use
	server,
	server\daemon\payloads
;

class client
{
	protected $socket = null;
	protected $server = null;
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
			$this->socket->onRead($this->server, array($this, 'readSocket'));
		}

		return $this;
	}

	public function readSocket()
	{
		if ($this->currentReadMessage === null)
		{
			$this->currentReadMessage = $this->readMessages->shiftMessage();
		}

		if ($this->currentReadMessage !== null)
		{
			if ($this->currentReadMessage->readSocket($this->socket) === false)
			{
				$this->socket->onRead($this->server, array($this, __FUNCTION__));
			}
			else
			{
				$this->currentReadMessage = null;

				if (sizeof($this->readMessages) > 0)
				{
					$this->socket->onRead($this->server, array($this, __FUNCTION__));
				}
			}
		}

		return $this;
	}

	public function writeMessage(client\message $message)
	{
		$this->writeMessages->addMessage($message);

		if (sizeof($this->writeMessages) == 1)
		{
			$this->socket->onWrite($this->server, array($this, 'writeSocket'));
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
			if ($this->currentWriteMessage->writeSocket($this->socket) === false)
			{
				$this->socket->onWrite($this->server, array($this, __FUNCTION__));
			}
			else
			{
				$this->currentWriteMessage = null;

				if (sizeof($this->writeMessages) > 0)
				{
					$this->socket->onWrite($this->server, array($this, __FUNCTION__));
				}
			}
		}

		return $this;
	}

	public function closeSocket()
	{
		$this->socket->close();

		return $this;
	}

	public function onTimeout(server\socket\timer $timer, callable $handler)
	{
		$this->socket->onTimeout($this->server, $timer, $handler);

		return $this;
	}
}
