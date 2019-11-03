<?php declare(strict_types=1);

use React\EventLoop\TimerInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Server;

require 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$server = new Server('tcp://127.0.0.1:8080', $loop);

$clients = 0;

$server->on('connection',
    function (ConnectionInterface $connection) use ($loop, &$clients) {
        $clients++;

        $connection->on('data', function ($data) use($clients) {
            echo $data;
            echo PHP_EOL;
        });

        $loop->addTimer(
            5,
            function (TimerInterface $timer) use ($connection, $clients) {
                $connection->write("HTTP/1.1 200 OK\r\n");
                $connection->write("Content-Type: application/json\r\n");
                $connection->write("Connection: Close\r\n");
                $connection->write("\r\n");

                $connection->end("{\"count\": $clients}\r\n");
            }
        );
    }
);

$loop->run();