<?php

namespace server\tests\units\writers;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\writers\file as testedClass
;

class file extends atoum
{
	public function beforeTestMethod($method)
	{
		$this->function->fclose->doesNothing();
	}

	public function testClass()
	{
		$this->testedClass->implements('server\logger\writer');
	}

	public function test__construct()
	{
		$this
			->if($file = new testedClass($path = uniqid()))
			->then
				->string($file->getPath())->isEqualTo($path)
		;
	}

	public function test__destruct()
	{
		$this
			->if(
				$this->function->fopen = $resource = uniqid(),
				$this->function->fwrite = function($data) { return strlen($data); }
			)
			->when(function() { $file = new testedClass(uniqid()); })
			->then
				->function('fclose')->wasCalledWithArguments($resource)->never()
			->if(
				$file = new testedClass($path = uniqid()),
				$file->log(uniqid())
			)
			->when(function() { $file = new testedClass(uniqid()); $file->log(uniqid()); })
			->then
				->function('fclose')->wasCalledWithArguments($resource)->once()
		;
	}

	public function testLog()
	{
		$this
			->if(
				$file = new testedClass($path = uniqid()),
				$this->function->fopen = false,
				$this->function->fwrite = function($data) { return strlen($data); }
			)
			->then
				->exception(function() use ($file, & $log) { $file->log($log = uniqid()); })
					->isInstanceOf('server\writers\file\exception')
					->hasMessage('Unable to write log \'' . $log . '\' in \'' . $path . '\'')
			->if(
				$this->function->fopen = $resource = uniqid()
			)
			->then
				->object($file->log($log = uniqid()))->isIdenticalTo($file)
				->function('fopen')->wasCalledWithArguments($path, 'a')->twice()
				->function('fwrite')->wasCalledWithArguments($resource, $log)->once()
				->object($file->log($anotherLog = uniqid()))->isIdenticalTo($file)
				->function('fopen')->wasCalledWithArguments($path, 'a')->twice()
				->function('fwrite')->wasCalledWithArguments($resource, $anotherLog)->once()
			->if(
				$this->function->fwrite[3] = strlen(substr($longLog = uniqid(), 0, 3)),
				$this->function->fwrite[4] = strlen(substr($longLog, 3))
			)
			->then
				->object($file->log($longLog))->isIdenticalTo($file)
				->function('fopen')->wasCalledWithArguments($path, 'a')->twice()
				->function('fwrite')
					->wasCalledWithArguments($resource, $longLog)->once()
					->wasCalledWithArguments($resource, substr($longLog, 3))->once()
			->if(
				$this->function->fwrite[5] = false
			)
			->then
				->exception(function() use ($file, & $log) { $file->log($log = uniqid()); })
					->isInstanceOf('server\writers\file\exception')
					->hasMessage('Unable to write log \'' . $log . '\' in \'' . $path . '\'')
		;
	}
}
