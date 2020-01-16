<?php

namespace Esites\EncryptionBundle\EventListener;

use Doctrine\ORM\Mapping\ClassMetadata;
use Esites\EncryptionBundle\Configuration\Hashed;
use ReflectionProperty;
use Symfony\Component\Security\Core\User\UserInterface;

class HashListener extends AbstractListener
{
    public function processProperty(
        ReflectionProperty $property,
        array $changeSet,
        object $entity,
        ClassMetadata $classMetadata
    ): void {
        if (!isset($changeSet[$property->getName()][1])) {
            return;
        }

        $value = $changeSet[$property->getName()][1];

        // fos listens to changes in username and sets that value in username canonical
        // decrypt the value first before hashing it
        if ($entity instanceof UserInterface) {
            $value = $this->encryptionHelper->decrypt($value);
        }

        $hashed = $this->encryptionHelper->hash($value);

        $classMetadata->setFieldValue(
            $entity,
            $property->getName(),
            $hashed
        );
    }

    public function hasAnnotation(ReflectionProperty $reflectionProperty): bool
    {
        $annotation = $this->annotationReader->getPropertyAnnotation(
            $reflectionProperty,
            Hashed::class
        );

        return $annotation instanceof Hashed;
    }
}
