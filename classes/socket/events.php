<?php

namespace server\socket;

class events
{
	protected $bind = null;
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

	public function bind($object)
	{
		$this->bind = $object;

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
			$onRead = $this->onRead;

			$this->onRead = null;

			call_user_func_array($onRead, array($this->bind ?: $socket));
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
			$onWrite = $this->onWrite;

			$this->onWrite = null;

			call_user_func_array($onWrite, array($this->bind ?: $socket));
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
				call_user_func_array($this->onTimeout[1], array($this->bind ?: $socket));
			}
		}

		return $remaining;
	}
}
