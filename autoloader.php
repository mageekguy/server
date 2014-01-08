<?php

return function($directories, $fileFormat = null, $namespaceAliases = null, $classAliases = null) {
	$fileFormat = $fileFormat ?: '%s.php';
	$namespaceAliases = $namespaceAliases ?: array();
	$classAliases = $classAliases ?: array();

	spl_autoload_register(function($class) use ($fileFormat, $directories, $namespaceAliases, $classAliases) {
			$realClass = (isset($classAliases[$class]) === false ? $class : $classAliases[$class]);

			foreach ($namespaceAliases as $alias => $namespace)
			{
				if ($realClass !== $alias && stripos($realClass, $alias) === 0)
				{
					$realClass = $namespace . substr($class, strlen($alias));

					break;
				}
			}

			if ($realClass !== $class && class_exists($realClass, false) === true)
			{
				class_alias($realClass, $class);
			}
			else
			{
				foreach ($directories as $namespace => $directory)
				{
					if ($realClass !== $namespace && stripos($realClass, $namespace) === 0)
					{
						@include($directory . str_replace('\\', DIRECTORY_SEPARATOR, substr($realClass, strlen($namespace))) . '.php');

						if (class_exists($realClass, false) === true && $realClass !== $class)
						{
							class_alias($realClass, $class);
						}

						break;
					}
				}
			}
		}
	);
};
