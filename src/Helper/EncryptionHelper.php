<?php

namespace Esites\EncryptionBundle\Helper;

use Exception;
use ParagonIE\Halite\Alerts\CannotPerformOperation;
use ParagonIE\Halite\Alerts\InvalidDigestLength;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Alerts\InvalidMessage;
use ParagonIE\Halite\Alerts\InvalidType;
use ParagonIE\Halite\Key;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use Symfony\Component\Filesystem\Filesystem;

class EncryptionHelper
{
    /**
     * @var Key|null
     */
    private $encryptionKey;

    /**
     * @var string
     */
    private $encryptionKeyFile;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string|null
     */
    private $hashAlgorithm;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        string $encryptionKeyFile,
        string $hashAlgorithm
    ) {
        $this->encryptionKeyFile = $encryptionKeyFile;
        $this->fileSystem = new Filesystem();
        $this->hashAlgorithm = $hashAlgorithm;
    }

    /**
     * @throws CannotPerformOperation
     * @throws InvalidKey
     * @throws InvalidDigestLength
     * @throws InvalidMessage
     * @throws InvalidType
     */
    public function encrypt(?string $string): ?string
    {
        if ($string === null) {
            return null;
        }

        return Crypto::encrypt(
            new HiddenString($string),
            $this->getEncryptionKey()
        );
    }

    public function isValidEncryption(?string $encryptedValue, ?string $value): bool
    {
        if ($encryptedValue === null && $value === null) {
            return true;
        }

        $decryptedValue = $this->decrypt($encryptedValue);

        if ($decryptedValue === $encryptedValue) {
            return false;
        }

        return $value === $decryptedValue;
    }

    public function decrypt(?string $string): ?string
    {
        if ($string === null) {
            return null;
        }

        try {
            return Crypto::decrypt(
                $string,
                $this->getEncryptionKey()
            );
        } catch (Exception $exception) {
            return $string;
        }
    }

    public function hash(?string $string): ?string
    {
        if ($string === null) {
            return null;
        }

        return hash(
            $this->hashAlgorithm,
            $string
        );
    }

    /**
     * @throws CannotPerformOperation
     * @throws InvalidKey
     */
    private function getEncryptionKey(): EncryptionKey
    {
        $key = $this->encryptionKey;

        if (!$key instanceof EncryptionKey) {
            return $this->setEncryptionKey();
        }

        return $key;
    }

    /**
     * @throws InvalidKey
     * @throws CannotPerformOperation
     */
    private function generateEncryptionKey(): void
    {
        $this->createEncryptionKeyDirectory();

        $encryptionKey = KeyFactory::generateEncryptionKey();
        KeyFactory::save($encryptionKey, $this->encryptionKeyFile);
    }

    private function createEncryptionKeyDirectory() {
        $this->fileSystem->mkdir(
            dirname($this->encryptionKeyFile)
        );
    }

    /**
     * @throws InvalidKey
     * @throws CannotPerformOperation
     */
    private function setEncryptionKey(): EncryptionKey
    {
        if (!$this->fileSystem->exists($this->encryptionKeyFile)) {
            $this->generateEncryptionKey();
        }

        $key = KeyFactory::loadEncryptionKey(
            $this->encryptionKeyFile
        );

        $this->encryptionKey = $key;

        return $key;
    }
}
