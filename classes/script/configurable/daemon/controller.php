<?php

namespace server\script\configurable\daemon;

class controller implements \arrayAccess
{
	protected $signals = array();
	protected $daemonShouldRun = true;

	public function offsetSet($signal, $handler)
	{
		$this->signals[$signal] = $handler;

		return $this;
	}

	public function offsetGet($signal)
	{
	}

	public function offsetUnset($signal)
	{
		if (isset($this[$signal]) === true)
		{
			unset($this->signals[$signal]);
		}

		pcntl_signal($signal, SIG_DFL);

		return $this;
	}

	public function offsetExists($signal)
	{
		return (isset($this->signals[$signal]) === true);
	}

	public function start()
	{
		foreach ($this->signals as $signal => $handler)
		{
			if (pcntl_signal($signal, $handler) === false)
			{
				throw new controller\exception('Unable to set handler for signal \'' . $signal . '\'');
			}

			unset($this->signals[$signal]);
		}

		return $this;
	}

	public function stopDaemon()
	{
		$this->daemonShouldRun = false;

		return $this;
	}

	public function daemonShouldRun()
	{
		pcntl_signal_dispatch();

		return $this->daemonShouldRun;
	}
}
