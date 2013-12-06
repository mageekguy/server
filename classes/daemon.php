<?php

namespace server;

use
	atoum,
	atoum\script,
	server\unix
;

abstract class daemon extends script\configurable
{
	protected $user = null;
	protected $controller = null;
	protected $infoLogger = null;
	protected $errorLogger = null;
	protected $outputLogger = null;
	protected $isDaemon = false;
	protected $pid = null;

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
			$this->errorLogger
				->log('Error ' . $error . ' in file \'' . $file . '\' on line ' . $line . ': ' . $message)
				->log('Error backtrace:')
			;

			$deep = 0;

			foreach (array_reverse(debug_backtrace()) as $trace)
			{
				$logMessage = '';

				if (isset($trace['file']) === true)
				{
					$logMessage .= 'File \'' . $trace['file'] . '\'';
				}

				if (isset($trace['line']) === true)
				{
					$logMessage .= ' on line ' . $trace['line'];
				}

				if ($logMessage !== '')
				{
					$this->errorLogger->log('#' . ++$deep . ' ' . $logMessage);
				}
			}
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

	public function outputHandler($buffer)
	{
		if ($buffer != '')
		{
			$this->outputLogger->log($buffer);
		}

		return '';
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
		if ($this->isDaemon() === false)
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
		if ($this->isDaemon() === false)
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
		if ($this->isDaemon() === false)
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
		if ($this->isDaemon() === false)
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

		$pid = pcntl_fork();

		if ($pid === -1)
		{
			throw $this->getException('Unable to fork to start daemon');
		}

		$this->pid = $pid;

		if ($this->pid !== 0)
		{
			pcntl_signal(SIGCHLD, SIG_IGN); // Avoid zombie
		}
		else
		{
			$this->isDaemon = true;
			$this->pid = posix_getpid();

			if (posix_setsid() < 0)
			{
				throw $this->getException('Unable to become a session leader');
			}

			$pid = pcntl_fork();

			if ($pid === -1)
			{
				throw $this->getException('Unable to fork to start daemon');
			}

			$this->pid = $pid;

			if ($this->pid !== 0)
			{
				pcntl_signal(SIGCHLD, SIG_IGN); // Avoid zombie
			}
			else
			{
				set_error_handler(array($this, 'errorHandler'));
				set_exception_handler(array($this, 'exceptionHandler'));

				ob_start(array($this, 'outputHandler'));
				ob_implicit_flush(true);

				$this->pid = posix_getpid();

				try
				{
					$this->user->goToHome();
				}
				catch (\exception $exception)
				{
					throw $this->getException('Unable to set home directory to \'' . $this->getHome() . '\'');
				}

				if (posix_setgid($this->getGid()) === false)
				{
					throw $this->getException('Unable to set GID to \'' . $this->getGid() . '\'');
				}

				if (posix_setuid($this->getUid()) === false)
				{
					throw $this->getException('Unable to set UID to \'' . $this->getUid() . '\'');
				}

				umask(137);

				if (defined('STDIN') === true)
				{
					fclose(STDIN);
				}

				if (defined('STDOUT') === true)
				{
					fclose(STDOUT);
				}

				if (defined('STDERR') === true)
				{
					fclose(STDERR);
				}

				$this->controller[SIGTERM] = array($this->controller, 'stopDaemon');

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

				while (ob_get_level() > 0)
				{
					ob_end_flush();
				}
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
							throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
						}

						$script->setUid(reset($values));
					},
					array('-u', '--uid'),
					null,
					$this->locale->_('Define UID')
				)
		;

		return $this;
	}

	protected function getException($message)
	{
		return new daemon\exception($message);
	}
}
