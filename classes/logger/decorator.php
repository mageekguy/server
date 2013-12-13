<?php

namespace server\logger;

interface decorator
{
	public function prepareToDecorateLog();
	public function decorateLog($message);
}
