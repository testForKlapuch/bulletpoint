<?php
namespace Bulletpoint\Model\Access;

use Bulletpoint\Model\Storage;

final class LimitedForgottenPasswords implements ForgottenPasswords {
    private $origin;
    private $database;
    // 3 attempts in last 24 hours
    const ATTEMPT_LIMIT = 3;
    const HOUR_LIMIT = 24;

    public function __construct(
        ForgottenPasswords $origin,
        Storage\Database $database
    ) {
        $this->origin = $origin;
        $this->database = $database;
    }

    public function remind(string $email): RemindedPassword {
        if($this->overstepped($email)) {
            throw new \OverflowException(
                sprintf(
                    'Byl překročen limit %d zapomenutých hesel během %d hodin',
                    self::ATTEMPT_LIMIT,
                    self::HOUR_LIMIT
                )
            );
        }
        return $this->origin->remind($email);
    }

    private function overstepped(string $email): bool {
        return (bool)$this->database->fetchColumn(
            'SELECT 1
			FROM forgotten_passwords
			WHERE user_id = (SELECT ID FROM users WHERE email = ?)
			AND reminded_at > NOW() - INTERVAL ? HOUR
			HAVING COUNT(ID) >= ?',
            [$email, self::HOUR_LIMIT, self::ATTEMPT_LIMIT]
        );
    }
}