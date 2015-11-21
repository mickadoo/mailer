<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Debug;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\HttpFoundation\Request;
use Mickadoo\Application\Application;
use Symfony\Component\HttpFoundation\Response;
use Mickadoo\Mailer\Exception\MailerException;

ErrorHandler::register();

$app = new Application();
$app['root_directory'] = realpath(__DIR__ . '/../');
$app->setUp();

$app->post('/mail', function (Request $request) use ($app) {

    $recipient = $request->get('recipient');
    $type = $request->get('type');
    $data = $request->get('data', []);
    $locale = $request->get('locale', $app['config']['translator']['fallback_locale']);

    checkRequirements($recipient, $type);

    $body = $app->getMailContentGenerator()->getBody($type, $data, $locale);
    $subject = $app->getMailContentGenerator()->getSubject($type, $data, $locale);

    $app->getMailer()->send($recipient, $subject, $body);

    return new Response(null, 204);
});

$app->run();

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