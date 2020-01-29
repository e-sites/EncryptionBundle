# EncryptionBundle

## Usage

Use `@Encryped()` annotation at any entity property to encrypt and decrypt its value.<br>
Use `@Hashed()` annotation at any entity property to hash its value.

## User

If you wish to hash the username, use the `@Hashed()` annotation for the username field.<br>
To still be able to login, use `Esites\EncryptionBundle\Provider\UserProvider` as the provider in the firewall.<br>
To be able to use this provider, you'll need to define `user_class` and `username_field` in the config. See the full configuration below.

## Full Configuration

```yaml
esites_encryption:
    username_field: username   # default value, the property name for the username in the user entity
    user_class: App\Entity\User   # not required, full namespace of the user entity
    encryption_key_file: '%kernel.root_dir%/encryption/key'   # default value, directory and filename for the encryption key
    hash_algorithm: 'sha256'   # default value, algorithm used to hash strings. uses the native php hash() function
```