<?php

require_once __DIR__.'/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

function getValueOrNull(array $array, $key)
{
    return $array[$key] ?? null;
}

try {
    $connection = new AMQPStreamConnection('rabbit', 5672, 'guest', 'guest');
} catch (ErrorException $exception) {
    throw new \Exception('Failed to connected to rabbitMQ');
}

$channel = $connection->channel();
$channel->queue_declare('yarnyard', false, false, false, true);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$sendMailCallback = function ($msg) {

    $body = json_decode($msg->body, true);
    $recipient = getValueOrNull($body, 'recipient');
    $type = getValueOrNull($body, 'type');
    $data = getValueOrNull($body, 'data');
    $command = sprintf(
        "php %s %s %s '%s'",
        __DIR__.'/send.php',
        $recipient,
        $type,
        json_encode($data)
    );

    exec($command, $output, $returnCode);

    if (!$returnCode) {
        echo 'sent a mail to '.$recipient;
    } else {
        echo 'error sending mail'.PHP_EOL.$output;
    }
};

$mailChangedCallback = function ($msg) {
    $body = json_decode($msg->body, true);
    $event = $body['event'];
    $data = $body['data'];

    echo json_encode($data) . PHP_EOL . PHP_EOL;

    if (!isset($data['user'])) {
        return;
    }

    $userData = $data['user'];

    $uuid = getValueOrNull($userData, '_id');
    $email = getValueOrNull($userData, 'email');

    $command = sprintf(
        "php %s %s %s",
        __DIR__.'/update-user.php',
        $uuid,
        $email
    );

    echo 'running ' . $command;

    exec($command, $output, $returnCode);

    if (!$returnCode) {
        echo 'added / updated a user';
    } else {
        echo 'error adding / updating user';
    }
};

$routingCallback = function ($msg) use (
    $sendMailCallback,
    $mailChangedCallback
) {

    $handledTypes = [
        'email_confirmation',
    ];

    $body = json_decode($msg->body, true);
    $type = getValueOrNull($body, 'type');

    if ($type && in_array($type, $handledTypes)) {
        echo 'sending a mail';
        $sendMailCallback($msg);
    } else {
        echo 'updating a user';
        $mailChangedCallback($msg);
    }
};

$channel->basic_consume(
    'yarnyard',
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
