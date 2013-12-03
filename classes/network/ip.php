<?php

namespace server\network;

class ip
{
	protected $value = 0;

	public function __construct($value)
	{
		$longValue = $value;

		if (is_numeric($longValue) === false)
		{
			$longValue = ip2long($value);
		}

		switch (true)
		{
			case $longValue === false:
			case $longValue < 0.0 || $longValue > 4294967295.0:
				throw new ip\exception('\'' . $value . '\' is not a valid IP address');

			default:
				$this->value = $longValue;
		}
	}

	public function __toString()
	{
		return long2ip($this->value);
	}
}
