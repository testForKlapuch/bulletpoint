<?php
namespace Bulletpoint\Model\Constraint;

use Bulletpoint\Exception;
use Bulletpoint\Core\Storage;

final class UsernameNotExistenceRule implements Rule {
	private $database;

	public function __construct(Storage\Database $database) {
		$this->database = $database;
	}

	public function isSatisfied($input) {
		if($this->exists($input))
			throw new Exception\ExistenceException('Přezdívka již existuje');
	}

	private function exists($input): bool {
		return (bool)$this->database->fetch(
			'SELECT 1 FROM users WHERE username = ?',
			[$input]
		);
	}
}