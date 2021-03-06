<?php

namespace server;

class logger
{
	protected $writers = array();
	protected $decorators = array();

	public function getWriters()
	{
		return $this->writers;
	}

	public function addWriter(logger\writer $writer)
	{
		$this->writers[] = $writer;

		return $this;
	}

	public function getDecorators()
	{
		return $this->decorators;
	}

	public function addDecorator(logger\decorator $decorator)
	{
		$this->decorators[] = $decorator;

		return $this;
	}

	public function log($message)
	{
		foreach ($this->decorators as $decorator)
		{
			$decorator->prepareToDecorateLog();
		}

		$lines = array();

		foreach (preg_split("/\r?\n/", $message) as $line)
		{
			if ($line != '')
			{
				foreach ($this->decorators as $decorator)
				{
					$line = $decorator->decorateLog($line);
				}

				if ($line != '')
				{
					$lines[] = $line;
				}
			}
		}

		$message = join($lines, '');

		foreach ($this->writers as $writer)
		{
			$writer->log($message);
		}

		return $this;
	}
}
