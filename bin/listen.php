<?php

require_once __DIR__.'/../bootstrap.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

function getEnvVariable($name) {
    if ($variable = getenv($name)) {
        return $variable;
    }

    return null;
}

$host = getEnvVariable("RABBITMQ_HOST") ?? 'localhost';
$port = getEnvVariable("RABBITMQ_PORT") ?? 5672;
$username = getEnvVariable("RABBITMQ_USER") ?? 'guest';
$password = getEnvVariable("RABBITMQ_PASS") ?? 'guest';
$queueName = getEnvVariable("RABBITMQ_QUEUE") ?? 'yarnyard';

var_dump($host, $port);

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
