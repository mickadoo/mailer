<?php

namespace Mickadoo\Mailer\Service;

use Doctrine\DBAL\Connection;

class UserFinder
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param $uuid
     *
     * @return null|array
     */
    public function findByUuid($uuid)
    {
        $query = 'SELECT * FROM "user" WHERE uuid = :uuid';
        $result = $this->connection->fetchAll($query, ['uuid' => $uuid]);

        return empty($result) ? null : $result[0];
    }
}
