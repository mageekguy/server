<?php

namespace server\daemon\payloads\server\client;

class queue implements \countable
{
	protected $messages = array();

	public function count()
	{
		return sizeof($this->messages);
	}

	public function addMessage(message $message)
	{
		$this->messages[] = $message;

		return $this;
	}

	public function shiftMessage()
	{
		$message = null;

		if (sizeof($this) > 0)
		{
			$message = array_shift($this->messages);
		}

		return $message ?: null;
	}
}
