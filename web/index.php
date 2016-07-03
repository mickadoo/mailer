<?php

require_once __DIR__ . '/../bootstrap.php';

use Symfony\Component\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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