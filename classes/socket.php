<?php

namespace server;

class socket
{
	protected $socketManager = null;
	protected $events = null;

	private $resource = null;

	public function __construct($resource, socket\manager\definition $manager = null)
	{
		$this->resource = $resource;

		$this->setSocketManager($manager);
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

	public function onRead(socket\select $select, callable $callable)
	{
		$this->setEvents($select)->events->onRead($callable);

		return $this;
	}

	public function onWrite(socket\select $select, callable $callable)
	{
		$this->setEvents($select)->events->onWrite($callable);

		return $this;
	}

	public function onTimeout(socket\select $select, socket\timer $timer, callable $callable)
	{
		$this->setEvents($select)->events->onTimeout($timer, $callable);

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

		return $this;
	}

	protected function setEvents(socket\select $select)
	{
		if ($this->events === null || (isset($this->events->onRead) === false && isset($this->events->onWrite) === false))
		{
			$this->events = $select->socket($this->resource);
		}

		return $this;
	}
}
