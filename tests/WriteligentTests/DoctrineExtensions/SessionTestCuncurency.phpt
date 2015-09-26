<?php

namespace WriteligentTests\DoctrineExtensions;

/**
* Test: Session entity concurency
* @multiple 5
*/
use Tester\Assert;
use Writeligent\DoctrineExtensions\Http\SessionHandler;

require __DIR__ . '/../bootstrap.php';

class SessionTestCuncurency extends TestCase {

	private $usersDao;

	public function setup()
	{
		$connection = $this->getContainer('session')->getByType('Doctrine\DBAL\Connection');
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

	public function testCuncurency()
	{
		$connection = $this->getContainer('session')->getByType('Doctrine\DBAL\Connection');

		$sessionHandler = new SessionHandler($connection, 'session');

		$err = 0;
		$sessionHandler->open('foo', 'bar');
		for ($i = 0; $i < 50; $i++) {
			$value = (string) getmypid() . rand (1000, 9999);
			$sessionHandler->write('concurency', $value);
			usleep(rand(1, 10000));
			if ($sessionHandler->read('concurency') !== $value) {
				$sessionHandler->close();
				var_dump($value, $sessionHandler->read('concurency'));
				$err++;
				break;
			}
		}
		$sessionHandler->close();
		Assert::same(0, $err);
	}

}

run(new SessionTestCuncurency);
