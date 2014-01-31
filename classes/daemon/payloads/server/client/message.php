<?php

namespace server\daemon\payloads\server\client;

use
	server
;

class message
{
	private $serializer = null;

	private $buffer = '';

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
		if ($this->serializer->unserializeMessage($data) === false)
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

	public function readSocket(server\socket $socket)
	{
		try
		{
			$unserializeOk = ($this->serializer->unserializeMessage($socket->getData()) === true);

			if ($unserializeOk === true)
			{
				$socket->truncateData(strlen((string) $this));

				if ($this->onRead !== null)
				{
					call_user_func_array($this->onRead, array($this));
				}
			}

			return $unserializeOk;
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
		if ($this->buffer === '')
		{
			$this->buffer = (string) $this;
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
}
