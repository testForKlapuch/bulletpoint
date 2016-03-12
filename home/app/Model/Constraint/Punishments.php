<?php
namespace Bulletpoint\Model\Constraint;

use Bulletpoint\Core\Storage;
use Bulletpoint\Model\Access;

abstract class Punishments {
    protected $myself;
    protected $database;

    public function __construct(
        Access\Identity $myself,
        Storage\Database $database
    ) {
        $this->myself = $myself;
        $this->database = $database;
    }

    public abstract function iterate(): \Iterator;

    public function punish(
        Access\Identity $sinner,
        \DateTime $expiration,
        string $reason
    ) {
        $this->database->query(
            'INSERT INTO banned_users (user_id, reason, expiration, author_id)
			VALUES (?, ?, ?, ?)',
            [
                $sinner->id(),
                $reason,
                current($expiration),
                $this->myself->id(),
            ]
        );
    }

    protected function iterateBy(
        string $where,
        array $parameters = []
    ): \Iterator {
        $rows = $this->database->fetchAll(
            "SELECT banned_users.ID,
			reason,
			expiration,
			user_id,
			users.role,
			users.username
			FROM banned_users
			INNER JOIN users
			ON users.ID = banned_users.user_id
			WHERE $where
			ORDER BY canceled ASC, expiration DESC",
            $parameters
        );
        foreach($rows as $row) {
            yield new ConstantPunishment(
                new Access\ConstantIdentity(
                    $row['user_id'],
                    new Access\ConstantRole(
                        $row['role'],
                        new Access\MySqlRole($row['user_id'], $this->database)
                    ),
                    $row['username']
                ),
                $row['reason'],
                new \DateTime($row['expiration']),
                new MySqlPunishment($row['ID'], $this->database)
            );
        }
    }
}