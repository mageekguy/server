<?php

namespace server\tests\units\daemon\payloads\server\client\message\serializers;

require __DIR__ . '/../../../../../../../runner.php';

use
	atoum
;

class eol extends atoum
{
	public function test__set()
	{
		$this
			->given($this->newTestedInstance())
			->then

				->exception(function() use (& $data) { $this->testedInstance->data = $data = uniqid(); })
					->isInstanceOf('server\daemon\payloads\server\client\message\serializer\exception')
					->hasMessage('Unable to set data with \'' . $data . '\'')

				->exception(function() use (& $unknownProperty) { $this->testedInstance->{$unknownProperty = uniqid()} = uniqid(); })
					->isInstanceOf('server\daemon\payloads\server\client\message\serializer\exception')
					->hasMessage('Unable to set value of property \'' . $unknownProperty . '\' because it does not exist')

				->if($this->testedInstance->data = $data = uniqid() . "\r\n")
				->then
					->string($this->testedInstance->data)->isEqualTo($data)
		;
	}

	public function test__get()
	{
		$this
			->given($this->newTestedInstance())
			->then
				->string($this->testedInstance->data)->isEmpty()

				->exception(function() use (& $unknownProperty) { $this->testedInstance->{$unknownProperty = uniqid()}; })
					->isInstanceOf('server\daemon\payloads\server\client\message\serializer\exception')
					->hasMessage('Unable to get value of property \'' . $unknownProperty . '\' because it does not exist')
		;
	}

	public function testSerializeMessage()
	{
		$this
			->given($this->newTestedInstance())
			->then
				->string($this->testedInstance->serializeMessage())->isEmpty()

			->if($this->testedInstance->data = $data = uniqid() . "\r\n")
			->then
				->string($this->testedInstance->serializeMessage())->isEqualTo($data)
		;
	}

	public function testUnserializeMessage()
	{
		$this
			->given($this->newTestedInstance())
			->then
				->integer($this->testedInstance->unserializeMessage($data = uniqid()))->isZero()
				->string($this->testedInstance->data)->isEmpty()
				->integer($this->testedInstance->unserializeMessage(($message = $data . "\r\n") . uniqid()))->isEqualTo(strlen($message))
				->string($this->testedInstance->data)->isEqualTo($message)
		;
	}
}
