<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Debug\ErrorHandler;
use Mickadoo\Application\Application;
use Mickadoo\Mailer\Exception\MailerException;

ErrorHandler::register();

$app = new Application();
$app['root_directory'] = realpath(__DIR__);
$app->setUp();

/**
 * @param $recipient
 * @param $type
 * @throws MailerException
 */
function checkRequirements($recipient, $type)
{
    if (!isset($recipient)) {
        throw new MailerException("At least you gotta say who you wanna send it to (recipient)");
    }

    if (!isset($type)) {
        throw new MailerException("You need to say what kind of mail to send (type)");
    }
}