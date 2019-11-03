<?php declare(strict_types=1);

function generator()
{
    $i = 0;
    while ($i < 100) {
        yield $i++;
    }
}

foreach(generator() as $value) {
    echo $value . PHP_EOL;
}