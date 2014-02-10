<?php

namespace server;

use
	atoum,
	atoum\script,
	server\fs,
	server\unix
;

abstract class daemon extends script\configurable
{
	const defaultUmask = 0133;
	const defaultStdinFile = '/dev/null';
	const defaultStdoutFile = '/dev/null';
	const defaultStderrFile = '/dev/null';

	protected $user = null;
	protected $controller = null;
	protected $stdinFileReader = null;
	protected $stdoutFileWriter = null;
	protected $stderrFileWriter = null;
	protected $infoLogger = null;
	protected $errorLogger = null;
	protected $outputLogger = null;
	protected $foreground = false;
	protected $isDaemon = false;
	protected $pid = null;
	protected $unixSocket = null;

	private $payload = null;

	public function __construct($name, atoum\adapter $adapter = null)
	{
		parent::__construct($name, $adapter);

		$this
			->setInfoLogger()
			->setErrorLogger()
			->setOutputLogger()
			->setUnixUser()
			->setController()
			->setStdinFileReader()
			->setStdoutFileWriter()
			->setStderrFileWriter()
		;
	}

	public function __call($method, $arguments)
	{
		if ($this->payload === null)
		{
			throw $this->getException('Method ' . get_class($this) . '::' . $method . '() is unknown');
		}

		$return = call_user_func_array(array($this->payload, $method), $arguments);

		if ($return === $this->payload)
		{
			$return = $this;
		}

		return $return;
	}

	public function runInForeground()
	{
		$this->foreground = true;

		return $this;
	}

	public function setUnixSocket(fs\path $path)
	{
		$this->unixSocket = $path;

		return $this;
	}

	public function getUnixSocket()
	{
		return $this->unixSocket;
	}

	public function setUnixUser(unix\user $user = null)
	{
		$this->user = $user ?: new unix\user();

		return $this;
	}

	public function getUnixUser()
	{
		return $this->user;
	}

	public function getPayload()
	{
		return $this->payload;
	}

	public function setPayload(daemon\payload $payload)
	{
		$this->payload = $payload;

		return $this;
	}

	public function setUid($name)
	{
		try
		{
			$this->user->setLogin($name);
		}
		catch (\exception $exception)
		{
			throw $this->getException('UID \'' . $name . '\' is unknown');
		}

		return $this;
	}

	public function getUid()
	{
		return $this->user->getUid();
	}

	public function getGid()
	{
		return $this->user->getGid();
	}

	public function getHome()
	{
		return $this->user->getHomePath();
	}

	public function setController(daemon\controller $controller = null)
	{
		$this->controller = $controller ?: new daemon\controller();

		return $this;
	}

	public function getController()
	{
		return $this->controller;
	}

	public function getPid()
	{
		return $this->pid;
	}

	public function isDaemon()
	{
		return ($this->isDaemon === true);
	}

	public function isForeground()
	{
		return ($this->isDaemon() === false || $this->foreground === true);
	}

	public function getStdinFileReader()
	{
		return $this->stdinFileReader;
	}

	public function setStdinFileReader(readers\file $reader = null)
	{
		$this->stdinFileReader = $reader ?: new readers\file(static::defaultStdinFile);

		return $this;
	}

	public function getStdoutFileWriter()
	{
		return $this->stdoutFileWriter;
	}

	public function setStdoutFileWriter(writers\file $writer = null)
	{
		$this->stdoutFileWriter = $writer ?: new writers\file(static::defaultStdoutFile);

		return $this;
	}

	public function getStderrFileWriter()
	{
		return $this->stderrFileWriter;
	}

	public function setStderrFileWriter(writers\file $writer = null)
	{
		$this->stderrFileWriter = $writer ?: new writers\file(static::defaultStderrFile);

		return $this;
	}

	public function getInfoLogger()
	{
		return $this->infoLogger;
	}

	public function setInfoLogger(\server\logger $logger = null)
	{
		$this->infoLogger = $logger ?: new \server\logger();

		return $this;
	}

	public function getErrorLogger()
	{
		return $this->errorLogger;
	}

	public function setErrorLogger(\server\logger $logger = null)
	{
		$this->errorLogger = $logger ?: new \server\logger();

		return $this;
	}

	public function getOutputLogger()
	{
		return $this->outputLogger;
	}

	public function setOutputLogger(\server\logger $logger = null)
	{
		$this->outputLogger = $logger ?: new \server\logger();

		return $this;
	}

	public function errorHandler($error, $message, $file, $line, $context)
	{
		$errorReporting = error_reporting();

		if ($errorReporting !== 0)
		{
			$deep = 0;

			$message = 'Error ' . $error . ' in file \'' . $file . '\' on line ' . $line . ': ' . $message . PHP_EOL . 'Error backtrace:';

			foreach (array_reverse(debug_backtrace()) as $trace)
			{
				$traceMessage = '';

				if (isset($trace['file']) === true)
				{
					$traceMessage .= 'File \'' . $trace['file'] . '\'';
				}

				if (isset($trace['line']) === true)
				{
					$traceMessage .= ' on line ' . $trace['line'];
				}

				if ($traceMessage !== '')
				{
					$message .= PHP_EOL . '#' . ++$deep . ' ' . $traceMessage;
				}
			}

			$this->errorLogger->log($message);
		}

		return true;
	}

	public function exceptionHandler($exception)
	{
		$this->errorLogger
			->log($exception->getMessage())
			->log($exception->getTraceAsString())
		;

		return true;
	}

	public function writeMessage($message)
	{
		if ($this->isDaemon() === false)
		{
			parent::writeMessage($message);
		}
		else
		{
			$this->outputLogger->log($message);
		}

		return $this;
	}

	public function writeInfo($info)
	{
		if ($this->isForeground() === true)
		{
			parent::writeInfo($info);
		}
		else
		{
			$this->infoLogger->log($info);
		}

		return $this;
	}

	public function writeHelp($help)
	{
		if ($this->isForeground() === true)
		{
			parent::writeHelp($help);
		}
		else
		{
			$this->outputLogger->log($help);
		}

		return $this;
	}

	public function writeWarning($warning)
	{
		if ($this->isForeground() === true)
		{
			parent::writeWarning($warning);
		}
		else
		{
			$this->errorLogger->log($warning);
		}

		return $this;
	}

	public function writeError($error)
	{
		if ($this->isForeground() === true)
		{
			parent::writeError($error);
		}
		else
		{
			$this->errorLogger->log($error);
		}

		return $this;
	}

	protected function doRun()
	{
		if ($this->payload === null)
		{
			throw $this->getException('Payload is undefined');
		}

		if ($this->user->getUid() === null)
		{
			throw $this->getException('UID is undefined');
		}

		if ($this->user->getHomePath() === null)
		{
			throw $this->getException('Home is undefined');
		}

		if ($this->fork()->isDaemon() === true)
		{
			if ($this->isForeground() === false && posix_setsid() < 0)
			{
				throw $this->getException('Unable to become a session leader');
			}

			if ($this->fork()->isDaemon() === true)
			{
				if (posix_setgid($this->getGid()) === false)
				{
					throw $this->getException('Unable to set GID to \'' . $this->getGid() . '\'');
				}

				if (posix_setuid($this->getUid()) === false)
				{
					throw $this->getException('Unable to set UID to \'' . $this->getUid() . '\'');
				}

				try
				{
					$this->user->goToHome();
				}
				catch (\exception $exception)
				{
					throw $this->getException('Unable to set home directory to \'' . $this->getHome() . '\'');
				}

				umask(static::defaultUmask);

				if ($this->isForeground() === false)
				{
					if (defined('STDIN') === true)
					{
						@fclose(STDIN);
					}

					if (defined('STDOUT') === true)
					{
						@fclose(STDOUT);
					}

					if (defined('STDERR') === true)
					{
						@fclose(STDERR);
					}

					$this->stdinFileReader
						->closeFile()
						->openFile()
					;

					$this->stdoutFileWriter
						->closeFile()
						->openFile()
					;

					$this->stderrFileWriter
						->closeFile()
						->openFile()
					;
				}

				set_error_handler(array($this, 'errorHandler'));
				set_exception_handler(array($this, 'exceptionHandler'));

				$this->controller[SIGTERM] = $this->controller[SIGINT] = array($this->controller, 'stopDaemon');

				$this->payload->setInfoLogger($this->infoLogger);
				$this->payload->setErrorLogger($this->errorLogger);
				$this->payload->activate();

				declare(ticks=1)
				{
					while ($this->controller->dispatchSignals()->daemonShouldRun() === true)
					{
						$this->payload->release();
					}
				}

				$this->payload->deactivate();
			}
		}
	}

	protected function setArgumentHandlers()
	{
		parent::setArgumentHandlers()
			->addArgumentHandler(
					function($script, $argument, $values) {
						if (sizeof($values) !== 1)
						{
							throw new daemon\exceptions\invalidArgument(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
						}

						$script->setUid(reset($values));
					},
					array('-u', '--uid'),
					null,
					$this->locale->_('Define UID')
				)
			->addArgumentHandler(
					function($script, $argument, $values) {
						if (sizeof($values) !== 0)
						{
							throw new daemon\exceptions\invalidArgument(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
						}

						$script->runInForeground();
					},
					array('-f', '--foreground'),
					null,
					$this->locale->_('Run daemon in foreground')
				)
		;

		return $this;
	}

	protected function getException($message)
	{
		return new daemon\exception($message);
	}

	private function fork()
	{
		$this->isDaemon = false;

		if ($this->foreground === true)
		{
			$this->pid = posix_getpid();
			$this->isDaemon = true;
		}
		else
		{
			$this->pid =  pcntl_fork();

			if ($this->pid === -1)
			{
				throw $this->getException('Unable to fork to start daemon');
			}

			if ($this->pid !== 0)
			{
				pcntl_signal(SIGCHLD, SIG_IGN); // Avoid zombie
			}
			else
			{
				$this->isDaemon = true;
				$this->pid = posix_getpid();
			}
		}

		return $this;
	}
}
