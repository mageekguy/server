<?php

namespace server\tests\units\script\configurable;

require __DIR__ . '/../../../runner.php';

use
	atoum,
	server\script\configurable\daemon as testedClass,
	mock\server\script\configurable\daemon as mockedTestedClass
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
		$this->testedClass->extends('atoum\script\configurable');
	}

	public function test__construct()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->variable($server->getGid())->isNull()
				->variable($server->getUid())->isNull()
				->variable($server->getHome())->isNull()
				->object($server->getInfoLogger())->isEqualTo(new \server\logger())
				->object($server->getErrorLogger())->isEqualTo(new \server\logger())
				->object($server->getOutputLogger())->isEqualTo(new \server\logger())
				->boolean($server->isDaemon())->isFalse()
		;
	}

	public function testSetInfoLogger()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->setInfoLogger($logger = new \server\logger()))->isIdenticalTo($server)
				->object($server->getInfoLogger())->isIdenticalTo($logger)
				->object($server->setInfoLogger())->isIdenticalTo($server)
				->object($server->getInfoLogger())
					->isNotIdenticalTo($logger)
					->isEqualTo(new \server\logger())
		;
	}

	public function testSetErrorLogger()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->setErrorLogger($logger = new \server\logger()))->isIdenticalTo($server)
				->object($server->getErrorLogger())->isIdenticalTo($logger)
				->object($server->setErrorLogger())->isIdenticalTo($server)
				->object($server->getErrorLogger())
					->isNotIdenticalTo($logger)
					->isEqualTo(new \server\logger())
		;
	}

	public function testSetOutputLogger()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->setOutputLogger($logger = new \server\logger()))->isIdenticalTo($server)
				->object($server->getOutputLogger())->isIdenticalTo($logger)
				->object($server->setOutputLogger())->isIdenticalTo($server)
				->object($server->getOutputLogger())
					->isNotIdenticalTo($logger)
					->isEqualTo(new \server\logger())
		;
	}

	public function testSetUid()
	{
		$this
			->given($server = new testedClass(uniqid()))

			->if($this->function->posix_getpwuid = false)
			->then
				->exception(function() use ($server, & $uidName) { $server->setUid($uidName = uniqid()); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('UID \'' . $uidName . '\' is unknown')
				->variable($server->getUid())->isNull()

			->if($this->function->posix_getpwuid = array('uid' => $uid = rand(1, PHP_INT_MAX), 'gid' => $gid = rand(1, PHP_INT_MAX)))
			->then
				->object($server->setUid($uidName))->isIdenticalTo($server)
				->integer($server->getUid())->isEqualTo($uid)
		;
	}

	public function testSetHome()
	{
		$this
			->if($server = new testedClass(uniqid()))
			->then
				->object($server->setHome($home = uniqid()))->isIdenticalTo($server)
				->string($server->getHome())->isEqualTo($home)
		;
	}

	public function testErrorHandler()
	{
		$this
			->if(
				$server = new testedClass(uniqid()),
				$server->setErrorLogger($errorLogger = new \mock\server\logger()),
				$this->calling($errorLogger)->log->returnThis(),
				$this->function->debug_backtrace = array(
					array('file' => $file1 = uniqid(), 'line' => $line1 = rand(1, PHP_INT_MAX)),
					array('file' => $file2 = uniqid(), 'line' => $line2 = rand(1, PHP_INT_MAX)),
					array('file' => $file3 = uniqid(), 'line' => $line3 = rand(1, PHP_INT_MAX))
				)
			)
			->then
				->boolean($server->errorHandler($code = rand(1, PHP_INT_MAX), $message = uniqid(), $file = uniqid(), $line = rand(0, PHP_INT_MAX), $context = uniqid()))->isTrue()
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
				$server = new testedClass(uniqid()),
				$server->setErrorLogger($errorLogger = new \mock\server\logger()),
				$this->calling($errorLogger)->log->returnThis()
			)
			->then
				->boolean($server->exceptionHandler($exception = new \exception(uniqid())))->isTrue()
				->mock($errorLogger)->call('log')
					->withArguments($exception->getMessage())->once()
					->withArguments($exception->getTraceAsString())->once()
		;
	}

	public function testOutputHandler()
	{
		$this
			->if(
				$server = new testedClass(uniqid()),
				$server->setOutputLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->returnThis()
			)
			->then
				->string($server->outputHandler($buffer = uniqid()))->isEmpty()
				->mock($logger)->call('log')->withArguments($buffer)->once()
				->string($server->outputHandler(''))->isEmpty()
				->mock($logger)->call('log')->once()
		;
	}

	public function testWriteMessage()
	{
		$this
			->given(
				$server = new mockedTestedClass(uniqid()),
				$server->setOutputWriter($writer = new \mock\atoum\writer()),
				$this->calling($writer)->write->returnThis(),
				$server->setOutputLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->returnThis()
			)

			->if($this->calling($server)->isDaemon = false)
			->then
				->object($server->writeMessage($message = uniqid()))->isIdenticalTo($server)
				->mock($writer)->call('write')->withArguments($message)->once()
				->mock($logger)->call('log')->withArguments($message)->never()

			->if($this->calling($server)->isDaemon = true)
			->then
				->object($server->writeMessage($message = uniqid()))->isIdenticalTo($server)
				->mock($writer)->call('write')->withArguments($message)->never()
				->mock($logger)->call('log')->withArguments($message)->once()
		;
	}

	public function testWriteInfo()
	{
		$this
			->given(
				$server = new mockedTestedClass(uniqid()),
				$server->setInfoWriter($writer = new \mock\atoum\writer()),
				$this->calling($writer)->write->returnThis(),
				$server->setOutputLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->returnThis()
			)

			->if($this->calling($server)->isDaemon = false)
			->then
				->object($server->writeInfo($info = uniqid()))->isIdenticalTo($server)
				->mock($writer)->call('write')->withArguments($info)->once()
				->mock($logger)->call('log')->withArguments($info)->never()

			->if($this->calling($server)->isDaemon = true)
			->then
				->object($server->writeInfo($info = uniqid()))->isIdenticalTo($server)
				->mock($writer)->call('write')->withArguments($info)->never()
				->mock($logger)->call('log')->withArguments($info)->once()
		;
	}

	public function testWriteHelp()
	{
		$this
			->given(
				$server = new mockedTestedClass(uniqid()),
				$server->setHelpWriter($writer = new \mock\atoum\writer()),
				$this->calling($writer)->write->returnThis(),
				$server->setOutputLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->returnThis()
			)

			->if($this->calling($server)->isDaemon = false)
			->then
				->object($server->writeHelp($help = uniqid()))->isIdenticalTo($server)
				->mock($writer)->call('write')->withArguments($help)->once()
				->mock($logger)->call('log')->withArguments($help)->never()

			->if($this->calling($server)->isDaemon = true)
			->then
				->object($server->writeHelp($help = uniqid()))->isIdenticalTo($server)
				->mock($writer)->call('write')->withArguments($help)->never()
				->mock($logger)->call('log')->withArguments($help)->once()
		;
	}

	public function testWriteWarning()
	{
		$this
			->given(
				$server = new mockedTestedClass(uniqid()),
				$server->setWarningWriter($writer = new \mock\atoum\writer()),
				$this->calling($writer)->write->returnThis(),
				$server->setErrorLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->returnThis()
			)

			->if($this->calling($server)->isDaemon = false)
			->then
				->object($server->writeWarning($warning = uniqid()))->isIdenticalTo($server)
				->mock($writer)->call('write')->withArguments($warning)->once()
				->mock($logger)->call('log')->withArguments($warning)->never()

			->if($this->calling($server)->isDaemon = true)
			->then
				->object($server->writeWarning($warning = uniqid()))->isIdenticalTo($server)
				->mock($writer)->call('write')->withArguments($warning)->never()
				->mock($logger)->call('log')->withArguments($warning)->once()
		;
	}

	public function testWriteError()
	{
		$this
			->given(
				$server = new mockedTestedClass(uniqid()),
				$server->setErrorWriter($writer = new \mock\atoum\writer()),
				$this->calling($writer)->write->returnThis(),
				$server->setErrorLogger($logger = new \mock\server\logger()),
				$this->calling($logger)->log->returnThis()
			)

			->if($this->calling($server)->isDaemon = false)
			->then
				->object($server->writeError($error = uniqid()))->isIdenticalTo($server)
				->mock($writer)->call('write')->withArguments($error)->once()
				->mock($logger)->call('log')->withArguments($error)->never()

			->if($this->calling($server)->isDaemon = true)
			->then
				->object($server->writeError($error = uniqid()))->isIdenticalTo($server)
				->mock($writer)->call('write')->withArguments($error)->never()
				->mock($logger)->call('log')->withArguments($error)->once()
		;
	}

	public function testRun()
	{
		$this
			->given($server = new mockedTestedClass(uniqid()))
			->then
				->exception(function() use ($server) { $server->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('UID is undefined')

			->if($this->calling($server)->getUid = $uid = rand(1, PHP_INT_MAX))
			->then
				->exception(function() use ($server) { $server->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Home is undefined')

			->if(
				$this->calling($server)->getHome = $home = uniqid(),
				$this->function->pcntl_fork = -1
			)
			->then
				->exception(function() use ($server) { $server->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Unable to fork to start daemon')

			->if(
				$this->function->pcntl_fork = $pid = rand(1, PHP_INT_MAX),
				$this->function->pcntl_signal->doesNothing()
			)
			->then
				->object($server->run())->isIdenticalTo($server)
				->boolean($server->isDaemon())->isFalse()
				->integer($server->getPid())->isEqualTo($pid)
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
				->exception(function() use ($server) { $server->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Unable to become a session leader')
				->boolean($server->isDaemon())->isFalse()
				->integer($server->getPid())->isEqualTo($pid)

			->if(
				$this->function->posix_setsid = 0,
				$this->function->pcntl_fork[2] = -1
			)
			->then
				->exception(function() use ($server) { $server->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Unable to fork to start daemon')
				->boolean($server->isDaemon())->isFalse()
				->integer($server->getPid())->isEqualTo($pid)

			->if(
				$this->function->pcntl_fork[2] = $pid = rand(1, PHP_INT_MAX),
				$this->function->pcntl_signal->doesNothing()
			)
			->then
				->object($server->run())->isIdenticalTo($server)
				->boolean($server->isDaemon())->isFalse()
				->integer($server->getPid())->isEqualTo($pid)
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
				->exception(function() use ($server) { $server->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Unable to set home directory to \'' . $server->getHome() . '\'')
				->boolean($server->isDaemon())->isTrue()
				->integer($server->getPid())->isEqualTo($pid)
				->function('set_error_handler')->wasCalledWithArguments(array($server, 'errorHandler'))->once()
				->function('set_exception_handler')->wasCalledWithArguments(array($server, 'exceptionHandler'))->once()
				->function('ob_start')
					->wasCalledWithArguments(array($server, 'outputHandler'))
						->before($this->function('ob_implicit_flush')->wasCalledWithArguments(true)->once())
							->once()

			->if(
				$this->function->chdir = true,
				$this->function->posix_setgid = false
			)
			->then
				->exception(function() use ($server) { $server->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Unable to set GID to \'' . $server->getGid() . '\'')
				->boolean($server->isDaemon())->isTrue()
				->integer($server->getPid())->isEqualTo($pid)

			->if(
				$this->function->posix_setgid = true,
				$this->function->posix_setuid = false
			)
			->then
				->exception(function() use ($server) { $server->run(); })
					->isInstanceOf('server\script\configurable\daemon\exception')
					->hasMessage('Unable to set UID to \'' . $server->getUid() . '\'')
				->boolean($server->isDaemon())->isTrue()
				->integer($server->getPid())->isEqualTo($pid)

			->if(
				$this->function->posix_setuid = true,
				$this->function->umask->doesNothing(),
				$this->function->fclose->doesNothing()
			)
			->then
				->object($server->run())->isIdenticalTo($server)
				->boolean($server->isDaemon())->isTrue()
				->integer($server->getPid())->isEqualTo($pid)
				->function('umask')->wasCalledWithArguments(137)->once()
				->function('fclose')
					->wasCalledWithArguments(STDIN)->once()
					->wasCalledWithArguments(STDOUT)->once()
					->wasCalledWithArguments(STDERR)->once()
		;
	}
}
