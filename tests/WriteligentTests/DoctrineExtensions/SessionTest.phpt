<?php

namespace WriteligentTests\DoctrineExtensions;

/**
* Test: Session entity
* @testCase
*/
use Tester\Assert;
use Writeligent\DoctrineExtensions\Http\SessionHandler;

require __DIR__ . '/../bootstrap.php';

class SessionTest extends TestCase {

	private $usersDao;

	public function setup()
	{
		$connection = $this->getContainer()->getByType('Doctrine\DBAL\Connection');
		$manager = $connection->getSchemaManager();
		if (!$manager->tablesExist('session')) {
			$schema = new \Doctrine\DBAL\Schema\Schema();
			$session = $schema->createTable('session');
			$session->addColumn('name', 'string');
			$session->addColumn('data', 'string');
			$session->addColumn('time', 'datetime');
			$session->addColumn('locked', 'string', ['notnull' => false]);


			$manager->createTable($session);
		}
	}

	public function testProcess()
	{
		$connection = $this->getContainer()->getByType('Doctrine\DBAL\Connection');

		$sessionHandler1 = new SessionHandler($connection, 'session');

		$name = 'process' . getmypid();

		$sessionHandler1->open('foo', 'bar');
		$sessionHandler1->read($name);
		$sessionHandler1->write($name, 'test#1');
		$sessionHandler1->close();

		$sessionHandler2 = new SessionHandler($connection, 'session');
		$sessionHandler2->open('foo', 'bar');


		Assert::same('test#1', $sessionHandler2->read($name));

		$sessionHandler2->destroy($name);
		$sessionHandler2->close();

		$sessionHandler1->open('foo', 'bar');
		Assert::same('', $sessionHandler1->read($name));
		$sessionHandler1->close();
	}

	public function testGC()
	{
		$connection = $this->getContainer()->getByType('Doctrine\DBAL\Connection');

		$sessionHandler = new SessionHandler($connection, 'session');

		$name = 'process'. getmypid();

		$sessionHandler->open('foo', 'bar');
		$sessionHandler->read($name);
		$sessionHandler->write($name, 'test#2');

		sleep(2);

		$sessionHandler->gc(1);
		$sessionHandler->close();

		$sessionHandler->open('foo', 'bar');
		Assert::same('', $sessionHandler->read($name));
	}

}

run(new SessionTest);
