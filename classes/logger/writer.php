<?php

namespace server\logger;

interface writer
{
	public function log($message);
}
