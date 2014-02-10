<?php

namespace server\daemon\payloads\server\client\message\serializers;

use
	server\daemon\payloads\server\client\message,
	server\daemon\payloads\server\client\message\serializer
;

class eol implements serializer
{
	const eol = "\r\n";

	protected $data = '';

	public function __set($property, $value)
	{
		switch (strtolower($property))
		{
			case 'data':
				if ($this->unserializeMessage($value) === 0)
				{
					throw new serializer\exception('Unable to set data with \'' . $value . '\'');
				}
				break;

			default:
				throw new serializer\exception('Unable to set value of property \'' . $property . '\' because it does not exist');
		}
	}

	public function __get($property)
	{
		switch (strtolower($property))
		{
			case 'data':
				return $this->data;

			default:
				throw new serializer\exception('Unable to get value of property \'' . $property . '\' because it does not exist');
		}
	}

	public function serializeMessage()
	{
		return ($this->data === '' ? '' : rtrim($this->data) . "\r\n");
	}

	public function unserializeMessage($data)
	{
		$this->data = '';

		$eolFound = (preg_match('/^.*' . self::eol . '/', (string) $data, $matches) === 1);

		if ($eolFound === true)
		{
			$this->data = $matches[0];
		}

		return strlen($this->data);
	}
}
