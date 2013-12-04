<?php

namespace server\demo;

require_once __DIR__ . '/../bootstrap.php';

$server = new scripts\server(__FILE__);

set_error_handler(function($error, $message, $file, $line) use ($server) {
		if (error_reporting() !== 0)
		{
			$server->writeError($message);

			exit($error);
		}
	}
);

try
{
	$server->run();
}
catch (\exception $exception)
{
	if ($server->isDaemon() === true)
	{
		throw $exception;
	}
	else
	{
		$server->writeError($exception->getMessage());

		exit($exception->getCode());
	}
}

exit(0);
