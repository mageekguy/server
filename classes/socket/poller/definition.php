<?php

namespace server\socket\poller;

interface definition
{
	public function pollSocket($socket);
	public function waitSockets();
}
