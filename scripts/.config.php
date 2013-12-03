<?php

namespace server;

use
	server\network\ip,
	server\network\port
;

$script
	->setTrackersIp(new ip('192.168.26.32'))
	->setTrackersPort(new port(8080))
	->setUid('nobody')
	->setHome('/tmp')
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
;

$script->setInfoLogger($infoLogger = new logger());
$infoLogger
	->addWriter(new writers\file(__DIR__ . '/info.log'))
	->addDecorator($trimDecorator)
	->addDecorator($prefixDecorator)
	->addDecorator($dateDecorator)
	->addDecorator($eolDecorator)
;

$script->setErrorLogger($errorLogger = new logger());
$errorLogger
	->addWriter(new writers\file(__DIR__ . '/error.log'))
	->addDecorator($trimDecorator)
	->addDecorator($prefixDecorator)
	->addDecorator($dateDecorator)
	->addDecorator($eolDecorator)
;
