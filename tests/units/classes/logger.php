<?php

namespace server\tests\units;

require __DIR__ . '/../runner.php';

use
	atoum,
	server\logger as testedClass
;

class logger extends atoum
{
	public function test__construct()
	{
		$this
			->if($logger = new testedClass())
			->then
				->array($logger->getWriters())->isEmpty()
				->array($logger->getDecorators())->isEmpty()
		;
	}

	public function testAddWriter()
	{
		$this
			->if($logger = new testedClass())
			->then
				->object($logger->addWriter($writer1 = new \mock\server\logger\writer()))->isIdenticalTo($logger)
				->array($logger->getWriters())->isEqualTo(array($writer1))
				->object($logger->addWriter($writer2 = new \mock\server\logger\writer()))->isIdenticalTo($logger)
				->array($logger->getWriters())->isEqualTo(array($writer1, $writer2))
				->object($logger->addWriter($writer1 = new \mock\server\logger\writer()))->isIdenticalTo($logger)
				->array($logger->getWriters())->isEqualTo(array($writer1, $writer2, $writer1))
		;
	}

	public function testAddDecorator()
	{
		$this
			->if($logger = new testedClass())
			->then
				->object($logger->addDecorator($decorator1 = new \mock\server\logger\decorator()))->isIdenticalTo($logger)
				->array($logger->getDecorators())->isEqualTo(array($decorator1))
				->object($logger->addDecorator($decorator2 = new \mock\server\logger\decorator()))->isIdenticalTo($logger)
				->array($logger->getDecorators())->isEqualTo(array($decorator1, $decorator2))
				->object($logger->addDecorator($decorator1 = new \mock\server\logger\decorator()))->isIdenticalTo($logger)
				->array($logger->getDecorators())->isEqualTo(array($decorator1, $decorator2, $decorator1))
		;
	}

	public function testLog()
	{
		$this
			->if($logger = new testedClass())
			->then
				->object($logger->log($message = uniqid()))->isIdenticalTo($logger)

			->if(
				$logger
					->addWriter($writer1 = new \mock\server\logger\writer())
					->addWriter($writer2 = new \mock\server\logger\writer())
			)
			->then
				->object($logger->log($message = uniqid()))->isIdenticalTo($logger)
				->mock($writer1)->call('log')->withArguments($message)->once()
				->mock($writer2)->call('log')->withArguments($message)->once()

			->if(
				$logger
					->addDecorator($decorator1 = new \mock\server\logger\decorator())
					->addDecorator($decorator2 = new \mock\server\logger\decorator()),
				$this->calling($decorator1)->decorateLog = $decoratedLog1 = uniqid(),
				$this->calling($decorator2)->decorateLog = $decoratedLog2 = uniqid()
			)
			->then
				->object($logger->log($message = uniqid()))->isIdenticalTo($logger)
				->mock($decorator1)->call('decorateLog')->withArguments($message)->once()
				->mock($decorator2)->call('decorateLog')->withArguments($decoratedLog1)->once()
				->mock($writer1)->call('log')->withArguments($decoratedLog2)->once()
				->mock($writer2)->call('log')->withArguments($decoratedLog2)->once()
				->object($logger->log(($message1 = uniqid()) . "\n" . ($message2 = uniqid())))->isIdenticalTo($logger)
				->mock($writer1)->call('log')->withArguments($decoratedLog2 . $decoratedLog2)->once()
				->mock($writer2)->call('log')->withArguments($decoratedLog2 . $decoratedLog2)->once()
		;
	}
}
