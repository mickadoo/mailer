<?php

require_once __DIR__ . '/../bootstrap.php';

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;

$db = $app->getDb();
$schema = $db->getSchemaManager();

//$x = $schema->listDatabases();
$y = $schema->listTableNames();

$tableName = 'user';

if (in_array($tableName, $schema->listTableNames())){
    throw new \Exception("table already exists");
}

$table = new Table(
    $tableName,
    [
        new Column('uuid', Type::getType(Type::STRING)),
        new Column('email', Type::getType(Type::STRING)),
    ]
);

$schema->createTable($table);

return 0;
