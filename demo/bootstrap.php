<?php

namespace server\demo;

require_once __DIR__ . '/../autoloader.php';

\atoum\autoloader::get()->addDirectory(__NAMESPACE__, __DIR__ . '/classes');
