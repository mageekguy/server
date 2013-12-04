<?php

namespace server\tests\units\script\configurable\daemon;

require __DIR__ . '/../../../../runner.php';

use
	atoum,
	server\script\configurable\daemon\controller as testedClass
;

class controller extends atoum
{
	public function beforeTestMethod($method)
	{
		$test = $this;

		$this->then = function() use ($test) { $test->getTestAdapterStorage()->resetCalls(); return $test; };

		if (defined('SIGTERM') === false)
		{
			define('SIGTERM', 15);
		}

		if (defined('SIGHUP') === false)
		{
			define('SIGHUP', 1);
		}

		if (defined('SIG_DFL') === false)
		{
			define('SIG_DFL', rand(1, PHP_INT_MAX));
		}
	}

	public function testDispatchSignals()
	{
		$this
			->if(
				$controller = new testedClass(),
				$this->function->pcntl_signal_dispatch->doesNothing(),
				$this->function->pcntl_signal = true
			)
			->then
				->object($controller->dispatchSignals())->isIdenticalTo($controller)
				->function('pcntl_signal')->wasCalled()->never()
				->function('pcntl_signal_dispatch')->wasCalled()->once()

			->if(
				$controller[SIGTERM] = $sigtermHandler = function() {},
				$controller[SIGHUP] = $sigupHandler = function() {}
			)
			->then
				->object($controller->dispatchSignals())->isIdenticalTo($controller)
				->function('pcntl_signal_dispatch')
					->wasCalled()
						->before($this->function('pcntl_signal')
							->wasCalledWithArguments(SIGTERM, $sigtermHandler)->once()
							->wasCalledWithArguments(SIGHUP, $sigupHandler)->once()
						)
							->once()
				->boolean(isset($controller[SIGHUP]))->isFalse()
				->boolean(isset($controller[SIGTERM]))->isFalse()

			->if(
				$controller[SIGTERM] = $sigtermHandler = function() {},
				$controller[SIGHUP] = $sigupHandler = function() {},
				$this->function->pcntl_signal = false
			)
			->then
				->exception(function() use ($controller) { $controller->dispatchSignals(); })
					->isInstanceOf('server\script\configurable\daemon\controller\exception')
					->hasMessage('Unable to set handler for signal \'' . SIGTERM . '\'')
		;
	}

	public function testOffsetSet()
	{
		$this
			->if(
				$controller = new testedClass(),
				$controller[SIGTERM] = function() {}
			)
			->then
				->boolean(isset($controller[SIGTERM]))->isTrue()
				->boolean(isset($controller[SIGHUP]))->isFalse()

			->if(
				$controller[SIGHUP] = function() {}
			)
			->then
				->boolean(isset($controller[SIGTERM]))->isTrue()
				->boolean(isset($controller[SIGHUP]))->isTrue()

			->when(function() use ($controller) { unset($controller[SIGTERM]); })
			->then
				->boolean(isset($controller[SIGTERM]))->isFalse()
				->boolean(isset($controller[SIGHUP]))->isTrue()
		;
	}

	public function testOffsetUnset()
	{
		$this
			->if(
				$controller = new testedClass(),
				$this->function->pcntl_signal = true
			)

			->when(function() use ($controller) { unset($controller[SIGTERM]); })
				->boolean(isset($controller[SIGTERM]))->isFalse()
				->function('pcntl_signal')->wasCalledWithArguments(SIGTERM, SIG_DFL)->once()

			->if($controller[SIGHUP] = function() {})
			->when(function() use ($controller) { unset($controller[SIGHUP]); })
				->boolean(isset($controller[SIGHUP]))->isFalse()
				->function('pcntl_signal')->wasCalledWithArguments(SIGHUP, SIG_DFL)->once()
		;
	}

	public function testDaemonShouldRun()
	{
		$this
			->if($controller = new testedClass())
			->then
				->boolean($controller->daemonShouldRun())->isTrue()

			->if($controller->stopDaemon())
			->then
				->boolean($controller->daemonShouldRun())->isFalse()
		;
	}
}
