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

		foreach (new \arrayIterator($this->socketsResource) as $key => $socketResource)
		{
		}

		foreach (new \arrayIterator($this->socketsEvents) as $key => $socketEvents)
		{
			$socketResource = $this->socketsResource[$key];

			if (is_resource($socketResource) === false)
			{
				unset($this->socketsResource[$key]);
				unset($this->socketsEvents[$key]);
			}
			else
			{
				if (isset($socketEvents->onRead) === true)
				{
					$read[$key] = $socketResource;
				}

				if (isset($socketEvents->onWrite) === true)
				{
					$write[$key] = $socketResource;
				}
			}
		}

		if (sizeof($read) > 0 || sizeof($write) > 0 || sizeof($except) > 0)
		{
			$this->socketManager->select($read, $write, $except, $timeout);

			if ($read)
			{
				foreach ($read as $key => $socket)
				{
					unset($this->socketsEvents[$key]->triggerOnRead($socket)->onRead);
				}
			}

			if ($write)
			{
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
