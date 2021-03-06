<?php
namespace Bulletpoint\Fake;

use Bulletpoint\Model\Access;

final class Identity implements Access\Identity {
	private $id;
	private $role;
	private $username;

	public function __construct(
		int $id = null,
		Access\Role $role = null,
		string $username = null
	) {
		$this->id = $id;
		$this->role = $role;
		$this->username = $username;
	}

	public function id(): int {
		return $this->id;
	}

	public function role(): Access\Role {
		return $this->role ?: new Role('guest', 1);
	}

	public function username(): string {
		return (string)$this->username;
	}
}