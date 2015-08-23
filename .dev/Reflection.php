<?php

$class   = new \ReflectionClass('Molajo\Route\Controller');
$methods = $class->getMethods();
foreach ($methods as $method) {
    echo '     * @covers  ' . $method->class . '::' . $method->name . PHP_EOL;
}
die;
