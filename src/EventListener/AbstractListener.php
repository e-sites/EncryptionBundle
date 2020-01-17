<?php

namespace Esites\EncryptionBundle\EventListener;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Esites\EncryptionBundle\Helper\EncryptionHelper;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractListener
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * @var CachedReader
     */
    protected $annotationReader;

    /**
     * @var EncryptionHelper
     */
    protected $encryptionHelper;


    public function __construct(
        CachedReader $annotationReader,
        EncryptionHelper $encryptionHelper
    ) {
        $this->annotationReader = $annotationReader;
        $this->encryptionHelper = $encryptionHelper;
    }

    abstract public function processProperty(
        ReflectionProperty $property,
        array $changeSet,
        object $entity,
        ClassMetadata $classMetadata
    ): void;

    abstract public function hasAnnotation(ReflectionProperty $reflectionProperty): bool;

    /**
     * @throws ReflectionException
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->processEntity($args);
    }

    /**
     * @throws ReflectionException
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->processEntity($args);
    }

    /**
     * @throws ReflectionException
     */
    private function processEntity(LifecycleEventArgs $args): void
    {
        $this->entityManager = $args->getEntityManager();
        $this->unitOfWork = $this->entityManager->getUnitOfWork();

        $entity = $args->getEntity();

        $class = get_class($entity);
        $reflectionClass = new ReflectionClass($class);

        /** @var ReflectionProperty[] $properties */
        $properties = [];

        foreach ($reflectionClass->getProperties() as $property) {
            if (!$this->hasAnnotation($property)) {
                continue;
            }

            $properties[] = $property;
        }

        if (!count($properties)) {
            return;
        }

        $classMetadata = $this->entityManager->getClassMetadata($class);
        $changeSet = $this->getChangeSet(
            $entity,
            $classMetadata
        );

        if (!$changeSet) {
            return;
        }

        foreach ($properties as $property) {
            $this->processProperty(
                $property,
                $changeSet,
                $entity,
                $classMetadata
            );
        }

        if ($this->unitOfWork->getSingleIdentifierValue($entity) !== null) {
            $this->unitOfWork->recomputeSingleEntityChangeSet(
                $classMetadata,
                $entity
            );

            return;
        }
    }

    private function getChangeSet(object $entity, ClassMetadata $classMetadata): array
    {
        if ($this->unitOfWork->getSingleIdentifierValue($entity) !== null) {
            return $this->unitOfWork->getEntityChangeSet($entity);
        }

        $changeSet = [];
        $fields = $classMetadata->getFieldNames();

        foreach ($fields as $field) {
            $changeSet[$field] = [
                null,
                $classMetadata->getFieldValue(
                    $entity,
                    $field
                ),
            ];
        }

        return $changeSet;
    }
}
