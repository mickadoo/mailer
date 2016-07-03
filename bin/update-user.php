<?php

include_once __DIR__ . '/../bootstrap.php';

list ($scriptName, $uuid, $email) = $argv;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new \Exception("Invalid email");
}

$db = $app->getDb();

$query = 'SELECT * FROM "user" WHERE uuid = :uuid';
$result = $db->fetchAll($query, ['uuid' => $uuid]);

if (empty($result)) {
    $db->insert('user', ['uuid' => $uuid, 'email' => $email]);
} elseif ($result[0]['email'] !== $email) {
    $db->update('user', ['email' => $email], ['uuid' => $uuid]);
}

return 0;
