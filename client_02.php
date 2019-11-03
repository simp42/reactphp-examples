<?php declare(strict_types=1);

use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Factory;

require 'vendor/autoload.php';

// create reactphp loop
$loop = Factory::create();
// create react-buzz http client
$browser = new Browser($loop);

$now = microtime(true);

$requests = [];
for ($i = 0; $i < 100; $i++) {
    $requests[] = $browser
        ->get('http://localhost:8080/')
        ->then(
            // Response handling
            function (ResponseInterface $response) {
                $responseContent = $response->getBody()->getContents();
                $responseData = json_decode($responseContent, true);

                echo 'Request: ' . $responseData['count'] . PHP_EOL;
            },
            // Error handling:
            function (Exception $e) {
                echo $e->getMessage() . PHP_EOL;
            }
        );
}

echo (microtime(true) - $now) . PHP_EOL;

$loop->run();

echo (microtime(true) - $now) . PHP_EOL;