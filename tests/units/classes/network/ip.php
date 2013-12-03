<?php

namespace server\tests\units\network;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\network\ip as testedClass
;

class ip extends atoum
{
	public function test__construct()
	{
		$this
			->if($ip = new testedClass($value = '127.0.0.1'))
			->then
				->castToString($ip)->isEqualTo($value)
			->if($ip = new testedClass(ip2long('192.0.34.166')))
			->then
				->castToString($ip)->isEqualTo('192.0.34.166')
			->if($ip = new testedClass(ip2long($value)))
			->then
				->castToString($ip)->isEqualTo($value)
			->exception(function() use (& $value) { new testedClass($value = '678.987.789.345'); })
				->isInstanceOf('server\network\ip\exception')
				->hasMessage('\'' . $value . '\' is not a valid IP address')
			->exception(function() use (& $value) { new testedClass($value = rand(-PHP_INT_MAX, -1)); })
				->isInstanceOf('server\network\ip\exception')
				->hasMessage('\'' . $value . '\' is not a valid IP address')
			->exception(function() use (& $value) { new testedClass($value = rand(4294967295, PHP_INT_MAX)); })
				->isInstanceOf('server\network\ip\exception')
				->hasMessage('\'' . $value . '\' is not a valid IP address')
		;
	}
}
