<?php

namespace server\tests\units\script\configurable;

require __DIR__ . '/../../../runner.php';

use
	atoum,
	server\script\configurable,
	mock\server\script\configurable\daemon as testedClass
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

	public function test__construct()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->variable($daemon->getGid())->isNull()
				->variable($daemon->getUid())->isNull()
				->variable($daemon->getHome())->isNull()
				->object($daemon->getInfoLogger())->isEqualTo(new \server\logger())
				->object($daemon->getErrorLogger())->isEqualTo(new \server\logger())
				->object($daemon->getOutputLogger())->isEqualTo(new \server\logger())
				->boolean($daemon->isDaemon())->isFalse()
				->object($daemon->getController())->isEqualTo(new configurable\daemon\controller())
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
				->object($daemon->setController($controller = new configurable\daemon\controller()))->isIdenticalTo($daemon)
				->object($daemon->getController())->isIdenticalTo($controller)
				->object($daemon->setController())->isIdenticalTo($daemon)
				->object($daemon->getController())
					->isNotIdenticalTo($controller)
					->isEqualTo(new configurable\daemon\controller())
		;
	}

	public function testSetUid()
	{
		$this
			->given($daemon = new testedClass(uniqid()))

			->if($this->function->posix_getpwuid = false)
			->then
				->exception(function() use ($daemon, & $uidName) { $daemon->setUid($uidName = uniqid()); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('UID \'' . $uidName . '\' is unknown')
				->variable($daemon->getUid())->isNull()

			->if($this->function->posix_getpwuid = array('uid' => $uid = rand(1, PHP_INT_MAX), 'gid' => $gid = rand(1, PHP_INT_MAX)))
			->then
				->object($daemon->setUid($uidName))->isIdenticalTo($daemon)
				->integer($daemon->getUid())->isEqualTo($uid)
		;
	}

	public function testSetHome()
	{
		$this
			->if($daemon = new testedClass(uniqid()))
			->then
				->object($daemon->setHome($home = uniqid()))->isIdenticalTo($daemon)
				->string($daemon->getHome())->isEqualTo($home)
		;
	}

	public function testErrorHandler()
	{
		$this
			->if(
				$daemon = new testedClass(uniqid()),
				$daemon->setErrorLogger($errorLogger = new \mock\server\logger()),
				$this->calling($errorLogger)->log->returnThis(),
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
						->withArguments('Error ' . $code . ' in file \'' . $file . '\' on line ' . $line . ': ' . $message)->once()
						->withArguments('Error backtrace:')->once()
						->withArguments('#1 File \'' . $file3 . '\' on line ' . $line3)->once()
						->withArguments('#2 File \'' . $file2 . '\' on line ' . $line2)->once()
						->withArguments('#3 File \'' . $file1 . '\' on line ' . $line1)->once()
		;
	}

	public function testExceptionHandler()
	{
		$this
			->if(
				$daemon = new testedClass(uniqid()),
				$daemon->setErrorLogger($errorLogger = new \mock\server\logger()),
				$this->calling($errorLogger)->log->returnThis()
			)
			->then
				->boolean($daemon->exceptionHandler($exception = new \exception(uniqid())))->isTrue()
				->mock($errorLogger)->call('log')
					->withArguments($exception->getMessage())->once()
					->withArguments($exception->getTraceAsString())->once()
		;
	}

	public function testOutputHandler()
	{
		$this
			->if(
				$daemon = new testedClass(uniqid()),
				$daemon->setOutputLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->returnThis()
			)
			->then
				->string($daemon->outputHandler($buffer = uniqid()))->isEmpty()
				->mock($logger)->call('log')->withArguments($buffer)->once()
				->string($daemon->outputHandler(''))->isEmpty()
				->mock($logger)->call('log')->once()
		;
	}

	public function testWriteMessage()
	{
		$this
			->given(
				$daemon = new testedClass(uniqid()),
				$daemon->setOutputWriter($writer = new \mock\atoum\writer()),
				$this->calling($writer)->write->returnThis(),
				$daemon->setOutputLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->returnThis()
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
				$this->calling($writer)->write->returnThis(),
				$daemon->setOutputLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->returnThis()
			)

			->if($this->calling($daemon)->isDaemon = false)
			->then
				->object($daemon->writeInfo($info = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($info)->once()
				->mock($logger)->call('log')->withArguments($info)->never()

			->if($this->calling($daemon)->isDaemon = true)
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
				$this->calling($writer)->write->returnThis(),
				$daemon->setOutputLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->returnThis()
			)

			->if($this->calling($daemon)->isDaemon = false)
			->then
				->object($daemon->writeHelp($help = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($help)->once()
				->mock($logger)->call('log')->withArguments($help)->never()

			->if($this->calling($daemon)->isDaemon = true)
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
				$this->calling($writer)->write->returnThis(),
				$daemon->setErrorLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->returnThis()
			)

			->if($this->calling($daemon)->isDaemon = false)
			->then
				->object($daemon->writeWarning($warning = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($warning)->once()
				->mock($logger)->call('log')->withArguments($warning)->never()

			->if($this->calling($daemon)->isDaemon = true)
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
				$this->calling($writer)->write->returnThis(),
				$daemon->setErrorLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->returnThis()
			)

			->if($this->calling($daemon)->isDaemon = false)
			->then
				->object($daemon->writeError($error = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($error)->once()
				->mock($logger)->call('log')->withArguments($error)->never()

			->if($this->calling($daemon)->isDaemon = true)
			->then
				->object($daemon->writeError($error = uniqid()))->isIdenticalTo($daemon)
				->mock($writer)->call('write')->withArguments($error)->never()
				->mock($logger)->call('log')->withArguments($error)->once()
		;
	}

	public function testRun()
	{
		$this
			->given(
				$daemon = new testedClass(uniqid()),
				$daemon->setController($controller = new \mock\server\script\configurable\daemon\controller())
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('UID is undefined')

			->if($this->calling($daemon)->getUid = $uid = rand(1, PHP_INT_MAX))
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Home is undefined')

			->if(
				$this->calling($daemon)->getHome = $home = uniqid(),
				$this->function->pcntl_fork = -1
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Unable to fork to start daemon')

			->if(
				$this->function->pcntl_fork = $pid = rand(1, PHP_INT_MAX),
				$this->function->pcntl_signal->doesNothing()
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
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Unable to become a session leader')
				->boolean($daemon->isDaemon())->isFalse()
				->integer($daemon->getPid())->isEqualTo($pid)

			->if(
				$this->function->posix_setsid = 0,
				$this->function->pcntl_fork[2] = -1
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Unable to fork to start daemon')
				->boolean($daemon->isDaemon())->isFalse()
				->integer($daemon->getPid())->isEqualTo($pid)

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
				$this->function->chdir = false,
				$this->function->set_error_handler->doesNothing(),
				$this->function->set_exception_handler->doesNothing(),
				$this->function->ob_start->doesNothing(),
				$this->function->ob_implicit_flush->doesNothing(),
				$this->function->ob_get_level[1] = 1,
				$this->function->ob_get_level[2] = 0,
				$this->function->ob_end_flush->doesNothing()
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Unable to set home directory to \'' . $daemon->getHome() . '\'')
				->boolean($daemon->isDaemon())->isTrue()
				->integer($daemon->getPid())->isEqualTo($pid)
				->function('set_error_handler')->wasCalledWithArguments(array($daemon, 'errorHandler'))->once()
				->function('set_exception_handler')->wasCalledWithArguments(array($daemon, 'exceptionHandler'))->once()
				->function('ob_start')
					->wasCalledWithArguments(array($daemon, 'outputHandler'))
						->before($this->function('ob_implicit_flush')->wasCalledWithArguments(true)->once())
							->once()

			->if(
				$this->function->chdir = true,
				$this->function->posix_setgid = false
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Unable to set GID to \'' . $daemon->getGid() . '\'')
				->boolean($daemon->isDaemon())->isTrue()
				->integer($daemon->getPid())->isEqualTo($pid)

			->if(
				$this->function->posix_setgid = true,
				$this->function->posix_setuid = false
			)
			->then
				->exception(function() use ($daemon) { $daemon->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Unable to set UID to \'' . $daemon->getUid() . '\'')
				->boolean($daemon->isDaemon())->isTrue()
				->integer($daemon->getPid())->isEqualTo($pid)

			->if(
				$this->function->posix_setuid = true,
				$this->function->umask->doesNothing(),
				$this->function->fclose->doesNothing(),
				$this->calling($controller)->start->returnThis(),
				$this->calling($controller)->daemonShouldRun[1] = true,
				$this->calling($controller)->daemonShouldRun[2] = false
			)
			->then
				->object($daemon->run())->isIdenticalTo($daemon)
				->boolean($daemon->isDaemon())->isTrue()
				->integer($daemon->getPid())->isEqualTo($pid)
				->function('umask')->wasCalledWithArguments(137)->once()
				->function('fclose')
					->wasCalledWithArguments(STDIN)->once()
					->wasCalledWithArguments(STDOUT)->once()
					->wasCalledWithArguments(STDERR)->once()
				->mock($controller)
					->call('daemonShouldRun')->wasCalled()
						->after(
							$this->mock($controller)
								->call('start')
									->after($this->mock($controller)->call('offsetSet')->withArguments(SIGTERM, array($controller, 'stopDaemon'))->once())
										->twice()
						)
							->before($this->mock($daemon)->call('runDaemon')->once())
								->once()
				->mock($daemon)->call('stopDaemon')->once()
		;
	}
}
