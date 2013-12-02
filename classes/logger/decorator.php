<?php

namespace server\logger;

interface decorator
{
	public function decorateLog($message);
}
