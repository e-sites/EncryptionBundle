<?php

namespace Esites\EncryptionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="test_entity")
 * @ORM\Entity(repositoryClass="Esites\EncryptionBundle\Repository\TestRepository")
 */
class TestEntity
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="encrypted_value", type="string", nullable=true)
     */
    private $encryptedValue;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hashed_value", type="string", nullable=true)
     */
    private $hashedValue;

    /**
     * @var string|null
     *
     * @ORM\Column(name="string_value", type="string", nullable=true)
     */
    private $stringValue;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_time_value", type="datetime", nullable=true)
     */
    private $dateTimeValue;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): TestEntity
    {
        $this->id = $id;

        return $this;
    }

    public function getEncryptedValue(): ?string
    {
        return $this->encryptedValue;
    }

    public function setEncryptedValue(?string $encryptedValue): TestEntity
    {
        $this->encryptedValue = $encryptedValue;

        return $this;
    }

    public function getHashedValue(): ?string
    {
        return $this->hashedValue;
    }

    public function setHashedValue(?string $hashedValue): TestEntity
    {
        $this->hashedValue = $hashedValue;

        return $this;
    }

    public function getStringValue(): ?string
    {
        return $this->stringValue;
    }

    public function setStringValue(?string $stringValue): TestEntity
    {
        $this->stringValue = $stringValue;

        return $this;
    }

    public function getDateTimeValue(): ?\DateTime
    {
        return $this->dateTimeValue;
    }

    public function setDateTimeValue(?\DateTime $dateTimeValue): TestEntity
    {
        $this->dateTimeValue = $dateTimeValue;

        return $this;
    }
}
