<?php

namespace server\tests\units\readers;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\readers\file as testedClass
;

class file extends atoum
{
	public function beforeTestMethod($method)
	{
		$this->function->fclose->doesNothing();
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
				$this->function->fread = function() { return uniqid(); }
			)

			->when(function() { $file = new testedClass(uniqid()); })
			->then
				->function('fclose')->wasCalledWithArguments($resource)->never()

			->when(function() { $file = new testedClass(uniqid()); $file->readFromFile(rand(1, PHP_INT_MAX)); })
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
					->isInstanceOf('server\readers\file\exception')
					->hasMessage('Unable to open \'' . $path . '\'')

			->if($this->function->fopen = $resource = uniqid())
			->then
				->object($file->openFile())->isIdenticalTo($file)
				->function('fopen')->wasCalledWithArguments($path, 'r')->twice()
		;
	}

	public function testReadFromFile()
	{
		$this
			->if(
				$file = new testedClass($path = uniqid()),
				$this->function->fopen = false,
				$this->function->fread = function() { return uniqid(); }
			)
			->then
				->exception(function() use ($file, & $data) { $file->readFromFile($data = uniqid()); })
					->isInstanceOf('server\readers\file\exception')
					->hasMessage('Unable to open \'' . $path . '\'')

			->if(
				$this->function->fopen = $resource = uniqid()
			)
			->then
				->string($file->readFromFile($length = rand(1, PHP_INT_MAX)))->isNotEmpty()
				->function('fopen')->wasCalledWithArguments($path, 'r')->twice()
				->function('fread')->wasCalledWithArguments($resource, $length)->once()
				->string($file->readFromFile($anotherLength = rand(1, PHP_INT_MAX)))->isNotEmpty()
				->function('fopen')->wasCalledWithArguments($path, 'r')->twice()
				->function('fread')->wasCalledWithArguments($resource, $anotherLength)->once()

			->if(
				$this->function->fread = false
			)
			->then
				->exception(function() use ($file, & $length) { $file->readFromFile($length = rand(1, PHP_INT_MAX)); })
					->isInstanceOf('server\readers\file\exception')
					->hasMessage('Unable to read ' . $length . ' bytes from \'' . $path . '\'')
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
					->isInstanceOf('server\readers\file\exception')
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
