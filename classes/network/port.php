<?php

namespace server\network;

class port
{
	const min = 1;
	const max = 65536;

	protected $value = 0;

	public function __construct($value)
	{
		$intValue = (int) $value;

		switch (true)
		{
			case $intValue < static::min:
			case $intValue > static::max:
				throw new port\exception('\'' . $value . '\' is not a valid port');

			default:
				$this->value = $value;
		}
	}

	public function __toString()
	{
		return (string) $this->value;
	}

	public function check()
	{
	}
}
