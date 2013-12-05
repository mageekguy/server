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

	public function wait($timeout = null)
	{
		$read = $write = $except = array();

		$minSocketTimeout = $this->filterTimeoutedSockets();

		foreach (new \arrayIterator($this->socketsEvents) as $key => $socketEvents)
		{
			$socketResource = $this->socketsResource[$key];

			if (isset($socketEvents->onRead) === true)
			{
				$read[$key] = $socketResource;
			}

			if (isset($socketEvents->onWrite) === true)
			{
				$write[$key] = $socketResource;
			}
		}

		if (sizeof($read) > 0 || sizeof($write) > 0 || sizeof($except) > 0)
		{
			$this->socketManager->select($read, $write, $except, $timeout ?: $minSocketTimeout);

			if ($read)
			{
				foreach ($read as $key => $socket)
				{
					unset($this->socketsEvents[$key]->triggerOnRead($socket)->onRead);

					if (isset($this->socketsEvents[$key]->onWrite) === false)
					{
						unset($this->socketsEvents[$key]);
						unset($this->socketsResource[$key]);
					}
				}
			}

			if ($write)
			{
				$write = array_filter($write, function($socket) { return (is_resource($socket) === true); });

				foreach ($write as $key => $socket)
				{
					unset($this->socketsEvents[$key]->triggerOnWrite($socket)->onWrite);

					if (isset($this->socketsEvents[$key]->onRead) === false)
					{
						unset($this->socketsEvents[$key]);
						unset($this->socketsResource[$key]);
					}
				}
			}

			$this->filterTimeoutedSockets();
		}

		return $this;
	}

	protected function filterTimeoutedSockets()
	{
		$minSocketTimeout = null;

		foreach (new \arrayIterator($this->socketsEvents) as $key => $socketEvents)
		{
			$socketResource = $this->socketsResource[$key];

			if (is_resource($socketResource) === false)
			{
				unset($this->socketsResource[$key]);
				unset($this->socketsEvents[$key]);
			}
			else if (isset($socketEvents->onTimeout) === true)
			{
				$timeout = $socketEvents->triggerOnTimeout($socketResource);

				switch (true)
				{
					case $timeout <= 0:
						unset($this->socketsResource[$key]);
						unset($this->socketsEvents[$key]);
						break;

					case $minSocketTimeout === null:
						$minSocketTimeout = $timeout;
						break;

					default:
						$minSocketTimeout = min($minSocketTimeout, $timeout);
				}
			}
		}

		return $minSocketTimeout;
	}
}
