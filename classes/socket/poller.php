<?php

namespace server\socket;

class poller implements poller\definition
{
	protected $socketManager = null;
	protected $socketsResource = array();
	protected $socketsEvents = array();
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

	public function setSocketManager(manager\definition $socketManager = null)
	{
		$this->socketManager = $socketManager ?: new manager();

		return $this;
	}

	public function pollSocket($socket)
	{
		$this->socketsResource[] = $socket;
		$this->socketsEvents[] = $socketEvents = $this->socketEventsFactory->build();

		return $socketEvents;
	}

	public function waitSockets($timeout = null)
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
			try
			{
				$this->socketManager->pollSockets($read, $write, $except, $timeout ?: $minSocketTimeout);
			}
			catch (\exception $exception)
			{
				throw $this->getExceptionFrom($exception);
			}

			if ($read)
			{
				foreach ($read as $key => $socket)
				{
					$this->socketsEvents[$key]->triggerOnRead($socket);

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
					$this->socketsEvents[$key]->triggerOnWrite($socket);

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

	protected function getException($message, $code = 0)
	{
		return new poller\exception($message, $code);
	}

	protected function getExceptionFrom(\exception $exception)
	{
		return new poller\exception($exception->getMessage(), $exception->getCode());
	}
}
