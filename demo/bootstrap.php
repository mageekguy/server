<?php

call_user_func_array(include(__DIR__ . '/../autoloader/autoloader.php'), [ [ 'server\demo' =>  __DIR__ . '/classes' ] ]);

require_once __DIR__ . '/../bootstrap.php';
