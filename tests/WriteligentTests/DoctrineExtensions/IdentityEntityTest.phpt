<?php

namespace WriteligentTests\DoctrineExtensions;

/**
* Test: Identity entity
*/
use Tester\Assert;
use Writeligent\DoctrineExtensions\Identity\UserStorage;

require __DIR__ . '/../bootstrap.php';

class IdentityEntityTest extends TestCase {

	private $usersDao;

	public function setUp()
	{
		$this->usersDao = $this->getContainer()->getByType('Kdyby\Doctrine\EntityManager')->getDao(Model\User::getClassName());
		$user = new Model\User(1);
		$this->usersDao->save($user);
	}

	public function testSaving()
	{
		$userStorage = $this->getContainer()->getByType('Writeligent\DoctrineExtensions\Identity\UserStorage');
		$userStorage->setIdentity($this->usersDao->find(1));
		$session = $this->getContainer()->getByType('Nette\Http\Session');
		$section = $session->getSection('Nette.Http.UserStorage/');

		Assert::type('Writeligent\DoctrineExtensions\Identity\MetaIdentity', $section->identity);
		Assert::same(Model\User::getClassName(), $section->identity->getClass());
		Assert::same(['id' => 1], $section->identity->getId());
	}

	public function testLoading()
	{
		$userStorage = $this->getContainer()->getByType('Writeligent\DoctrineExtensions\Identity\UserStorage');
		$metaIdentity = new \Writeligent\DoctrineExtensions\Identity\MetaIdentity(['id' => 1], Model\User::getClassName());
		$userStorage->setIdentity($metaIdentity);
		$identity = $userStorage->getIdentity();

		Assert::type(Model\User::getClassName(), $identity);
		Assert::same(1, $identity->id);
	}

}

run(new IdentityEntityTest);
