<?php

require_once __DIR__ . '/../bootstrap.php';

$server = new \server\scripts\server(__FILE__);

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
	$server->writeError($exception->getMessage());

	exit($exception->getCode());
}

exit(0);
