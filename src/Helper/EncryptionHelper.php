<?php

namespace Esites\EncryptionBundle\Helper;

use Esites\EncryptionBundle\Exception\NoEncryptionKeyException;
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
     * {@inheritdoc}
     */
    public function __construct(string $encryptionKeyFile)
    {
        $this->encryptionKeyFile = $encryptionKeyFile;
        $this->fileSystem = new Filesystem();
    }

    /**
     * @throws CannotPerformOperation
     * @throws InvalidKey
     * @throws NoEncryptionKeyException
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
            'sha256',
            $string
        );
    }

    /**
     * @throws CannotPerformOperation
     * @throws InvalidKey
     * @throws NoEncryptionKeyException
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
        $hashKey = KeyFactory::generateEncryptionKey();
        $rawKey = $hashKey->getRawKeyMaterial();

        $this->fileSystem->dumpFile(
            $this->encryptionKeyFile,
            $rawKey
        );
    }

    /**
     * @throws NoEncryptionKeyException
     * @throws InvalidKey
     * @throws CannotPerformOperation
     */
    private function setEncryptionKey(): EncryptionKey
    {
        if (!$this->fileSystem->exists($this->encryptionKeyFile)) {
            $this->generateEncryptionKey();
        }

        $encryptionKey = file_get_contents($this->encryptionKeyFile);

        if (empty($encryptionKey)) {
            $this->generateEncryptionKey();

            $encryptionKey = file_get_contents($this->encryptionKeyFile);
        }

        if (empty($encryptionKey)) {
            throw new NoEncryptionKeyException();
        }

        $key = KeyFactory::importEncryptionKey(
            new HiddenString($encryptionKey)
        );
        $this->encryptionKey = $key;

        return $key;
    }
}
