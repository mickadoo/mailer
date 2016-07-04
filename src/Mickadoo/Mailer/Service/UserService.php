<?php

namespace Mickadoo\Mailer\Service;

use Doctrine\DBAL\Connection;

class UserService
{
    /**
     * @var Connection
     */
    protected $conn;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->conn = $connection;
    }

    /**
     * @param $uuid
     *
     * @return null|array
     */
    public function findByUuid($uuid)
    {
        $query = 'SELECT * FROM "user" WHERE uuid = :uuid';
        $result = $this->conn->fetchAll($query, ['uuid' => $uuid]);

        return empty($result) ? null : $result[0];
    }

    /**
     * @param $uuid
     * @param $email
     *
     * @throws \Exception
     */
    public function updateOrCreate($uuid, $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid email");
        }

        $result = $this->findByUuid($uuid);

        if (empty($result)) {
            $this->conn->insert('user', ['uuid' => $uuid, 'email' => $email]);
        } elseif ($result[0]['email'] !== $email) {
            $this->conn->update('user', ['email' => $email], ['uuid' => $uuid]);
        }
    }
}
