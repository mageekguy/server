<?php

namespace server\demo\scripts\server;

use
	server\network\ip,
	server\network\port,
	server\socket,
	server\socket\timer,
	server\daemon\payloads\server,
	server\daemon\payloads\server\endpoint,
	server\daemon\payloads\server\client,
	server\daemon\payloads\server\client\message
;

class payload extends server
{
	protected $clientsEndpoint = null;

	public function __construct()
	{
		parent::__construct();

		$this->clientsEndpoint = (new endpoint(new ip('192.168.0.1'), new port(8080)))->onConnect(array($this, 'acceptClient'));

		$this->addEndpoint($this->clientsEndpoint);
	}

	public function setClientsIp(ip $ip)
	{
		$this->clientsEndpoint->setIp($ip);

		return $this;
	}

	public function getClientsIp()
	{
		return $this->clientsEndpoint->getIp();
	}

	public function setClientsPort(port $port)
	{
		$this->clientsEndpoint->setPort($port);

		return $this;
	}

	public function getClientsPort()
	{
		return $this->clientsEndpoint->getPort();
	}

	public function acceptClient($clientsSocket)
	{
		$this->pollSocket($clientsSocket)->onReadNotBlock(array($this, __FUNCTION__));

		$client = new client(new socket($this->acceptSocket($clientsSocket), $this), $this);

		$client
			->onTimeout(new timer(60), function($client) {
					$this->writeInfo('Client ' . $client . ' timeout!');

					$client->closeSocket();
				}
			)
			->writeMessage(new message('Hello, type something to get its rot13 version, type :quit to close the connection.' . "\r\n"))
			->readMessage((new message())->onRead(function($message) use ($client) {
						$this->writeInfo('Receive \'' . trim($message) . '\' from peer ' . $client);

						if (trim($message) == ':quit') $client->writeMessage(
							$message('Bye!' . "\r\n")
								->onWrite(function($message) use ($client) {
										$this->writeInfo('Close connection with peer ' . $client);

										$client->closeSocket();
									}
								)
						);

						else $client->writeMessage(
							$message('Rot13: ' . str_rot13($message))
								->onWrite(function($message) use ($client) {
										$this->writeInfo('Sent \'' . trim($message) . '\' to peer ' . $client);

										$client->readMessage($message);
									}
								)
						);
					}
				)
			)
		;

		return $this->writeInfo('Accept peer ' . $client);
	}
}
