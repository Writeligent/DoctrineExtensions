<?php

namespace WriteligentTests\DoctrineExtensions;

/**
* Test: Save identity entity.
*/
use Doctrine\ORM\Tools\SchemaTool;
use Kdyby\Doctrine;
use Nette;
use Tester;

class TestCase extends Tester\TestCase {

	/** @var Nette\DI\Container[] $container */
	private $container;

	protected function getContainer($configName = 'base')
	{
		if (!isset($this->container[$configName])) {
			$this->container[$configName] = $this->createContainer($configName);
		}
		return $this->container[$configName];
	}

	public function createContainer($configName = 'base')
	{
		$rootDir = __DIR__ . '/../../';
		$config = new Nette\Configurator();
		\Writeligent\DoctrineExtensions\DI\Extension::register($config);
		\Kdyby\Events\DI\EventsExtension::register($config);
		\Kdyby\Console\DI\ConsoleExtension::register($config);
		\Kdyby\Annotations\DI\AnnotationsExtension::register($config);
		\Kdyby\Doctrine\DI\OrmExtension::register($config);
		$container = $config->setTempDirectory(TEMP_DIR)
			->addConfig(__DIR__ . '/../../config/' . $configName . '.neon', $config::NONE)
			->addParameters(array(
				'appDir' => $rootDir,
				'wwwDir' => $rootDir,
			))
			->createContainer();
		$em = $container->getByType('Kdyby\Doctrine\EntityManager');
		/** @var Kdyby\Doctrine\EntityManager $em */
		$schemaTool = new SchemaTool($em);
		$schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());
		return $container;
	}
}
