<?php

namespace server\tests\units\daemon\payloads\server\client;

require __DIR__ . '/../../../../../runner.php';

use
	atoum,
	server\daemon\payloads\server\client\message as testedClass
;

class message extends atoum
{
	public function beforeTestMethod($method)
	{
		$test = $this;

		$this->then = function() use ($test) { $test->getTestAdapterStorage()->resetCalls(); return $test; };

		$this->mockGenerator->shuntParentClassCalls();
	}

	public function test__construct()
	{
		$this
			->if($message = new testedClass())
			->then
				->castToString($message)->isEmpty()

			->if($message = new testedClass($data = uniqid()))
			->then
				->castToString($message)->isEqualTo($data)
		;
	}

	public function test__invoke()
	{
		$this
			->if($message = new testedClass())
			->then
				->object($message(''))->isIdenticalTo($message)
				->castToString($message)->isEmpty()
				->object($message($data = uniqid()))->isIdenticalTo($message)
				->castToString($message)->isEqualTo($data)
		;
	}

	public function testOnRead()
	{
		$this
			->given($message = new testedClass())
			->then
				->object($message->onRead(function() {}))->isIdenticalTo($message)
		;
	}

	public function testReadSocket()
	{
		$this
			->given(
				$message = new testedClass(),
				$socket = new \mock\server\socket(uniqid())
			)

			->if($this->calling($socket)->read = $data1 = uniqid())
			->then
				->boolean($message->readSocket($socket))->isFalse()
				->mock($socket)->call('read')->withArguments(2048, PHP_NORMAL_READ)->once()
				->castToString($message)->isEmpty()
				->integer($message->getBytesRead())->isEqualTo(strlen($data1))

			->if($this->calling($socket)->read = $data2 = (uniqid() . "\r\n"))
			->then
				->boolean($message->readSocket($socket))->isTrue()
				->mock($socket)->call('read')->withArguments(2048, PHP_NORMAL_READ)->once()
				->castToString($message)->isEqualTo($data1 . $data2)
				->integer($message->getBytesRead())->isEqualTo(strlen($data1 . $data2))

			->if($this->calling($socket)->read = $data3 = uniqid())
			->then
				->boolean($message->readSocket($socket))->isFalse()
				->mock($socket)->call('read')->withArguments(2048, PHP_NORMAL_READ)->once()
				->castToString($message)->isEmpty()
				->integer($message->getBytesRead())->isEqualTo(strlen($data3))

			->given(
				$message = new testedClass(),
				$message->onRead(function() use (& $messageRead) { $messageRead = true; }),
				$socket = new \mock\server\socket(uniqid()),
				$this->calling($socket)->read = uniqid() . "\r\n"
			)
			->then
				->boolean($message->readSocket($socket))->isTrue()
				->boolean($messageRead)->isTrue()
		;
	}

	public function testOnWrite()
	{
		$this
			->given($message = new testedClass())
			->then
				->object($message->onWrite(function() {}))->isIdenticalTo($message)
		;
	}

	public function testWriteSocket()
	{
		$this
			->given(
				$message = new testedClass(),
				$socket = new \mock\server\socket(uniqid())
			)
			->then
				->boolean($message->writeSocket($socket))->isTrue()
				->mock($socket)->call('write')->never()
				->integer($message->getBytesWritten())->isZero()

			->if($message = new testedClass(''))
			->then
				->boolean($message->writeSocket($socket))->isTrue()
				->mock($socket)->call('write')->never()
				->integer($message->getBytesWritten())->isZero()

			->if(
				$message = new testedClass($data = 'ABCDEFGH'),
				$this->calling($socket)->write = 1
			)
			->then
				->boolean($message->writeSocket($socket))->isFalse()
				->mock($socket)->call('write')->withArguments($data)->once()
				->integer($message->getBytesWritten())->isEqualTo(1)
				->boolean($message->writeSocket($socket))->isFalse()
				->mock($socket)->call('write')->withArguments('BCDEFGH')->once()
				->integer($message->getBytesWritten())->isEqualTo(2)

			->if(
				$this->calling($socket)->write = 5
			)
			->then
				->boolean($message->writeSocket($socket))->isFalse()
				->mock($socket)->call('write')->withArguments('CDEFGH')->once()
				->integer($message->getBytesWritten())->isEqualTo(7)

			->if(
				$this->calling($socket)->write = 1
			)
			->then
				->boolean($message->writeSocket($socket))->isTrue()
				->mock($socket)->call('write')->withArguments('H')->once()
				->integer($message->getBytesWritten())->isEqualTo(8)
				->boolean($message->writeSocket($socket))->isFalse()
				->mock($socket)->call('write')->withArguments($data)->once()
				->mock($socket)->call('write')->withArguments('ABCDEFGH')->once()
				->integer($message->getBytesWritten())->isEqualTo(1)

			->if(
				$message = new testedClass($data = 'ABCDEFGH'),
				$message->onWrite(function() use (& $messageWrited) { $messageWrited = true; }),
				$this->calling($socket)->write = function($data) { return strlen($data); }
			)
			->then
				->boolean($message->writeSocket($socket))->isTrue()
				->boolean($messageWrited)->isTrue()
		;
	}
}
