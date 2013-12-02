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
			$message = $decorator->decorateLog($message);
		}

		foreach ($this->writers as $writer)
		{
			$writer->log($message);
		}

		return $this;
	}
}
