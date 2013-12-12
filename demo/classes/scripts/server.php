<?php

namespace server\demo\scripts;

use
	atoum,
	server\network\ip,
	server\network\port
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
							throw new \runtimeException(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
						}

						try
						{
							$ip = new ip(reset($values));
						}
						catch (ip\exception $exception)
						{
							throw new \runtimeException(sprintf($script->getLocale()->_('IP \'%s\' is invalid'), reset($values)));
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
							throw new \runtimeException(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
						}

						try
						{
							$port = new port(reset($values));
						}
						catch (port\exception $exception)
						{
							throw new \runtimeException(sprintf($script->getLocale()->_('Port \'%s\' is invalid'), reset($values)));
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
