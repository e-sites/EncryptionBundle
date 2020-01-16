<?php

namespace Esites\EncryptionBundle\Constants;

class ConfigConstants
{
    public const CONFIG_PREFIX_KEY = 'esites_encryption_bundle.';

    public const CONFIG_USERNAME_FIELD = 'username_field';
    public const CONFIG_USER_CLASS = 'user_class';
    public const CONFIG_ENCRYPTION_KEY_FILE = 'encryption_key_file';

    public static function getParameterKeyName(string $key): string
    {
        return static::CONFIG_PREFIX_KEY . $key;
    }
}
