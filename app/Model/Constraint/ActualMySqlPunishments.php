<?php
namespace Bulletpoint\Model\Constraint;

use Bulletpoint\Model\{
    Storage, Access
};
use Bulletpoint\Exception;

final class ActualMySqlPunishments implements Punishments {
    private $myself;
    private $database;

    public function __construct(
        Access\Identity $myself,
        Storage\Database $database
    ) {
        $this->myself = $myself;
        $this->database = $database;
    }
    public function iterate(): \Iterator {
        $rows = $this->database->fetchAll(
            'SELECT ID, reason, expiration, sinner_id
			FROM punishments
			WHERE forgiven = 0 AND NOW() < expiration
			ORDER BY expiration ASC'
        );
        foreach($rows as $row) {
            yield new ConstantPunishment(
                new Access\MySqlIdentity($row['sinner_id'], $this->database),
                $row['reason'],
                new \DateTime($row['expiration']),
                new MySqlPunishment($row['ID'], $this->database)
            );
        }
    }

    public function punish(
        Access\Identity $sinner,
        \DateTime $expiration,
        string $reason
    ) {
        if($this->past($expiration)) {
            throw new \LogicException(
                'Trest smí být udělen pouze na budoucí období'
            );
        }
        $this->database->query(
            'INSERT INTO punishments (sinner_id, reason, expiration, author_id)
			VALUES (?, ?, ?, ?)',
            [
                $sinner->id(),
                $reason,
                current($expiration),
                $this->myself->id(),
            ]
        );
    }

    private function past(\DateTime $expiration): bool {
        return $expiration < new \DateTime;
    }
}