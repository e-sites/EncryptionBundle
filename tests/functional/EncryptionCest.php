<?php

namespace EncryptionBundleTests\functional;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Esites\EncryptionBundle\Tests\Entity\EncryptionEntity;
use FunctionalTester;

class EncryptionCest
{
    private function getEntityManager(FunctionalTester $I): EntityManager
    {
        /** @var EntityManager $currentEntityManager */
        $currentEntityManager = $I->grabService('doctrine.orm.entity_manager');
        $configuration = $currentEntityManager->getConfiguration();

        $configuration->setEntityNamespaces(['EncryptionBundleTests' => 'EncryptionBundle\Tests\Entity']);
        $configuration->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
        $configuration->setProxyNamespace('EncryptionBundleTests\Entity');

        $entityManager = $currentEntityManager::create($currentEntityManager->getConnection(), $configuration);

        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getClassMetadata(EncryptionEntity::class);
        $sqlDiff = $schemaTool->getUpdateSchemaSql([$metadata], true);

        foreach ($sqlDiff as $query) {
            $entityManager
                ->getConnection()
                ->executeQuery($query)
            ;
        }

        return $entityManager;
    }

    public function testEncryption(FunctionalTester $I): void
    {
        $entityManager = $this->getEntityManager($I);

        $encryptionEntity = new EncryptionEntity();
        $encryptionEntity->setEncryptedValue('test');

        $entityManager->persist($encryptionEntity);
        $entityManager->flush();

        $testRepository = $entityManager->getRepository(EncryptionEntity::class);
        $entity = $testRepository->findOneBy([]);

        $I->assertInstanceOf(EncryptionEntity::class, $entity);
    }
}
