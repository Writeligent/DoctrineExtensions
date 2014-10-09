<?php

namespace WriteligentTests\DoctrineExtensions\Model;

use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;

/**
* @ORM\Entity()
*/
class User extends Kdyby\Doctrine\Entities\IdentifiedEntity implements Nette\Security\IIdentity
{
	public function getRoles()
	{
		return array();
	}
}
