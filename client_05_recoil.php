<?php declare(strict_types=1);

use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Factory;
use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use Recoil\React\ReactKernel;

require 'vendor/autoload.php';

// create reactphp loop
$loop = Factory::create();
// create recoil kernel
$kernel = ReactKernel::create($loop);
// create react-buzz http client
$browser = new Browser($loop);
// create async mysql client
$mysqlFactory = new \React\MySQL\Factory($loop);

$now = microtime(true);

function requestData(Browser $browser, ConnectionInterface $connection): Generator
{
    /** @var ResponseInterface $response */
    $response = yield $browser->get('http://localhost:8080/');

    $responseContent = $response->getBody()->getContents();
    $responseData = json_decode($responseContent, true);
    echo 'Request: ' . $responseData['count'] . PHP_EOL;

    /** @var QueryResult $inserted */
    $result = yield $connection->query(
        'INSERT INTO test(content) VALUES(?)',
        [$responseContent]
    );

    echo $responseData['count'] . ' inserted with id ' . $result->insertId . PHP_EOL;
}

$kernel->execute(function () use ($browser, $mysqlFactory) {

    // yield single promise, waits until it is resolved
    /** @var ConnectionInterface $connection */
    $connection = yield $mysqlFactory->createConnection('root@localhost:3306/test');

    $requests = [];
    for ($i = 0; $i < 100; $i++) {
        $requests[] = requestData($browser, $connection);
    }

    // Yield array of promises -> are executed in "parallel"
    yield $requests;

    // Yield int, sleeps for given number of seconds
    // yield 5;

    yield $connection->quit();
});

$loop->run();

echo (microtime(true) - $now) . PHP_EOL;