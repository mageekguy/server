<?php

namespace server\daemon\payloads\server\client;

use
	server
;

class message
{
	private $serializer = null;
	private $data = '';
	private $onRead = null;
	private $onWrite = null;
	private $onError = null;

	public function __construct($data = null)
	{
		$this->setSerializer();

		if ($data !== null)
		{
			$this($data);
		}
	}

	public function __toString()
	{
		return $this->serializer->serializeMessage();
	}

	public function __set($property, $value)
	{
		$this->serializer->{$property} = $value;

		return $this;
	}

	public function __get($property)
	{
		return $this->serializer->{$property};
	}

	public function __isset($property)
	{
		return isset($this->serializer->{$property});
	}

	public function __invoke($data)
	{
		if ($this->serializer->unserializeMessage($data) === 0)
		{
			throw new message\exception('Data \'' . $data . '\' are invalid');
		}

		return $this;
	}

	public function setSerializer(message\serializer $serializer = null)
	{
		$this->serializer = $serializer ?: new message\serializers\eol();

		return $this;
	}

	public function getSerializer()
	{
		return $this->serializer;
	}

	public function onRead(callable $handler)
	{
		$this->onRead = $handler;

		return $this;
	}

	public function readData(server\socket $socket)
	{
		try
		{
			$messageRead = false;

			$messageLength = $this->serializer->unserializeMessage($socket->getData());

			if ($messageLength > 0)
			{
				$messageRead = true;

				$socket->truncateData($messageLength);

				if ($this->onRead !== null)
				{
					call_user_func_array($this->onRead, array($this));
				}
			}

			return $messageRead;
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

	public function writeData(server\socket $socket)
	{
		if ($this->data === '')
		{
			$this->data = $this->serializer->serializeMessage();
		}

		if ($this->data !== '')
		{
			$bytesWritten = $socket->write($this->data);

			if ($bytesWritten > 0)
			{
				$this->data = (string) substr($this->data, $bytesWritten);
			}
		}

		if ($this->data !== '')
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
}
