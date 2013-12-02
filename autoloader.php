<?php

namespace mehen;

use atoum;

require_once __DIR__ . '/tests/units/atoum/classes/autoloader.php';

atoum\autoloader::get()->addDirectory(__NAMESPACE__, __DIR__ . '/classes');
