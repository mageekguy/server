<?php

namespace server\demo\scripts;

use
	atoum,
	server\network
;

class server extends \server\daemon
{
	public function __construct($name, atoum\adapter $adapter = null)
	{
		parent::__construct($name, $adapter);

		$this->setPayload(new server\payload());
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

						try
						{
							$ip = new network\ip(reset($values));
						}
						catch (network\ip\exception $exception)
						{
							throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('IP \'%s\' is invalid'), reset($values)));
						}

						$script->setClientsIp($ip);
					},
					array('-ci', '--clients-ip'),
					null,
					$this->locale->_('Define clients IP')
				)
			->addArgumentHandler(
					function($script, $argument, $values) {
						if (sizeof($values) !== 1)
						{
							throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
						}

						try
						{
							$port = new network\port(reset($values));
						}
						catch (network\ip\exception $exception)
						{
							throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Port \'%s\' is invalid'), reset($values)));
						}

						$script->setClientsPort($port);
					},
					array('-cp', '--clients-port'),
					null,
					$this->locale->_('Define clients port')
				)
		;

		return $this;
	}
}
