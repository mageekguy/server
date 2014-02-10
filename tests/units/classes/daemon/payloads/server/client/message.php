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
			->if($this->newTestedInstance())
			->then
				->castToString($this->testedInstance)->isEmpty()
				->object($this->testedInstance->getSerializer())->isEqualTo(new testedClass\serializers\eol())

			->if($this->newTestedInstance($data = uniqid() . "\r\n"))
			->then
				->castToString($this->testedInstance)->isEqualTo($data)
		;
	}

	public function test__invoke()
	{
		$this
			->if($message = $this->newTestedInstance())
			->then
				->object($message($data = '' . "\r\n"))->isIdenticalTo($message)
				->string($message->data)->isEqualTo($data)
				->object($message($data = uniqid() . "\r\n"))->isIdenticalTo($message)
				->string($message->data)->isEqualTo($data)
				->exception(function() use ($message, & $dataWithoutEol) { $message($dataWithoutEol = uniqid()); })
					->isInstanceOf('server\daemon\payloads\server\client\message\exception')
					->hasMessage('Data \'' . $dataWithoutEol . '\' are invalid')
		;
	}

	public function testSetSerializer()
	{
		$this
			->if($this->newTestedInstance())
			->then
				->object($this->testedInstance->setSerializer($serializer = new \mock\server\daemon\payloads\server\client\message\serializer()))->isTestedInstance
				->object($this->testedInstance->getSerializer())->isIdenticalTo($serializer)
				->object($this->testedInstance->setSerializer())->isTestedInstance
				->object($this->testedInstance->getSerializer())
					->isNotIdenticalTo($serializer)
					->isEqualTo(new testedClass\serializers\eol())
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
				$socket = $this->getMockedSocket()
			)

			->if($this->calling($socket)->getData = '')
			->then
				->boolean($message->readSocket($socket))->isFalse()
				->mock($socket)
					->call('getData')->once()
				->castToString($message)->isEmpty()

			->if($this->calling($socket)->getData = $data1 = uniqid() . "\r\n")
			->then
				->boolean($message->readSocket($socket))->isTrue()
				->mock($socket)
					->call('getData')->once()
					->before(
						$this->mock($socket)->call('truncateData')->withArguments(strlen($data1))->once()
					)
				->castToString($message)->isEqualTo($data1)

			->given(
				$message = new testedClass(),
				$message->onRead(function() use (& $messageRead) { $messageRead = true; }),
				$socket = $this->getMockedSocket(),
				$this->calling($socket)->getData = uniqid() . "\r\n"
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
				$socket = $this->getMockedSocket()
			)
			->then
				->boolean($message->writeSocket($socket))->isTrue()
				->mock($socket)->call('write')->never()

			->if(
				$message = new testedClass($data = '' . "\r\n"),
				$this->calling($socket)->write = strlen($data)
			)
			->then
				->boolean($message->writeSocket($socket))->isTrue()
				->mock($socket)->call('write')->withArguments($data)->once()

			->if(
				$message = new testedClass($data = 'ABCDEFGH' . "\r\n"),
				$this->calling($socket)->write = 1
			)
			->then
				->boolean($message->writeSocket($socket))->isFalse()
				->mock($socket)->call('write')->withArguments($data)->once()
				->boolean($message->writeSocket($socket))->isFalse()
				->mock($socket)->call('write')->withArguments('BCDEFGH' . "\r\n")->once()

			->if(
				$this->calling($socket)->write = 5
			)
			->then
				->boolean($message->writeSocket($socket))->isFalse()
				->mock($socket)->call('write')->withArguments('CDEFGH' . "\r\n")->once()

			->if(
				$this->calling($socket)->write = 3
			)
			->then
				->boolean($message->writeSocket($socket))->isTrue()
				->mock($socket)->call('write')->withArguments('H' . "\r\n")->once()
				->boolean($message->writeSocket($socket))->isFalse()
				->mock($socket)->call('write')->withArguments($data)->once()
				->mock($socket)->call('write')->withArguments('ABCDEFGH' . "\r\n")->once()

			->if(
				$message = new testedClass($data = 'ABCDEFGH' . "\r\n"),
				$message->onWrite(function() use (& $messageWrited) { $messageWrited = true; }),
				$this->calling($socket)->write = function($data) { return strlen($data); }
			)
			->then
				->boolean($message->writeSocket($socket))->isTrue()
				->boolean($messageWrited)->isTrue()
		;
	}

	protected function getMockedSocket()
	{
		$this->mockGenerator->orphanize('__construct');

		return new \mock\server\socket(uniqid());
	}
}
