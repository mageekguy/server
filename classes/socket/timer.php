<?php

namespace server\socket;

class timer
{
	protected $duration = 0;
	protected $start = null;

	public function __construct($duration)
	{
		$this->duration = (int) $duration;
	}

	public function start()
	{
		$this->start = time();

		return $this;
	}

	public function getDuration()
	{
		return $this->duration;
	}

	public function getStart()
	{
		return $this->start;
	}

	public function getRemaining()
	{
		$remaining = null;

		if ($this->start !== null)
		{
			$remaining = ($this->duration - (time() - $this->start));

			if ($remaining <= 0)
			{
				$remaining = 0;
			}
		}

		return $remaining;
	}
}
