<?php declare(strict_types=1);

require 'vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$kernel = \Recoil\React\ReactKernel::create($loop);

function printer(string $value): Generator
{
    echo $value;
    yield;

    sleep(1);
    echo PHP_EOL;
    yield;
}

$kernel->execute(function () {
    yield [
        printer('Hello'),
        printer('World')
    ];
});

$loop->run();