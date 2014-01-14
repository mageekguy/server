<?php

namespace server\socket;

class events
{
	protected $bind = null;
	protected $onReadNotBlock = null;
	protected $onWriteNotBlock = null;
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

	public function onReadNotBlock(callable $callable)
	{
		$this->onReadNotBlock = $callable;

		return $this;
	}

	public function triggerOnReadNotBlock($socket)
	{
		if ($this->onReadNotBlock !== null)
		{
			$onReadNotBlock = $this->onReadNotBlock;

			$this->onReadNotBlock = null;

			call_user_func_array($onReadNotBlock, array($this->bind ?: $socket));
		}

		return $this;
	}

	public function onWriteNotBlock(callable $callable)
	{
		$this->onWriteNotBlock = $callable;

		return $this;
	}

	public function triggerOnWriteNotBlock($socket)
	{
		if ($this->onWriteNotBlock !== null)
		{
			$onWriteNotBlock = $this->onWriteNotBlock;

			$this->onWriteNotBlock = null;

			call_user_func_array($onWriteNotBlock, array($this->bind ?: $socket));
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
