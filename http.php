<?php declare(strict_types=1);

require 'vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$browser = (new \Clue\React\Buzz\Browser($loop));
$mysqlFactory = new \React\MySQL\Factory($loop);

$connection = $mysqlFactory->createLazyConnection('root@localhost:3306/test');

$now = microtime(true);
$requests = [];

for ($i = 0; $i < 100; $i++) {
    $requests[] = $browser
        ->get('http://localhost:8080/')
        ->then(function (\Psr\Http\Message\ResponseInterface $response) use ($connection, &$requests) {
            $responseContent = $response->getBody()->getContents();
            $responseData = json_decode($responseContent, true);

            echo 'Request: ' . $responseData['count'] . PHP_EOL;

            return $connection->query(
                "INSERT INTO test(content) VALUES(?)",
                [$responseContent]
            )->then(function () {
                echo 'inserted' . PHP_EOL;
            }, function (Exception $error) {
                echo $error->getMessage() . PHP_EOL;
            });
        });
}

echo (microtime(true) - $now) . PHP_EOL;
\Clue\React\Block\awaitAll($requests, $loop);

echo (microtime(true) - $now) . PHP_EOL;