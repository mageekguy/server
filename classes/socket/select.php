<?php

namespace server\socket;

class select
{
	protected $socketsResource = array();
	protected $socketsEvents = array();
	protected $socketManager = null;
	protected $socketEventsFactory = null;

	public function __construct()
	{
		$this
			->setSocketManager()
			->setSocketEventsFactory()
		;
	}

	public function getSocketEventsFactory()
	{
		return $this->socketEventsFactory;
	}

	public function setSocketEventsFactory(events\factory $socketEventsFactory = null)
	{
		$this->socketEventsFactory = $socketEventsFactory ?: new events\factory();

		return $this;
	}

	public function getSocketManager()
	{
		return $this->socketManager;
	}

	public function setSocketManager(manager $socketManager = null)
	{
		$this->socketManager = $socketManager ?: new manager();

		return $this;
	}

	public function getSockets()
	{
		return $this->socketsResource;
	}

	public function socket($socket)
	{
		$this->socketsResource[] = $socket;
		$this->socketsEvents[] = $socketEvents = $this->socketEventsFactory->build();

		return $socketEvents;
	}

	public function wait($timeout)
	{
		$read = $write = $except = array();

		foreach ($this->socketsEvents as $key => $socketEvents)
		{
			if (isset($socketEvents->onRead) === true)
			{
				$read[$key] = $this->socketsResource[$key];
			}

			if (isset($socketEvents->onWrite) === true)
			{
				$write[$key] = $this->socketsResource[$key];
			}
		}

		if (sizeof($read) > 0 || sizeof($write) > 0 || sizeof($except) > 0)
		{
			if ($this->socketManager->select($read, $write, $except, $timeout) > 0)
			{
				foreach ($read as $key => $socket)
				{
					unset($this->socketsEvents[$key]->triggerOnRead($socket)->onRead);
				}

				$write = array_filter($write, function($socket) { return (is_resource($socket) === true); });

				foreach ($write as $key => $socket)
				{
					unset($this->socketsEvents[$key]->triggerOnWrite($socket)->onWrite);
				}
			}
		}

		return $this;
	}
}
