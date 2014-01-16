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
		$socketKey = array_search($socket, $this->socketsResource, true);

		if ($socketKey !== false)
		{
			$socketEvents = $this->socketsEvents[$socketKey];
		}
		else
		{
			$this->socketsResource[] = $socket;
			$this->socketsEvents[] = $socketEvents = $this->socketEventsFactory->build();
		}

		return $socketEvents;
	}

	public function waitSockets($timeout = null)
	{
		$read = $write = $except = array();

		$minSocketTimeout = $this->filterTimeoutedSockets();

		foreach (new \arrayIterator($this->socketsEvents) as $key => $socketEvents)
		{
			$socketResource = $this->socketsResource[$key];

			if ($this->forgetSocket($key) === false)
			{
				if (isset($socketEvents->onReadNotBlock) === true)
				{
					$read[$key] = $socketResource;
				}

				if (isset($socketEvents->onWriteNotBlock) === true)
				{
					$write[$key] = $socketResource;
				}
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
					$events = $this->socketsEvents[$key];

					$this->forgetSocket($key);

					$events->triggerOnReadNotBlock($socket);
				}
			}

			if ($write)
			{
				$write = array_filter($write, function($socket) { return ($this->socketManager->isSocket($socket) === true); });

				foreach ($write as $key => $socket)
				{
					$events = $this->socketsEvents[$key];

					$this->forgetSocket($key);

					$events->triggerOnWriteNotBlock($socket);
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
			if ($this->forgetSocket($key) === false)
			{
				$timeout = $socketEvents->triggerOnTimeout($this->socketsResource[$key]);

				if ($timeout !== null)
				{
					if ($timeout <= 0)
					{
						$this->forgetSocket($key, true);
					}
					else
					{
						$minSocketTimeout = min($minSocketTimeout ?: $timeout, $timeout);
					}
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

	private function forgetSocket($key, $force = false)
	{
		if ($force === true || $this->socketManager->isSocket($this->socketsResource[$key]) === false || (isset($this->socketsEvents[$key]->onTimeout) === false && isset($this->socketsEvents[$key]->onWriteNotBlock) === false && isset($this->socketsEvents[$key]->onReadNotBlock) === false))
		{
			unset($this->socketsResource[$key]);
			unset($this->socketsEvents[$key]);
		}

		return (isset($this->socketsResource[$key]) === false);
	}
}
