<?php
/**
 * Bootstrap for Testing
 *
 * @package    Molajo
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */
$base = substr(__DIR__, 0, strlen(__DIR__) - 5);
if (function_exists('CreateClassMap')) {
} else {
    include_once __DIR__ . '/CreateClassMap.php';
}
include_once $base . '/vendor/autoload.php';

$classmap                              = array();
$results                               = createClassMap($base . '/Source/Adapter', 'Molajo\\Route\\Controller\\');
$classmap                              = array_merge($classmap, $results);
$results                               = createClassMap($base . '/.dev/Mocks', 'Molajo\\Query\\');
$classmap                              = array_merge($classmap, $results);
$classmap['Molajo\\Route\\Controller'] = $base . '/Source/Driver.php';

spl_autoload_register(
    function ($class) use ($classmap) {
        if (array_key_exists($class, $classmap)) {
            require_once $classmap[$class];
        }
    }
);
