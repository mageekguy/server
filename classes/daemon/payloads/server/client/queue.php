<?php

namespace server\daemon\payloads\server\client;

class queue implements \countable
{
	protected $messages = array();
	protected $priorities = array();
	protected $size = 0;

	public function count()
	{
		return $this->size;
	}

	public function addMessage(message $message, $priority = 0)
	{
		if ($priority > 0 && isset($this->messages[$priority]) === false)
		{
			$this->priorities[] = $priority;

			sort($this->priorities, SORT_NUMERIC);
		}

		$this->messages[$priority][] = $message;

		$this->size++;

		return $this;
	}

	public function shiftMessage()
	{
		$message = null;

		if ($this->size > 0)
		{
			$priority = reset($this->priorities);

			if ($priority === false)
			{
				$priority = 0;
			}

			$message = array_shift($this->messages[$priority]);

			if (sizeof($this->messages[$priority]) <= 0)
			{
				unset($this->messages[$priority]);

				if ($priority > 0)
				{
					array_shift($this->priorities);
				}
			}

			$this->size--;
		}

		return $message;
	}
}
