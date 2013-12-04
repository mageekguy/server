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
	->setHome('/tmp') // Put the home directory of the server processus here
;

$trimDecorator = new decorators\trim();
$prefixDecorator = new decorators\prefix(': ');
$dateDecorator = new decorators\date();
$eolDecorator = new decorators\eol();

$script->setOutputLogger($outputLogger = new logger());
$outputLogger
	->addWriter(new writers\file(__DIR__ . '/output.log'))
	->addDecorator($trimDecorator)
	->addDecorator($prefixDecorator)
	->addDecorator($dateDecorator)
	->addDecorator($eolDecorator)
; // Output log message will be '2013-12-03 21:03:28: The output here'

$script->setInfoLogger($infoLogger = new logger());
$infoLogger
	->addWriter(new writers\file(__DIR__ . '/info.log'))
	->addDecorator($trimDecorator)
	->addDecorator($prefixDecorator)
	->addDecorator($dateDecorator)
	->addDecorator($eolDecorator)
; // Info log message will be '2013-12-03 21:03:28: The info log message here'

$script->setErrorLogger($errorLogger = new logger());
$errorLogger
	->addWriter(new writers\file(__DIR__ . '/error.log'))
	->addDecorator($trimDecorator)
	->addDecorator($prefixDecorator)
	->addDecorator($dateDecorator)
	->addDecorator($eolDecorator)
; // Error log message will be '2013-12-03 21:03:28: The error log message here'
