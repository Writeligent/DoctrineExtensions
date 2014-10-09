<?php

namespace Writeligent\DoctrineExtensions\Identity;

use Nette;

/**
* @ORM\Entity()
*/
class MetaIdentity implements Nette\Security\IIdentity
{

	public function __construct($id, $class)
	{
		$this->id = $id;
		$this->class = $class;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getClass()
	{
		return $this->class;
	}

	public function getRoles()
	{
		return array();
	}
}
