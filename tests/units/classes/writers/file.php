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

			->when(function() { $file = new testedClass(uniqid()); $file->log(uniqid()); })
			->then
				->function('fclose')->wasCalledWithArguments($resource)->once()
		;
	}

	public function test__toString()
	{
		$this
			->if($file = new testedClass($path = uniqid()))
			->then
				->castToString($file)->isEqualTo($path)
		;
	}

	public function testOpenFile()
	{
		$this
			->given($file = new testedClass($path = uniqid()))

			->if($this->function->fopen = false)
			->then
				->exception(function() use ($file, & $log) { $file->openFile(); })
					->isInstanceOf('server\writers\file\exception')
					->hasMessage('Unable to open \'' . $path . '\'')
				->function('fopen')->wasCalledWithArguments($path, 'a')->once()

			->if($this->function->fopen = $resource = uniqid())
			->then
				->object($file->openFile())->isIdenticalTo($file)
				->function('fopen')->wasCalledWithArguments($path, 'a')->twice()
		;
	}

	public function testWriteInFile()
	{
		$this
			->if(
				$file = new testedClass($path = uniqid()),
				$this->function->fopen = false,
				$this->function->fwrite = function($data) { return strlen($data); }
			)
			->then
				->exception(function() use ($file, & $data) { $file->writeInFile($data = uniqid()); })
					->isInstanceOf('server\writers\file\exception')
					->hasMessage('Unable to open \'' . $path . '\'')
			->if(
				$this->function->fopen = $resource = uniqid()
			)
			->then
				->object($file->writeInFile($data = uniqid()))->isIdenticalTo($file)
				->function('fopen')->wasCalledWithArguments($path, 'a')->twice()
				->function('fwrite')->wasCalledWithArguments($resource, $data)->once()
				->object($file->writeInFile($anotherData = uniqid()))->isIdenticalTo($file)
				->function('fopen')->wasCalledWithArguments($path, 'a')->twice()
				->function('fwrite')->wasCalledWithArguments($resource, $anotherData)->once()
			->if(
				$this->function->fwrite[3] = strlen(substr($longLog = uniqid(), 0, 3)),
				$this->function->fwrite[4] = strlen(substr($longLog, 3))
			)
			->then
				->object($file->writeInFile($longLog))->isIdenticalTo($file)
				->function('fopen')->wasCalledWithArguments($path, 'a')->twice()
				->function('fwrite')
					->wasCalledWithArguments($resource, $longLog)->once()
					->wasCalledWithArguments($resource, substr($longLog, 3))->once()
			->if(
				$this->function->fwrite[5] = false
			)
			->then
				->exception(function() use ($file, & $data) { $file->writeInFile($data = uniqid()); })
					->isInstanceOf('server\writers\file\exception')
					->hasMessage('Unable to write \'' . $data . '\' in \'' . $path . '\'')
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
					->hasMessage('Unable to open \'' . $path . '\'')
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
					->hasMessage('Unable to write \'' . $log . '\' in \'' . $path . '\'')
		;
	}

	public function testCloseFile()
	{
		$this
			->given($file = new testedClass($path = uniqid()))
			->then
				->object($file->closeFile())->isIdenticalTo($file)

			->if(
				$this->function->fopen = $resource = uniqid(),
				$this->function->fclose = false,
				$file->openFile()
			)
			->then
				->exception(function() use ($file, & $log) { $file->closeFile(); })
					->isInstanceOf('server\writers\file\exception')
					->hasMessage('Unable to close \'' . $path . '\'')
				->function('fclose')->wasCalledWithArguments($resource)->once()

			->if(
				$this->function->fclose = true,
				$file->openFile()
			)
			->then
				->object($file->closeFile())->isIdenticalTo($file)
				->function('fclose')->wasCalledWithArguments($resource)->twice()
		;
	}
}
