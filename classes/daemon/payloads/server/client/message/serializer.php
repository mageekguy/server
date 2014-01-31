<?php

namespace server\daemon\payloads\server\client\message;

use
	server\daemon\payloads\server\client\message,
	server\daemon\payloads\server\client\message\serializer
;

interface serializer
{
	public function __set($property, $value);
	public function __get($property);
	public function serializeMessage();
	public function unserializeMessage($data);
}
