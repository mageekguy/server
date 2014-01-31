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
				->if($this->testedInstance->data = $data = uniqid())
				->then
					->string($this->testedInstance->data)->isEqualTo($data)
				->exception(function() use (& $unknownProperty) { $this->testedInstance->{$unknownProperty = uniqid()} = uniqid(); })
					->isInstanceOf('server\daemon\payloads\server\client\message\serializer\exception')
					->hasMessage('Unable to set value of property \'' . $unknownProperty . '\' because it does not exist')
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

			->if($this->testedInstance->data = $data = uniqid())
			->then
				->string($this->testedInstance->serializeMessage())->isEqualTo($data . "\r\n")
		;
	}

	public function testUnserializeMessage()
	{
		$this
			->given($this->newTestedInstance())
			->then
				->boolean($this->testedInstance->unserializeMessage($data = uniqid()))->isFalse
				->string($this->testedInstance->data)->isEmpty()
				->boolean($this->testedInstance->unserializeMessage($data . "\r\n"))->isTrue
				->string($this->testedInstance->data)->isEqualTo($data)
		;
	}
}
