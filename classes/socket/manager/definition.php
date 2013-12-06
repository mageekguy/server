<?php

namespace server\socket\manager;

use
	server\network
;

interface definition
{
	public function getLastSocketErrorCode();
	public function getLastSocketErrorMessage();
	public function getSocketPeer($socket);
	public function bindSocketTo(network\ip $ip, network\port $port);
	public function acceptSocket($serverSocket);
	public function readSocket($socket, $length, $mode);
	public function writeSocket($socket, $data);
	public function pollSockets(array & $read, array & $write, array & $except, $timeout);
	public function closeSocket($socket);
}
