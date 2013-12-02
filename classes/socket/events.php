<?php

namespace server\socket;

class events
{
	protected $onRead = null;
	protected $onWrite = null;

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
}
