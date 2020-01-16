<?php

namespace Esites\EncryptionBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Esites\EncryptionBundle\Configuration\Encrypted;
use Esites\EncryptionBundle\Exception\NoEncryptionKeyException;
use ParagonIE\Halite\Alerts\CannotPerformOperation;
use ParagonIE\Halite\Alerts\InvalidDigestLength;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Alerts\InvalidMessage;
use ParagonIE\Halite\Alerts\InvalidType;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class EncryptionListener extends AbstractListener
{
    /**
     * @throws ReflectionException
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();

        if (!$entityManager instanceof EntityManagerInterface) {
            return;
        }

        $entity = $args->getObject();

        $class = get_class($entity);
        $classMetadata = $entityManager->getClassMetadata($class);
        $reflectionClass = new ReflectionClass($class);

        foreach ($reflectionClass->getProperties() as $property) {
            if (!$this->hasAnnotation($property)) {
                continue;
            }

            $value = $classMetadata->getFieldValue(
                $entity,
                $property->getName()
            );
            $value = $this->encryptionHelper->decrypt($value);

            $classMetadata->setFieldValue(
                $entity,
                $property->getName(),
                $value
            );
        }
    }

    /**
     * @throws CannotPerformOperation
     * @throws InvalidDigestLength
     * @throws InvalidKey
     * @throws InvalidMessage
     * @throws InvalidType
     * @throws NoEncryptionKeyException
     */
    public function processProperty(
        ReflectionProperty $property,
        array $changeSet,
        object $entity,
        ClassMetadata $classMetadata
    ): void {
        if (!isset($changeSet[$property->getName()][1])) {
            return;
        }

        $value = $classMetadata->getFieldValue(
            $entity,
            $property->getName()
        );

        if ($value === null) {
            return;
        }

        $encrypt = $this->getEncryptionValue($changeSet[$property->getName()]);

        $classMetadata->setFieldValue(
            $entity,
            $property->getName(),
            $encrypt
        );
    }

    /**
     * @throws NoEncryptionKeyException
     * @throws CannotPerformOperation
     * @throws InvalidDigestLength
     * @throws InvalidKey
     * @throws InvalidMessage
     * @throws InvalidType
     */
    private function getEncryptionValue(array $changes): ?string
    {
        if (!isset($changes[1])) {
            return null;
        }

        $isValidEncryption = $this->encryptionHelper->isValidEncryption(
            $changes[0],
            $changes[1]
        );

        if ($isValidEncryption) {
            return $changes[0];
        }

        return $this->encryptionHelper->encrypt($changes[1]);
    }

    public function hasAnnotation(ReflectionProperty $reflectionProperty): bool
    {
        $annotation = $this->annotationReader->getPropertyAnnotation(
            $reflectionProperty,
            Encrypted::class
        );

        return $annotation instanceof Encrypted;
    }
}
