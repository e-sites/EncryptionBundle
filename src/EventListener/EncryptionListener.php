<?php

namespace Esites\EncryptionBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Esites\EncryptionBundle\Configuration\Encrypted;
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

            if (!is_string($value)) {
                continue;
            }

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

        $oldValue = $changeSet[$property->getName()][0] ?? null;
        $newValue = $changeSet[$property->getName()][1] ?? null;

        if (!is_string($newValue)) {
            return;
        }

        if (!is_string($oldValue) && $oldValue !== null) {
            return;
        }

        $encryptedValue = $this->getEncryptedValue($oldValue, $newValue);

        $classMetadata->setFieldValue(
            $entity,
            $property->getName(),
            $encryptedValue
        );
    }

    /**
     * @throws CannotPerformOperation
     * @throws InvalidDigestLength
     * @throws InvalidKey
     * @throws InvalidMessage
     * @throws InvalidType
     */
    private function getEncryptedValue(string $oldValue, string $newValue): ?string
    {
        $isValidEncryption = $this->encryptionHelper->isValidEncryption(
            $oldValue,
            $newValue
        );

        if ($isValidEncryption) {
            return $oldValue;
        }

        return $this->encryptionHelper->encrypt($newValue);
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
