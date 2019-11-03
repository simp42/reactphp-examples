<?php declare(strict_types=1);

use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Factory;

require 'vendor/autoload.php';

// create reactphp loop
$loop = Factory::create();
// create react-buzz http client
$browser = new Browser($loop);
// create async mysql client
$mysqlFactory = new \React\MySQL\Factory($loop);

$now = microtime(true);
$connection = $mysqlFactory->createLazyConnection('root@localhost:3306/test');

$requests = [];
for ($i = 0; $i < 100; $i++) {
    $requests[] = $browser
        ->get('http://localhost:8080/')
        ->then(
            function (ResponseInterface $response) use ($connection) {
                $responseContent = $response->getBody()->getContents();

                $responseData = json_decode($responseContent, true);

                echo 'Request: ' . $responseData['count'] . PHP_EOL;

                return $connection->query(
                    'INSERT INTO test(content) VALUES(?)',
                    [$responseContent]
                )->then(
                    function (\React\MySQL\QueryResult $result) use ($responseData) {
                        echo $responseData['count'] . ' inserted with id ' . $result->insertId . PHP_EOL;
                    },
                    function (Exception $e) {
                        echo 'DB error:' . $e->getMessage() . PHP_EOL;
                    }
                );
            }
        );
}

echo (microtime(true) - $now) . PHP_EOL;

// hangs...
$loop->run();

echo (microtime(true) - $now) . PHP_EOL;