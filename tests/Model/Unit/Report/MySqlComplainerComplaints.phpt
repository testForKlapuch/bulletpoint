<?php

/**
 * @testCase
 * @phpVersion > 7.0.0
 */

namespace Bulletpoint\Model\Unit;

use Tester\Assert;
use Bulletpoint\Model\Report;
use Bulletpoint\TestCase;
use Bulletpoint\Fake;
use Bulletpoint\Model\Access;

require __DIR__ . '/../../../bootstrap.php';

final class MySqlComplainerComplaints extends TestCase\Database {
    public function testIteratingWithTarget() {
        $connection = $this->preparedDatabase();
        $myself = new Fake\Identity(7);
        $complaints = (new Report\MySqlComplainerComplaints(
            $myself,
            $connection,
            new Fake\Complaints()
        ))->iterate(new Report\Target(1));
        Assert::equal(
            new Report\ConstantComplaint(
                new Access\ConstantIdentity(
                    7,
                    new Access\ConstantRole(
                        'member',
                        new Access\MySqlRole(7, $connection)
                    ),
                    'face'
                ),
                new Report\Target(1),
                'Vulgarita',
                new Report\MySqlComplaint(1, $myself, $connection)
            ),
            $complaints->current()
        );
        $complaints->next();
        Assert::false($complaints->valid());
    }

    public function testIteratingWithoutTarget() {
        $connection = $this->preparedDatabase();
        $myself = new Fake\Identity(7);
        $complaints = (new Report\MySqlComplainerComplaints(
            $myself,
            $connection,
            new Fake\Complaints()
        ))->iterate();
        Assert::equal(
            new Report\ConstantComplaint(
                new Access\ConstantIdentity(
                    7,
                    new Access\ConstantRole(
                        'member',
                        new Access\MySqlRole(7, $connection)
                    ),
                    'face'
                ),
                new Report\Target(1),
                'Vulgarita',
                new Report\MySqlComplaint(1, $myself, $connection)
            ),
            $complaints->current()
        );
        $complaints->next();
        Assert::equal(
            new Report\ConstantComplaint(
                new Access\ConstantIdentity(
                    7,
                    new Access\ConstantRole(
                        'member',
                        new Access\MySqlRole(7, $connection)
                    ),
                    'face'
                ),
                new Report\Target(2),
                'Vulgarita',
                new Report\MySqlComplaint(2, $myself, $connection)
            ),
            $complaints->current()
        );
        $complaints->next();
        Assert::false($complaints->valid());
    }


    public function testComplaining() {
        $connection = $this->preparedDatabase();
        (new Report\MySqlComplainerComplaints(
            new Fake\Identity(7),
            $connection,
            new Fake\Complaints()
        ))->complain(new Report\Target(666), 'rude');
        Assert::true(true); // No exception was thrown
    }

    /**
     * @throws \OverflowException Tento komentář jsi již nahlásil
     */
    public function testAlreadyCompalinedTarget() {
        $connection = $this->preparedDatabase();
        (new Report\MySqlComplainerComplaints(
            new Fake\Identity(7),
            $connection,
            new Fake\Complaints()
        ))->complain(new Report\Target(1), 'rude');
    }

    private function preparedDatabase() {
        $connection = $this->connection();
        $connection->query('TRUNCATE comment_complaints');
        $connection->query('TRUNCATE users');
        $connection->query(
            'INSERT INTO comment_complaints
			(comment_id, settled, user_id, reason)
			VALUES 
			(1, 0, 7, "vulgarita"), (2, 0, 7, "vulgarita"), (1, 0, 2, "vulgarita")'
        );
        $connection->query(
            'INSERT INTO users (ID, role, username, email)
            VALUES
            (7, "member", "face", "e1"), (2, "member", "face2", "e2")'
        );
        return $connection;
    }
}


(new MySqlComplainerComplaints())->run();
