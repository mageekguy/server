<?php

namespace server;

use
	server\network\ip,
	server\network\port
;

$script
	->setClientsIp(new ip('127.0.0.1')) // Put IP where clients should connect here
	->setClientsPort(new port(8080)) // Put port where clients should connect here
	->setUid('nobody') // Put user which should own the server processus here
;

$trimDecorator = new decorators\trim();
$datePrefixDecorator = new decorators\prefix('|');
$messagePrefixDecorator = new decorators\prefix(': ');
$dateDecorator = new decorators\date();
$eolDecorator = new decorators\eol();
$idDecorator = new decorators\id();

$script
	->getOutputLogger()
		->addWriter(new writers\file(__DIR__ . '/output.log'))
		->addDecorator($trimDecorator)
		->addDecorator($messagePrefixDecorator)
		->addDecorator($dateDecorator)
		->addDecorator($datePrefixDecorator)
		->addDecorator($idDecorator)
		->addDecorator($eolDecorator)
; // Output log message will be '52aacd5d033252.24168358|2013-12-03 21:03:28: The output here'

$script
	->getInfoLogger()
		->addWriter(new writers\file(__DIR__ . '/info.log'))
		->addDecorator($trimDecorator)
		->addDecorator($messagePrefixDecorator)
		->addDecorator($dateDecorator)
		->addDecorator($datePrefixDecorator)
		->addDecorator($idDecorator)
		->addDecorator($eolDecorator)
; // Info log message will be '52aacd5d033252.24168358|2013-12-03 21:03:28: The info log message here'

$script
	->getErrorLogger()
		->addWriter(new writers\file(__DIR__ . '/error.log'))
		->addDecorator($trimDecorator)
		->addDecorator($messagePrefixDecorator)
		->addDecorator($dateDecorator)
		->addDecorator($datePrefixDecorator)
		->addDecorator($idDecorator)
		->addDecorator($eolDecorator)
; // Error log message will be '52aacd5d033252.24168358|2013-12-03 21:03:28: The error log message here'
