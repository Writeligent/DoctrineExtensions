<?php

namespace Writeligent\DoctrineExtensions\Identity;

use Kdyby;
use Nette;

class UserStorage extends Nette\Http\UserStorage
{

	private $entityManager;

	public function __construct(Nette\Http\Session $sessionHandler, Kdyby\Doctrine\EntityManager $entityManager)
	{
		parent::__construct($sessionHandler);
		$this->entityManager = $entityManager;
	}

	public function setIdentity(Nette\Security\IIdentity $identity = null)
	{
		if ($identity !== null) {
			$class = get_class($identity);
			if ($this->entityManager->getMetadataFactory()->hasMetadataFor($class)) {
				$cm = $this->entityManager->getClassMetadata($class);
				$identifier = $cm->getIdentifierValues($identity);
				$identity = new MetaIdentity($identifier, $class);
			}
		}
		parent::setIdentity($identity);
	}

	public function getIdentity()
	{
		$identity = parent::getIdentity();
		if ($identity instanceof MetaIdentity) {
			return $this->entityManager->getReference($identity->getClass(), $identity->getId());
		}
		return $identity;
	}

}
