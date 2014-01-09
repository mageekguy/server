<?php

namespace server;

class socket
{
	protected $socketManager = null;
	protected $events = null;
	protected $bind = null;
	protected $buffer = '';

	private $resource = null;

	public function __construct($resource, socket\manager\definition $manager = null)
	{
		$this->setSocketManager($manager);

		if ($this->socketManager->isSocket($resource) === false)
		{
			throw $this->getException('Resource is invalid');
		}

		$this->resource = $resource;
	}

	public function __toString()
	{
		try
		{
			return (string) $this->getPeer();
		}
		catch (\exception $exception)
		{
			return '';
		}
	}

	public function getBuffer()
	{
		return $this->buffer;
	}

	public function bufferize($data)
	{
		$this->buffer = $data;

		return $this;
	}

	public function setSocketManager(socket\manager\definition $manager = null)
	{
		$this->socketManager = $manager ?: new socket\manager();

		return $this;
	}

	public function getSocketManager()
	{
		return $this->socketManager;
	}

	public function onRead(socket\poller\definition $poller, callable $callable)
	{
		$this->setEvents($poller)->events->onRead($callable);

		return $this;
	}

	public function onWrite(socket\poller\definition $poller, callable $callable)
	{
		$this->setEvents($poller)->events->onWrite($callable);

		return $this;
	}

	public function onTimeout(socket\poller\definition $poller, socket\timer $timer, callable $callable)
	{
		$this->setEvents($poller)->events->onTimeout($timer, $callable);

		return $this;
	}

	public function getPeer()
	{
		try
		{
			return $this->socketManager->getSocketPeer($this->resource);
		}
		catch (\exception $exception)
		{
			throw $this->getExceptionFrom($exception);
		}
	}

	public function getName()
	{
		try
		{
			return $this->socketManager->getSocketName($this->resource);
		}
		catch (\exception $exception)
		{
			throw $this->getExceptionFrom($exception);
		}
	}

	public function read($length, $mode)
	{
		try
		{
			$data = $this->buffer . $this->socketManager->readSocket($this->resource, $length, $mode);

			$this->buffer = '';

			return $data;
		}
		catch (\exception $exception)
		{
			throw $this->getExceptionFrom($exception);
		}
	}

	public function write($data)
	{
		try
		{
			return $this->socketManager->writeSocket($this->resource, $data);
		}
		catch (\exception $exception)
		{
			throw $this->getExceptionFrom($exception);
		}
	}

	public function bind($mixed)
	{
		$this->bind = $mixed;

		return $this;
	}

	public function close()
	{
		try
		{
			$this->socketManager->closeSocket($this->resource);
		}
		catch (\exception $exception)
		{
			throw $this->getExceptionFrom($exception);
		}

		$this->events = null;

		return $this;
	}

	public function isClosed()
	{
		return ($this->socketManager->isSocket($this->resource) === false);
	}

	protected function setEvents(socket\poller\definition $poller)
	{
		if ($this->events === null || (isset($this->events->onRead) === false && isset($this->events->onWrite) === false))
		{
			$this->events = $poller->pollSocket($this->resource)->bind($this->bind ?: $this);
		}

		return $this;
	}

	protected function getException($message)
	{
		return new socket\exception($message);
	}

	protected function getExceptionFrom(\exception $exception)
	{
		return new socket\exception($exception->getMessage(), $exception->getCode());
	}
}
