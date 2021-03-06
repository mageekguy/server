<?php

namespace server\tests\units;

require __DIR__ . '/../runner.php';

use
	atoum,
	server,
	server\fs,
	server\unix,
	mock\server\daemon as testedClass
;

/*
According to http://lib.ru/UNIXFAQ/unixprogrfaq.txt, here are the steps to become a daemon:

  1. `fork()' so the parent can exit, this returns control to the command
     line or shell invoking your program.  This step is required so that
     the new process is guaranteed not to be a process group leader. The
     next step, `setsid()', fails if you're a process group leader.

  2. `setsid()' to become a process group and session group leader. Since a
     controlling terminal is associated with a session, and this new
     session has not yet acquired a controlling terminal our process now
     has no controlling terminal, which is a Good Thing for daemons.

  3. `fork()' again so the parent, (the session group leader), can exit.
     This means that we, as a non-session group leader, can never regain a
     controlling terminal (see Stevens's book "Advanced # Programming in the
	  UNIX Environment" (Addison-Wesley) for details).

  4. `chdir("/")' to ensure that our process doesn't keep any directory in
     use. Failure to do this could make it so that an administrator
     couldn't unmount a filesystem, because it was our current directory.

     [Equivalently, we could change to any directory containing files
     important to the daemon's operation.]

  5. `umask(0)' so that we have complete control over the permissions of
     anything we write. We don't know what umask we may have inherited.

     [This step is optional]

  6. `close()' fds 0, 1, and 2. This releases the standard in, out, and
     error we inherited from our parent process. We have no way of knowing
     where these fds might have been redirected to. Note that many daemons
     use `sysconf()' to determine the limit `_SC_OPEN_MAX'.  `_SC_OPEN_MAX'
     tells you the maximun open files/process. Then in a loop, the daemon
     can close all possible file descriptors. You have to decide if you
     need to do this or not.  If you think that there might be
     file-descriptors open you should close them, since there's a limit on
     number of concurrent file descriptors.

  7. Establish new open descriptors for stdin, stdout and stderr. Even if
     you don't plan to use them, it is still a good idea to have them open.
     The precise handling of these is a matter of taste; if you have a
     logfile, for example, you might wish to open it as stdout or stderr,
     and open `/dev/null' as stdin; alternatively, you could open
     `/dev/console' as stderr and/or stdout, and `/dev/null' as stdin, or
     any other combination that makes sense for your particular daemon.
*/

class daemon extends atoum
{
	public function beforeTestMethod($method)
	{
		$test = $this;

		$this->then = function() use ($test) { $test->getTestAdapterStorage()->resetCalls(); return $test; };

		if (defined('SIGCHLD') === false)
		{
			define('SIGCHLD', uniqid());
		}

		if (defined('SIG_IGN') === false)
		{
			define('SIG_IGN', uniqid());
		}

		if (defined('STDIN') === false)
		{
			define('STDIN', uniqid());
		}

		if (defined('STDOUT') === false)
		{
			define('STDOUT', uniqid());
		}

		if (defined('STDERR') === false)
		{
			define('STDERR', uniqid());
		}
	}

	public function testClass()
	{
		$this->testedClass
			->isAbstract()
			->extends('atoum\script\configurable')
		;
	}

	public function testClassConstants()
	{
		$this
			->integer(testedClass::defaultUmask)->isEqualTo(0133) // rw-r--r--
			->string(testedClass::defaultStdinFile)->isEqualTo('/dev/null')
			->string(testedClass::defaultStdoutFile)->isEqualTo('/dev/null')
			->string(testedClass::defaultStderrFile)->isEqualTo('/dev/null')
		;
	}

	public function test__construct()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->variable($daemon->getGid())->isNull()
				->variable($daemon->getUid())->isNull()
				->variable($daemon->getHome())->isNull()
				->variable($daemon->getPayload())->isNull()
				->object($daemon->getInfoLogger())->isEqualTo(new \server\logger())
				->object($daemon->getErrorLogger())->isEqualTo(new \server\logger())
				->object($daemon->getOutputLogger())->isEqualTo(new \server\logger())
				->boolean($daemon->isDaemon())->isFalse()
				->object($daemon->getController())->isEqualTo(new server\daemon\controller())
				->object($daemon->getStdinFileReader())->isEqualTo(new server\readers\file(testedClass::defaultStdinFile))
				->object($daemon->getStdoutFileWriter())->isEqualTo(new server\writers\file(testedClass::defaultStdoutFile))
				->object($daemon->getStderrFileWriter())->isEqualTo(new server\writers\file(testedClass::defaultStderrFile))
		;
	}

	public function test__call()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->exception(function() use ($daemon, & $method) { $daemon->{$method = uniqid()}(); })
					->isInstanceOf('server\daemon\exception')
					->hasMessage('Method ' . get_class($daemon) . '::' . $method . '() is unknown')

			->if(
				$daemon->setPayload($payload = new \mock\server\daemon\payload()),
				$this->calling($payload)->{$method} = $returnValue = uniqid()
			)
			->then
				->string($daemon->{$method}($arg = uniqid()))->isEqualTo($returnValue)
				->mock($payload)->call($method)->withArguments($arg)->once()
		;
	}

	public function testRunInForeground()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->runInForeground())->isIdenticalTo($daemon)
		;
	}

	public function testSetUnixSocket()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->setUnixSocket($socket = new fs\path(uniqid())))->isIdenticalTo($daemon)
				->object($daemon->getUnixSocket())->isIdenticalTo($socket)
		;
	}

	public function testSetUnixUser()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->setUnixUser($user = new unix\user()))->isIdenticalTo($daemon)
				->object($daemon->getUnixUser())->isIdenticalTo($user)
				->object($daemon->setUnixUser())->isIdenticalTo($daemon)
				->object($daemon->getUnixUser())
					->isNotIdenticalTo($user)
					->isEqualTo(new unix\user())
		;
	}

	public function testSetStdinFileReader()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->setStdinFileReader($reader = new server\readers\file(uniqid())))->isIdenticalTo($daemon)
				->object($daemon->getStdinFileReader())->isEqualTo($reader)
				->object($daemon->setStdinFileReader())->isIdenticalTo($daemon)
				->object($daemon->getStdinFileReader())->isEqualTo(new server\readers\file(testedClass::defaultStdinFile))
		;
	}

	public function testSetStdoutFileWriter()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->setStdoutFileWriter($writer = new server\writers\file(uniqid())))->isIdenticalTo($daemon)
				->object($daemon->getStdoutFileWriter())->isEqualTo($writer)
				->object($daemon->setStdoutFileWriter())->isIdenticalTo($daemon)
				->object($daemon->getStdoutFileWriter())->isEqualTo(new server\writers\file(testedClass::defaultStdoutFile))
		;
	}

	public function testSetStderrFileWriter()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->setStderrFileWriter($writer = new server\writers\file(uniqid())))->isIdenticalTo($daemon)
				->object($daemon->getStderrFileWriter())->isEqualTo($writer)
				->object($daemon->setStderrFileWriter())->isIdenticalTo($daemon)
				->object($daemon->getStderrFileWriter())->isEqualTo(new server\writers\file(testedClass::defaultStderrFile))
		;
	}

	public function testSetInfoLogger()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->setInfoLogger($logger = new \server\logger()))->isIdenticalTo($daemon)
				->object($daemon->getInfoLogger())->isIdenticalTo($logger)
				->object($daemon->setInfoLogger())->isIdenticalTo($daemon)
				->object($daemon->getInfoLogger())
					->isNotIdenticalTo($logger)
					->isEqualTo(new \server\logger())
		;
	}

	public function testSetErrorLogger()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->setErrorLogger($logger = new \server\logger()))->isIdenticalTo($daemon)
				->object($daemon->getErrorLogger())->isIdenticalTo($logger)
				->object($daemon->setErrorLogger())->isIdenticalTo($daemon)
				->object($daemon->getErrorLogger())
					->isNotIdenticalTo($logger)
					->isEqualTo(new \server\logger())
		;
	}

	public function testSetOutputLogger()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->setOutputLogger($logger = new \server\logger()))->isIdenticalTo($daemon)
				->object($daemon->getOutputLogger())->isIdenticalTo($logger)
				->object($daemon->setOutputLogger())->isIdenticalTo($daemon)
				->object($daemon->getOutputLogger())
					->isNotIdenticalTo($logger)
					->isEqualTo(new \server\logger())
		;
	}

	public function testSetController()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->setController($controller = new server\daemon\controller()))->isIdenticalTo($daemon)
				->object($daemon->getController())->isIdenticalTo($controller)
				->object($daemon->setController())->isIdenticalTo($daemon)
				->object($daemon->getController())
					->isNotIdenticalTo($controller)
					->isEqualTo(new server\daemon\controller())
		;
	}

	public function testSetUid()
	{
		$this
			->given(
				$daemon = new testedClass(uniqid()),
				$daemon->setUnixUser($unixUser = new \mock\server\unix\user())
			)

			->if($this->calling($unixUser)->setLogin->throw = $exception = new \exception(uniqid()))
			->then
				->exception(function() use ($daemon, & $uidName) { $daemon->setUid($uidName = uniqid()); })
					->isInstanceOf('server\daemon\exception')
					->hasMessage('UID \'' . $uidName . '\' is unknown')
				->variable($daemon->getUid())->isNull()
				->mock($unixUser)->call('setLogin')->withArguments($uidName)->once()

			->if(
				$this->calling($unixUser)->setLogin->isFluent(),
				$this->calling($unixUser)->getUid = $uid = rand(1, PHP_INT_MAX),
				$this->calling($unixUser)->getHomePath = $home = uniqid()
			)
			->then
				->object($daemon->setUid($uidName))->isIdenticalTo($daemon)
				->integer($daemon->getUid())->isEqualTo($uid)
				->string($daemon->getHome())->isEqualTo($home)
				->mock($unixUser)->call('setLogin')->withArguments($uidName)->once()
		;
	}

	public function testSetPayload()
	{
		$this
			->if(
				$daemon = new testedClass(uniqid()),
				$payload = new \mock\server\daemon\payload(),
				$this->calling($payload)->setInfoLogger->isFluent(),
				$this->calling($payload)->setErrorLogger->isFluent()
			)
			->then
				->object($daemon->setPayload($payload))->isIdenticalTo($daemon)
				->object($daemon->getPayload())->isIdenticalTo($payload)
		;
	}

	public function testErrorHandler()
	{
		$this
			->if(
				$daemon = new testedClass(uniqid()),
				$daemon->setErrorLogger($errorLogger = new \mock\server\logger()),
				$this->calling($errorLogger)->log->isFluent(),
				$this->function->debug_backtrace = array(
					array('file' => $file1 = uniqid(), 'line' => $line1 = rand(1, PHP_INT_MAX)),
					array('file' => $file2 = uniqid(), 'line' => $line2 = rand(1, PHP_INT_MAX)),
					array('file' => $file3 = uniqid(), 'line' => $line3 = rand(1, PHP_INT_MAX))
				)
			)
			->then
				->boolean($daemon->errorHandler($code = rand(1, PHP_INT_MAX), $message = uniqid(), $file = uniqid(), $line = rand(0, PHP_INT_MAX), $context = uniqid()))->isTrue()
				->mock($errorLogger)
					->call('log')
						->withArguments(
							'Error ' . $code . ' in file \'' . $file . '\' on line ' . $line . ': ' . $message
							. PHP_EOL . 'Error backtrace:'
							. PHP_EOL . '#1 File \'' . $file3 . '\' on line ' . $line3
							. PHP_EOL . '#2 File \'' . $file2 . '\' on line ' . $line2
							. PHP_EOL . '#3 File \'' . $file1 . '\' on line ' . $line1
						)->once()
		;
	}

	public function testExceptionHandler()
	{
		$this
			->if(
				$daemon = new testedClass(uniqid()),
				$daemon->setErrorLogger($errorLogger = new \mock\server\logger()),
				$this->calling($errorLogger)->log->isFluent()
			)
			->then
				->boolean($daemon->exceptionHandler($exception = new \exception(uniqid())))->isTrue()
				->mock($errorLogger)->call('log')
					->withArguments($exception->getMessage())->once()
					->withArguments($exception->getTraceAsString())->once()
		;
	}

	public function testWriteMessage()
	{
		$this
			->given(
				$daemon = new testedClass(uniqid()),
				$daemon->setOutputWriter($writer = new \mock\atoum\writer()),
				$this->calling($writer)->write->isFluent(),
				$daemon->setOutputLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->isFluent()
			)

			->if($this->calling($daemon)->isDaemon = false)
			->then
				->object($daemon->writeMessage($message = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($message)->once()
				->mock($logger)->call('log')->withArguments($message)->never()

			->if($this->calling($daemon)->isDaemon = true)
			->then
				->object($daemon->writeMessage($message = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($message)->never()
				->mock($logger)->call('log')->withArguments($message)->once()
		;
	}

	public function testWriteInfo()
	{
		$this
			->given(
				$daemon = new testedClass(uniqid()),
				$daemon->setInfoWriter($writer = new \mock\atoum\writer()),
				$this->calling($writer)->write->isFluent(),
				$daemon->setInfoLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->isFluent()
			)

			->if($this->calling($daemon)->isForeground = true)
			->then
				->object($daemon->writeInfo($info = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($info)->once()
				->mock($logger)->call('log')->withArguments($info)->never()

			->if($this->calling($daemon)->isForeground = false)
			->then
				->object($daemon->writeInfo($info = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($info)->never()
				->mock($logger)->call('log')->withArguments($info)->once()
		;
	}

	public function testWriteHelp()
	{
		$this
			->given(
				$daemon = new testedClass(uniqid()),
				$daemon->setHelpWriter($writer = new \mock\atoum\writer()),
				$this->calling($writer)->write->isFluent(),
				$daemon->setOutputLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->isFluent()
			)

			->if($this->calling($daemon)->isForeground = true)
			->then
				->object($daemon->writeHelp($help = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($help)->once()
				->mock($logger)->call('log')->withArguments($help)->never()

			->if($this->calling($daemon)->isForeground = false)
			->then
				->object($daemon->writeHelp($help = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($help)->never()
				->mock($logger)->call('log')->withArguments($help)->once()
		;
	}

	public function testWriteWarning()
	{
		$this
			->given(
				$daemon = new testedClass(uniqid()),
				$daemon->setWarningWriter($writer = new \mock\atoum\writer()),
				$this->calling($writer)->write->isFluent(),
				$daemon->setErrorLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->isFluent()
			)

			->if($this->calling($daemon)->isForeground = true)
			->then
				->object($daemon->writeWarning($warning = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($warning)->once()
				->mock($logger)->call('log')->withArguments($warning)->never()

			->if($this->calling($daemon)->isForeground = false)
			->then
				->object($daemon->writeWarning($warning = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($warning)->never()
				->mock($logger)->call('log')->withArguments($warning)->once()
		;
	}

	public function testWriteError()
	{
		$this
			->given(
				$daemon = new testedClass(uniqid()),
				$daemon->setErrorWriter($writer = new \mock\atoum\writer()),
				$this->calling($writer)->write->isFluent(),
				$daemon->setErrorLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->isFluent()
			)

			->if($this->calling($daemon)->isForeground = true)
			->then
				->object($daemon->writeError($error = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($error)->once()
				->mock($logger)->call('log')->withArguments($error)->never()

			->if($this->calling($daemon)->isForeground = false)
			->then
				->object($daemon->writeError($error = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($error)->never()
				->mock($logger)->call('log')->withArguments($error)->once()
		;
	}

	public function testRunWithFork()
	{
		$this
			->given(
				$daemon = new testedClass(uniqid()),
				$daemon->setUnixUser($unixUser = new \mock\server\unix\user()),
				$daemon->setController($controller = new \mock\server\daemon\controller()),
				$daemon->setStdinFileReader($stdinFileReader = new \mock\server\readers\file(uniqid())),
				$this->calling($stdinFileReader)->openFile->isFluent(),
				$daemon->setStdoutFileWriter($stdoutFileWriter = new \mock\server\writers\file(uniqid())),
				$this->calling($stdoutFileWriter)->openFile->isFluent(),
				$daemon->setStderrFileWriter($stderrFileWriter = new \mock\server\writers\file(uniqid())),
				$this->calling($stderrFileWriter)->openFile->isFluent()
			)

			->exception(function() use ($daemon) { $daemon->run(); })
				->isInstanceOf('server\daemon\exception')
				->hasMessage('Payload is undefined')

			->if(
				$daemon->setPayload($payload = new \mock\server\daemon\payload()),
				$this->calling($unixUser)->getUid = null
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\daemon\exception')
					->hasMessage('UID is undefined')

			->if($this->calling($unixUser)->getUid = $uid = rand(1, PHP_INT_MAX))
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\daemon\exception')
					->hasMessage('Home is undefined')

			->if(
				$this->calling($unixUser)->getHomePath = $home = uniqid(),
				$this->function->pcntl_fork = -1
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\daemon\exception')
					->hasMessage('Unable to fork to start daemon')

			->if(
				$this->function->pcntl_fork = $pid = rand(1, PHP_INT_MAX),
				$this->function->pcntl_signal->doesNothing(),
				$this->function->fclose->doesNothing(),
				$this->function->fopen->doesNothing()
			)
			->then
				->object($daemon->run())->isIdenticalTo($daemon)
				->boolean($daemon->isDaemon())->isFalse()
				->integer($daemon->getPid())->isEqualTo($pid)
				->function('pcntl_signal')
					->wasCalledWithArguments(SIGCHLD, SIG_IGN)
						->after($this->function('pcntl_fork')->wasCalled()->once())
							->once()

			->if(
				$this->function->pcntl_fork = 0,
				$this->function->posix_getpid = $pid = rand(1, PHP_INT_MAX),
				$this->function->posix_setsid = -1
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\daemon\exception')
					->hasMessage('Unable to become a session leader')
				->boolean($daemon->isDaemon())->isTrue()
				->integer($daemon->getPid())->isEqualTo($pid)

			->if(
				$this->function->posix_setsid = 0,
				$this->function->pcntl_fork[2] = -1
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\daemon\exception')
					->hasMessage('Unable to fork to start daemon')
				->boolean($daemon->isDaemon())->isFalse()
				->integer($daemon->getPid())->isEqualTo(-1)

			->if(
				$this->function->pcntl_fork[2] = $pid = rand(1, PHP_INT_MAX),
				$this->function->pcntl_signal->doesNothing()
			)
			->then
				->object($daemon->run())->isIdenticalTo($daemon)
				->boolean($daemon->isDaemon())->isFalse()
				->integer($daemon->getPid())->isEqualTo($pid)
				->function('pcntl_signal')
					->wasCalledWithArguments(SIGCHLD, SIG_IGN)
						->after($this->function('pcntl_fork')->wasCalled()->twice())
							->once()

			->if(
				$this->function->pcntl_fork[2] = 0,
				$this->function->posix_getpid[2] = $pid = rand(1, PHP_INT_MAX),
				$this->function->set_error_handler->doesNothing(),
				$this->function->set_exception_handler->doesNothing(),
				$this->function->posix_setgid = false
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\daemon\exception')
					->hasMessage('Unable to set GID to \'' . $daemon->getGid() . '\'')
				->boolean($daemon->isDaemon())->isTrue()
				->integer($daemon->getPid())->isEqualTo($pid)

			->if(
				$this->function->posix_setgid = true,
				$this->function->posix_setuid = false
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\daemon\exception')
					->hasMessage('Unable to set UID to \'' . $daemon->getUid() . '\'')
				->boolean($daemon->isDaemon())->isTrue()
				->integer($daemon->getPid())->isEqualTo($pid)

			->if(
				$this->function->posix_setuid = true,
				$this->calling($unixUser)->goToHome->throw = $exception = new \exception(uniqid())
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\daemon\exception')
					->hasMessage('Unable to set home directory to \'' . $daemon->getHome() . '\'')
				->boolean($daemon->isDaemon())->isTrue()
				->integer($daemon->getPid())->isEqualTo($pid)

			->if(
				$this->calling($unixUser)->goToHome->isFluent(),
				$this->function->umask->doesNothing(),
				$this->calling($controller)->dispatchSignals->isFluent(),
				$this->calling($controller)->daemonShouldRun[1] = true,
				$this->calling($controller)->daemonShouldRun[2] = false
			)
			->then
				->object($daemon->run())->isIdenticalTo($daemon)
				->boolean($daemon->isdaemon())->istrue()
				->integer($daemon->getpid())->isequalto($pid)
				->function('set_error_handler')->wasCalledWithArguments(array($daemon, 'errorHandler'))->once()
				->function('set_exception_handler')->wasCalledWithArguments(array($daemon, 'exceptionHandler'))->once()
				->function('umask')->wasCalledWithArguments(testedClass::defaultUmask)->once()
				->before(
					$this->mock($stdinFileReader)->call('closeFile')
						->before($this->mock($stdinFileReader)->call('openFile')->once())
						->after(
							$this->function('fclose')->wasCalledWithArguments(STDIN)->once(),
							$this->function('fclose')->wasCalledWithArguments(STDOUT)->once(),
							$this->function('fclose')->wasCalledWithArguments(STDERR)->once()
						)
							->once(),
					$this->mock($stdoutFileWriter)->call('closeFile')
						->before($this->mock($stdoutFileWriter)->call('openFile')->once())
						->after(
							$this->function('fclose')->wasCalledWithArguments(STDIN)->once(),
							$this->function('fclose')->wasCalledWithArguments(STDOUT)->once(),
							$this->function('fclose')->wasCalledWithArguments(STDERR)->once()
						)
							->once(),
					$this->mock($stderrFileWriter)->call('closeFile')
						->before($this->mock($stderrFileWriter)->call('openFile')->once())
						->after(
							$this->function('fclose')->wasCalledWithArguments(STDIN)->once(),
							$this->function('fclose')->wasCalledWithArguments(STDOUT)->once(),
							$this->function('fclose')->wasCalledWithArguments(STDERR)->once()
						)
							->once()
				)
				->mock($controller)
					->call('daemonShouldRun')->wasCalled()
						->after(
							$this->mock($controller)
								->call('dispatchSignals')
									->after($this->mock($controller)->call('offsetSet')->withArguments(SIGTERM, array($controller, 'stopDaemon'))->once())
										->twice()
						)
							->before($this->mock($payload)->call('release')->once())
								->once()
				->mock($payload)
					->call('activate')
						->before($this->mock($payload)->call('release'))
							->once()
					->call('deactivate')
						->after($this->mock($payload)->call('release'))
							->once()
		;
	}

	public function testRunWithoutFork()
	{
		$this
			->given(
				$daemon = new testedClass(uniqid()),
				$daemon->setUnixUser($unixUser = new \mock\server\unix\user()),
				$daemon->setController($controller = new \mock\server\daemon\controller()),
				$daemon->setStdinFileReader($stdinFileReader = new \mock\server\readers\file(uniqid())),
				$this->calling($stdinFileReader)->openFile->isFluent(),
				$daemon->setStdoutFileWriter($stdoutFileWriter = new \mock\server\writers\file(uniqid())),
				$this->calling($stdoutFileWriter)->openFile->isFluent(),
				$daemon->setStderrFileWriter($stderrFileWriter = new \mock\server\writers\file(uniqid())),
				$this->calling($stderrFileWriter)->openFile->isFluent(),
				$daemon->runInForeground()
			)

			->exception(function() use ($daemon) { $daemon->run(); })
				->isInstanceOf('server\daemon\exception')
				->hasMessage('Payload is undefined')

			->if(
				$daemon->setPayload($payload = new \mock\server\daemon\payload()),
				$this->calling($unixUser)->getUid = null
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\daemon\exception')
					->hasMessage('UID is undefined')

			->if($this->calling($unixUser)->getUid = $uid = rand(1, PHP_INT_MAX))
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\daemon\exception')
					->hasMessage('Home is undefined')

			->if(
				$this->calling($unixUser)->getHomePath = $home = uniqid(),
				$this->function->posix_getpid = $pid = rand(1, PHP_INT_MAX),
				$this->function->pcntl_fork->doesNothing(),
				$this->function->pcntl_signal->doesNothing(),
				$this->function->fclose->doesNothing(),
				$this->function->posix_setsid = 0,
				$this->function->posix_setgid = true,
				$this->function->posix_setuid = true,
				$this->function->umask->doesNothing(),
				$this->calling($controller)->dispatchSignals->isFluent(),
				$this->calling($controller)->daemonShouldRun[1] = true,
				$this->calling($controller)->daemonShouldRun[2] = false
			)
			->then
				->object($daemon->run())->isIdenticalTo($daemon)
				->boolean($daemon->isDaemon())->isTrue()
				->integer($daemon->getPid())->isEqualTo($pid)
				->function('pcntl_fork')->wasCalled()->never()
				->function('posix_setsid')->wasCalled()->never()
				->function('pcntl_signal')->wasCalled()->never()
				->function('fclose')->wasCalled()->never()
				->mock($controller)
					->call('daemonShouldRun')->wasCalled()
						->after(
							$this->mock($controller)
								->call('dispatchSignals')
									->after($this->mock($controller)->call('offsetSet')->withArguments(SIGTERM, array($controller, 'stopDaemon'))->once())
										->twice()
						)
							->before($this->mock($payload)->call('release')->once())
								->once()
				->mock($payload)
					->call('activate')
						->before($this->mock($payload)->call('release'))
							->once()
					->call('deactivate')
						->after($this->mock($payload)->call('release'))
							->once()
				->mock($stdinFileReader)->wasNotCalled()
				->mock($stdoutFileWriter)->wasNotCalled()
				->mock($stderrFileWriter)->wasNotCalled()
		;
	}
}
