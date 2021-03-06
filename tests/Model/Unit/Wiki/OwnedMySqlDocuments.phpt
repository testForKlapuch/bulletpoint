<?php

/**
* @testCase
* @phpVersion > 7.0.0
*/

namespace Bulletpoint\Model\Unit;

use Tester\Assert;
use Bulletpoint\Model\Wiki;
use Bulletpoint\TestCase;
use Bulletpoint\Fake;
use Bulletpoint\Model\Access;

require __DIR__ . '/../../../bootstrap.php';

final class OwnedMySqlDocuments extends TestCase\Database {
	public function testIterating() {
		$connection = $this->preparedDatabaseForIterating();
        $owner = new Fake\Identity(2);
		$documents = new Wiki\OwnedMySqlDocuments($owner, $connection);
        $rows = $documents->iterate();
		Assert::equal(
			new Wiki\ConstantDocument(
				'firstTitle',
				'first',
				$owner,
				new \DateTimeImmutable('1999-01-01 01:01:01'),
                new Wiki\MySqlInformationSource(1, $connection),
				new Wiki\MySqlDocument(1, $connection)
			),
			$rows->current()
		);
		$rows->next();
		Assert::false($rows->valid());
        Assert::same(1, $documents->count());
	}

	public function testAdding() {
		$connection = $this->connection();
		$connection->query('TRUNCATE documents');
		$document = (new Wiki\OwnedMySqlDocuments(
            new Fake\Identity(4),
            $connection
        ))->add(
			'new title...',
			'new description...',
			new Fake\InformationSource(2, 'some_place', 2005, 'facedown')
		);
        Assert::equal(new Wiki\MySqlDocument(1, $connection), $document);
		Assert::same(
			[
				'user_id' => 4,
				'information_source_id' => 2,
				'ID' => 1,
				'title' => 'new title...',
			],
			$connection->fetch(
				'SELECT user_id, information_source_id, ID, title
				FROM documents
				WHERE description = "new description..."'
			)
		);
	}

	/**
	* @throws \Bulletpoint\Exception\DuplicateException Titulek již existuje
	*/
	public function testAddingExistingOne() {
		$connection = $this->connection();
		$connection->query('TRUNCATE documents');
		$connection->query(
			'INSERT INTO documents
			(ID, user_id, created_at, description, information_source_id, title)
			VALUES
			(1, 2, "1999-01-01 01:01:01", "first", 1, "firstTitle")'
		);
		(new Wiki\OwnedMySqlDocuments(
			new Fake\Identity(4),
			$this->connection()
		))->add(
			'firstTitle',
			'new description...',
			new Fake\InformationSource(2, 'some_place', 2005, 'facedown')
		);
	}

	private function preparedDatabaseForIterating() {
		$connection = $this->connection();
		$connection->query('TRUNCATE documents');
		$connection->query(
			'INSERT INTO documents
			(ID, user_id, created_at, description, information_source_id, title)
			VALUES
			(2, 1, "2000-01-01 01:01:01", "second", 1, "secondTitle"),
			(1, 2, "1999-01-01 01:01:01", "first", 1, "firstTitle")'
		);
		return $connection;
	}
}


(new OwnedMySqlDocuments())->run();
