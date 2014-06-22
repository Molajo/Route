<?php

$class   = new \ReflectionClass('Molajo\Route\Driver');
$methods = $class->getMethods();
foreach ($methods as $method) {
    echo '     * @covers  ' . $method->class . '::' . $method->name . PHP_EOL;
}
die;
