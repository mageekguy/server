<?php

namespace server\socket;

class events
{
	protected $onRead = null;
	protected $onWrite = null;
	protected $onTimeout = null;

	public function __isset($event)
	{
		return (isset($this->{$event}) === true && $this->{$event} !== null);
	}

	public function __unset($event)
	{
		if (isset($this->{$event}) === true)
		{
			$this->{$event} = null;
		}

		return $this;
	}

	public function onRead(callable $callable)
	{
		$this->onRead = $callable;

		return $this;
	}

	public function triggerOnRead($socket)
	{
		if ($this->onRead !== null)
		{
			call_user_func_array($this->onRead, array($socket));
		}

		return $this;
	}

	public function onWrite(callable $callable)
	{
		$this->onWrite = $callable;

		return $this;
	}

	public function triggerOnWrite($socket)
	{
		if ($this->onWrite !== null)
		{
			call_user_func_array($this->onWrite, array($socket));
		}

		return $this;
	}

	public function onTimeout(timer $timer, callable $callable)
	{
		$this->onTimeout = array($timer->start(), $callable);

		return $this;
	}

	public function triggerOnTimeout($socket)
	{
		$remaining = null;

		if ($this->onTimeout !== null)
		{
			$remaining =  $this->onTimeout[0]->getRemaining();

			if ($remaining <= 0)
			{
				call_user_func_array($this->onTimeout[1], array($socket));
			}
		}

		return $remaining;
	}

	public function restartTimer()
	{
		if ($this->onTimeout !== null)
		{
			$this->onTimeout[0]->start();
		}

		return $this;
	}
}
