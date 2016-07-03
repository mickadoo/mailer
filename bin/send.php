<?php

include_once __DIR__ . '/../bootstrap.php';

list ($scriptName, $recipient, $type, $data) = $argv;

$data = $data ? json_decode($data, true) : [];

checkRequirements($recipient, $type);

// todo extract locale
$locale = 'en_US';

$body = $app->getMailContentGenerator()->getBody($type, $data, $locale);
$subject = $app->getMailContentGenerator()->getSubject($type, $data, $locale);

$app->getMailer()->send($recipient, $subject, $body);

exit(0);
