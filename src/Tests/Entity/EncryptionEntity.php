<?php

namespace Esites\EncryptionBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Esites\EncryptionBundle\Configuration\Encrypted;

/**
 * @ORM\Table(name="encryption_entity")
 * @ORM\Entity(repositoryClass="Esites\EncryptionBundle\Tests\Repository\EncryptionRepository")
 */
class EncryptionEntity
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
     * @Encrypted()
     *
     * @ORM\Column(name="encrypted_value", type="string", nullable=true)
     */
    private $encryptedValue;

    /**
     * @var string|null
     *
     * @ORM\Column(name="string_value", type="string", nullable=true)
     */
    private $stringValue;

    /**
     * @var \DateTime|null
     *
     * @Encrypted()
     *
     * @ORM\Column(name="date_time_value", type="datetime", nullable=true)
     */
    private $dateTimeValue;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): EncryptionEntity
    {
        $this->id = $id;

        return $this;
    }

    public function getEncryptedValue(): ?string
    {
        return $this->encryptedValue;
    }

    public function setEncryptedValue(?string $encryptedValue): EncryptionEntity
    {
        $this->encryptedValue = $encryptedValue;

        return $this;
    }

    public function getStringValue(): ?string
    {
        return $this->stringValue;
    }

    public function setStringValue(?string $stringValue): EncryptionEntity
    {
        $this->stringValue = $stringValue;

        return $this;
    }

    public function getDateTimeValue(): ?\DateTime
    {
        return $this->dateTimeValue;
    }

    public function setDateTimeValue(?\DateTime $dateTimeValue): EncryptionEntity
    {
        $this->dateTimeValue = $dateTimeValue;

        return $this;
    }
}
