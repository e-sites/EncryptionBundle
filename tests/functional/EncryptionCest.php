<?php

namespace EncryptionBundleTests\functional;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
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

        $config = new Configuration();
        $config->setEntityNamespaces(array('EncryptionBundleTests' => 'EncryptionBundle\Tests\Entity'));
        $config->setAutoGenerateProxyClasses(true);
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace('EncryptionBundleTests\Entity');
        $config->setQueryCacheImpl(new ArrayCache());
        $config->setMetadataCacheImpl(new ArrayCache());

        $entityManager = $currentEntityManager::create($currentEntityManager->getConnection(), $config);

        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getClassMetadata(EncryptionEntity::class);
        $sqlDiff = $schemaTool->getUpdateSchemaSql([$metadata], true);

        foreach ($sqlDiff as $query) {
            $entityManager->getConnection()
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
