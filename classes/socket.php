<?php

namespace server;

class socket
{
	protected $socketManager = null;
	protected $events = null;
	protected $peer = null;

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
		return $this->socketManager->getSocketPeer($this->resource);
	}

	public function read($length, $mode)
	{
		return $this->socketManager->readSocket($this->resource, $length, $mode);
	}

	public function write($data)
	{
		return $this->socketManager->writeSocket($this->resource, $data);
	}

	public function close()
	{
		$this->socketManager->closeSocket($this->resource);

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
			$this->events = $poller->pollSocket($this->resource);
		}

		return $this;
	}

	protected function getException($message)
	{
		return new socket\exception($message);
	}
}
