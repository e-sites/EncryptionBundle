<?php

namespace EncryptionBundleTests\functional;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Esites\EncryptionBundle\Helper\EncryptionHelper;
use Esites\EncryptionBundle\Tests\Entity\EncryptionEntity;
use FunctionalTester;

class EncryptionCest
{
    /**
     * @var EncryptionHelper
     */
    private $encryptionHelper;


    public function _before(FunctionalTester $I): void
    {
        $this->encryptionHelper = $I->grabService(EncryptionHelper::class);
    }

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

        $value = 'test';

        $encryptionEntity = new EncryptionEntity();
        $encryptionEntity->setEncryptedValue($value);

        $entityManager->persist($encryptionEntity);
        $entityManager->flush();

        $data = $entityManager->getConnection()->fetchAssoc('SELECT * FROM encryption_entity WHERE id=?', [
            $encryptionEntity->getId()
        ]);

        $I->assertNotEmpty($data['encrypted_value']);

        $I->assertTrue(
            $this->encryptionHelper->isValidEncryption(
                $data['encrypted_value'],
                $value
            )
        );
    }
}
