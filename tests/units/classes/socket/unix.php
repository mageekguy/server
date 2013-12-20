<?php

namespace server\tests\units\socket;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\socket\unix as testedClass
;

class unix extends atoum
{
	public function testClass()
	{
		$this->testedClass->extends('server\socket');
	}

	public function test__toString()
	{
		$this
			->given(
				$socket = $this->getSocketInstance($resource = uniqid()),
				$socket->setSocketManager($socketManager = new \mock\server\socket\manager())
			)

			->if($this->calling($socketManager)->getSocketName = $name = uniqid())
			->then
				->castToString($socket)->isEqualTo($name)

			->if($this->calling($socketManager)->getSocketName->throw = $exception = new \exception())
			->then
				->castToString($socket)->isEmpty()
		;
	}

	protected function getSocketInstance($resource = null)
	{
		$socketManager = new \mock\server\socket\manager();
		$this->calling($socketManager)->isSocket = true;

		return new testedClass($resource ?: uniqid(), $socketManager);
	}
}
