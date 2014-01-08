<?php

call_user_func_array(include __DIR__ . '/autoloader/autoloader.php',
	[
		[
			'server' =>  __DIR__ . '/classes',
			'mageekguy\atoum' => __DIR__ . '/tests/units/atoum/classes'
		],
		null,
		[
			'atoum' => 'mageekguy\atoum'
		]
	]
);
