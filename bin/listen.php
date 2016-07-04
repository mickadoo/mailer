<?php

require_once __DIR__.'/../bootstrap.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$host = 'rabbit';
$port = 5672;
$username = 'guest';
$password = 'guest';
$queueName = 'yarnyard';

function getValueOrNull(array $array, $key)
{
    return $array[$key] ?? null;
}

try {
    $connection = new AMQPStreamConnection($host, $port, $username, $password);
} catch (ErrorException $exception) {
    throw new \Exception('Failed to connected to rabbitMQ');
}

$channel = $connection->channel();
$channel->queue_declare($queueName, false, false, false, true);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$routingCallback = function ($message) use ($app) {
    $body = json_decode($message->body, true);
    $handler = $app->getHandlerFactory()->getHandlerForMessage($body);
    echo 'using ' . get_class($handler) . ' for message' . PHP_EOL;
    $handler->handle($body);
};

$channel->basic_consume(
    $queueName,
    '',
    false,
    true,
    false,
    false,
    $routingCallback
);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
