<?php

namespace server\tests\units\unix\user;

require __DIR__ . '/../../../runner.php';

use
	atoum,
	server\unix\user\home as testedClass
;

class home extends atoum
{
	public function test__construct()
	{
		$this
			->if(
				$this->function->getcwd = $currentHome = uniqid(),
				$home = new testedClass()
			)
			->then
				->castToString($home)->isEqualTo($currentHome)

			->if($home = new testedClass($path = uniqid()))
			->then
				->castToString($home)->isEqualTo($path)
		;
	}

	public function testSetPath()
	{
		$this
			->given($home = new testedClass(uniqid()))
			->then
				->object($home->setPath($path = uniqid()))->isIdenticalTo($home)
				->castToString($home)->isEqualTo($path)
		;
	}

	public function testGo()
	{
		$this
			->given($home = new testedClass($path = uniqid()))
			->then
				->if($this->function->chdir = false)
				->then
					->exception(function() use ($home) { $home->go(); })
						->isInstanceOf('server\unix\user\home\exception')
						->hasMessage('Unable to go to directory \'' . $home . '\'')
		;
	}
}
