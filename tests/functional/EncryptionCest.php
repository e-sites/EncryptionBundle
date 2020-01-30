<?php

namespace EncryptionBundleTests\functional;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Esites\EncryptionBundle\Tests\Entity\TestEntity;
use FunctionalTester;

class EncryptionCest
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function _before(FunctionalTester $I): void
    {


        $this->entityManager = EntityManager::create($params, $config);;

        $I->runShellCommand('php bin/console doctrine:schema:update -force');
    }

    public function testEncryption(FunctionalTester $I): void
    {
        $testRepository = $this->entityManager->getRepository(TestEntity::class);
        $entity = $testRepository->findOneBy([]);

        $I->assertInstanceOf(TestEntity::class, $entity);
    }
}