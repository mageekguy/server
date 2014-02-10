<?php

namespace server;

class socket
{
	protected $socketManager = null;
	protected $events = null;
	protected $bind = null;
	protected $data = '';

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

	public function setSocketManager(socket\manager\definition $manager = null)
	{
		$this->socketManager = $manager ?: new socket\manager();

		return $this;
	}

	public function getSocketManager()
	{
		return $this->socketManager;
	}

	public function onReadNotBlock(socket\poller\definition $poller, callable $callable)
	{
		$this->pollWith($poller)->onReadNotBlock($callable);

		return $this;
	}

	public function onWriteNotBlock(socket\poller\definition $poller, callable $callable)
	{
		$this->pollWith($poller)->onWriteNotBlock($callable);

		return $this;
	}

	public function onTimeout(socket\poller\definition $poller, socket\timer $timer, callable $callable)
	{
		$this->pollWith($poller)->onTimeout($timer, $callable);

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

	public function connectTo(network\ip $ip, network\port $port)
	{
		try
		{
			$this->socketManager->connectSocketTo($this->resource, $ip, $port);

			return $this;
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
			$this->data .= ($data = $this->socketManager->readSocket($this->resource, $length, $mode));

			return $data;
		}
		catch (\exception $exception)
		{
			throw $this->getExceptionFrom($exception);
		}
	}

	public function getData()
	{
		return $this->data;
	}

	public function peekData($regex)
	{
		if (preg_match($regex, $this->getData(), $data) === 0)
		{
			$data = null;
		}
		else
		{
			$this->truncateData(strlen($data[0]));
		}

		return $data;
	}

	public function truncateData($bytes)
	{
		$this->data = (string) substr($this->data, $bytes + 1);

		return $this;
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

	protected function pollWith(socket\poller\definition $poller)
	{
		return $poller->pollSocket($this->resource)->bind($this->bind ?: $this);
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
