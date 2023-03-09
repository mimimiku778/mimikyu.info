<?php

declare(strict_types=1);

/**
 * Encrypt and decrypt strings using AES-CBC and obtain hashes of encrypted strings using HKDF.
 * 
 * @author mimimiku778 <0203.sub@gmail.com>
 * @license https://github.com/mimimiku778/MimimalCMS/blob/master/LICENSE.md
 */
class StringCryptor
{
    // NOTE: Replace the following placeholder keys with your own keys before deploying to production.
    private const DEFALUT_HKDF_KEY = 'REPLACE_WITH_YOUR_HKDF_KEY';
    private const DEFALUT_OPENSSL_KEY = 'REPLACE_WITH_YOUR_OPENSSL_KEY';

    private string $hkdfKey;
    private string $opensslKey;

    /**
     * Class constructor.
     * 
     * **Example of key generation**
     * 
     * * Generate a key from an arbitrary password
     * `$key = hash('sha256', 'YOUR_PASSWORD');`
     * 
     * * Generate a random key
     * `$key = bin2hex(random_bytes(32));`
     * 
     * @param string|null $hkdfKey [optional] The HKDF key to use. If null, the default key will be used.
     * @param string|null $opensslKey [optional] The OpenSSL key to use. If null, the default key will be used.
     */
    public function __construct(?string $hkdfKey = null, ?string $opensslKey = null)
    {
        $this->hkdfKey = $hkdfKey ?? self::DEFALUT_HKDF_KEY;
        $this->opensslKey = $opensslKey ?? self::DEFALUT_OPENSSL_KEY;
    }

    /**
     * Encrypts a string to a Base64 URL encoded string using AES-CBC and obtains the hash of the encrypted string using HKDF.
     * 
     * @param string $string The string to be encrypted and hashed.
     * * *Example:* `'Hello World'`
     * 
     * @return string Returns a Base64 URL encoded string containing encrypted text and its hash, 
     * decryptable using `verifyHashAndDecrypt()` method only.
     * * *Example:* 
     *  `'cnJHV0JVR2dRSFRyeE9JU0VzQVRndz09QE1LMGM4VjBzV25yeXhBd1MzVmR0RFE9PSNMd1YwMHVWc3FQVHNXWnZUSG0raXU4VnJhWld1NGFCZ3R2ZmtWUT09QFIxMk9yK0FVSEJZajJ5NDdzRE5CZlE9PQ'`
     * 
     * @throws LogicException If the encryption fails.
     */
    public function encryptAndHashString(string $string): string
    {
        $encryptedString = $this->encryptAesCbcString($string);
        $hash = $this->hashHkdf($encryptedString);

        return $this->encodeBase64URL($encryptedString . '#' . $hash);
    }

    /**
     * Verifies the hash of a Base64 URL encoded encrypted string and returns the decrypted string.
     * 
     * @param string $encryptedString A Base64 URL encoded string that is a concatenation of the encrypted string and its hash, 
     * generated by `encryptAndHashString()` method only.
     * * *Example:*
     *  `'cnJHV0JVR2dRSFRyeE9JU0VzQVRndz09QE1LMGM4VjBzV25yeXhBd1MzVmR0RFE9PSNMd1YwMHVWc3FQVHNXWnZUSG0raXU4VnJhWld1NGFCZ3R2ZmtWUT09QFIxMk9yK0FVSEJZajJ5NDdzRE5CZlE9PQ'`
     * 
     * @return string Returns the decrypted string if the hash is valid.
     * * *Example:* `'Hello World'`
     * 
     * @throws RuntimeException If the Base64 URL encoded encrypted string is invalid.
     * @throws LogicException If the hash is valid but decryption fails.
     */
    public function verifyHashAndDecrypt(string $encryptedString): string
    {
        $components = explode("#", $this->decodeBase64URL($encryptedString));

        if (count($components) !== 2) {
            throw new RuntimeException('Invalid format for the Base64 URL encoded string.');
        }

        $aesCbcEncryptedString = $components[0];
        $hash = $components[1];

        if (!$this->hkdfEquals($aesCbcEncryptedString, $hash)) {
            throw new RuntimeException('Invalid hash for the Base64 URL encoded string.');
        }

        try {
            $decryptedString = $this->decryptAesCbcString($aesCbcEncryptedString);
        } catch (RuntimeException $e) {
            throw new LogicException('Hash is valid but decryption fails: ' . $e->getMessage());
        }

        return $decryptedString;
    }

    /**
     * Encrypts a string to a Base64 URL encoded string with hashed validity period using AES-CBC and HKDF.
     * 
     * @param string $string The string to be encrypted and hashed.
     * * *Example:* `'Hello World'`
     * 
     * @param int $expires The validity period in Unix time should be 10 digits. The encrypted and hashed string can only be decrypted within this period.
     * * *Example:* `time() + (7 * 24 * 60 * 60)` `1678970283`
     * 
     * @return string Returns a Base64 URL encoded string containing encrypted text, its hash and the validity period, 
     * decryptable using `verifyHashAndDecryptWithValidity()` method only.
     * * *Example:* 
     *  `'1678970283dbGRqVXp4L0dDbXNjNUNPazEwakI5UT09QHRkUTVxYXNNNk5nZmhqTWZiWjNYWnc9PSM1Y2hPNFpRVFZqbmJJQno3LzgxMEFzVzY1UkdNVlVRdU9xanR0Zz09QHVLM29vRXR2NG9kY295bnVWYStuSnc9PQ'`
     * 
     * @throws InvalidArgumentException If the validity period in Unix time is before now, or not 10 digits.
     * @throws LogicException If the encryption fails.
     */
    public function encryptAndHashWithValidity(string $string, int $expires): string
    {
        if ($expires < time()) {
            throw new InvalidArgumentException(
                'Invalid parameter value for expires: only time after now allowed.'
            );
        }

        if (strlen((string) $expires) !== 10) {
            throw new InvalidArgumentException(
                'Invalid parameter value for expires: Unix time should be 10 digits.'
            );
        }

        $encryptedString = $this->encryptAesCbcString($string);
        $hash = $this->hashHkdf($encryptedString . (string) $expires);

        return (string) $expires . 'd' . $this->encodeBase64URL($encryptedString . '#' . $hash);
    }

    /**
     * Verifies the hash and validity period of a Base64 URL encoded encrypted string and returns the decrypted string if the validity period is still valid.
     * 
     * @param string $encryptedString A Base64 URL encoded string that is a concatenation of the encrypted string and its hash, 
     * generated by `encryptAndHashWithValidity()` method only.
     * * *Example:*
     *  `'1678970283dbGRqVXp4L0dDbXNjNUNPazEwakI5UT09QHRkUTVxYXNNNk5nZmhqTWZiWjNYWnc9PSM1Y2hPNFpRVFZqbmJJQno3LzgxMEFzVzY1UkdNVlVRdU9xanR0Zz09QHVLM29vRXR2NG9kY295bnVWYStuSnc9PQ'`
     * 
     * @return array|false Returns an array contains the decrypted string and the expiry time, if the hash is valid or false if expires.
     * * *Example:* `[1678970283, 'Hello World']`
     * 
     * @throws RuntimeException If the Base64 URL encoded encrypted string is invalid.
     * @throws LogicException If the hash is valid but decryption fails.
     */
    public function verifyHashAndDecryptWithValidity(string $encryptedString): array|false
    {
        $data = substr($encryptedString, 11);
        $components = explode("#", $this->decodeBase64URL($data));

        if (count($components) !== 2) {
            throw new RuntimeException('Invalid format for the Base64 URL encoded string.');
        }

        $expires = strtok($encryptedString, 'd');
        $aesCbcEncryptedString = $components[0];
        $hash = $components[1];

        if (!$this->hkdfEquals($aesCbcEncryptedString . $expires, $hash)) {
            throw new RuntimeException('Invalid hash for the Base64 URL encoded string.');
        }

        if ((int) $expires < time()) {
            return false;
        }

        try {
            $decryptedString = $this->decryptAesCbcString($aesCbcEncryptedString);
        } catch (RuntimeException $e) {
            throw new LogicException('Hash is valid but decryption fails: ' . $e->getMessage());
        }

        return [(int) $expires, $decryptedString];
    }


    /**
     * Hashes a string using HKDF with SHA3-224 and returns a string in the format of `hash`@`salt`.
     *
     * @param string $string The string to hash.
     * * *Example:* `'Hello World'`
     * 
     * @return string The hashed string with salt.
     * * *Example:* `'2VrazmQuSS0alphnIsMXGBp2LEzmiCpcxMJ/Mg==@0VJpOVEUTZqDG8J4DGlRqA=='`
     */
    public function hashHkdf(string $string): string
    {
        $salt = random_bytes(16);
        $hash = hash_hkdf('SHA3-224', $this->hkdfKey, 0, $string, $salt);

        // Return the Base64 encoded hash with the salt in the format of `hash`@`salt`.
        return base64_encode($hash) . '@' . base64_encode($salt);
    }

    /**
     * Compares a string with a HKDF hashed string with salt in the format of `hash`@`salt`.
     *
     * @param string $string The string to compare.
     * * *Example:* `'Hello World'`
     * 
     * @param string $hashedString The HKDF hashed string with salt in the format of `hash`@`salt`.
     * * *Example:* `'2VrazmQuSS0alphnIsMXGBp2LEzmiCpcxMJ/Mg==@0VJpOVEUTZqDG8J4DGlRqA=='`
     * 
     * @return bool True if the strings are equal, false otherwise.
     * 
     * @throws RuntimeException If the HKDF hashed string is invalid format.
     */
    public function hkdfEquals(string $string, string $hashedString): bool
    {
        $components = explode('@', $hashedString);

        if (count($components) !== 2) {
            throw new RuntimeException('Invalid format for the HKDF hashed string.');
        }

        $hash = base64_decode($components[0]);
        $salt = base64_decode($components[1]);

        $reHash = hash_hkdf('SHA3-224', $this->hkdfKey, 0, $string, $salt);

        return hash_equals($hash, $reHash);
    }

    /**
     * Encrypts a string using the AES-256-CBC algorithm and returns the encrypted string in the format of `string`@`iv`.
     * 
     * @param string $targetString The string to encrypt.
     * * *Example:* `'Hello World'`
     * 
     * @return string The encrypted string in the format of `string`@`iv`.
     * * *Example:* `'hexzX3nLJKqMWuXEhiOQHQ==@IsMEmTl11x6Siyyug2HBnw=='`
     * 
     * @throws LogicException If the encryption fails.
     */
    public function encryptAesCbcString(string $targetString): string
    {
        $iv = random_bytes(16);

        $encryptedData = openssl_encrypt(
            $targetString,
            'AES-256-CBC',
            $this->opensslKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encryptedData === false) {
            throw new LogicException('Encryption failed.');
        }

        // Return the Base64-encoded encrypted string in the format `string`@`iv`.
        return base64_encode($encryptedData) . '@' . base64_encode($iv);
    }

    /**
     * 
     * Decrypts a string that was encoded using AES-256-CBC algorithm with the format of `string`@`iv`.
     * 
     * @param string $encryptedString The encrypted string to decrypt in the format of `string`@`iv`.
     * * *Example:* `'hexzX3nLJKqMWuXEhiOQHQ==@IsMEmTl11x6Siyyug2HBnw=='`
     * 
     * @return string The decrypted string.
     * * *Example:* `'Hello World'`
     * 
     * @throws RuntimeException If the decryption fails.
     */
    public function decryptAesCbcString(string $encryptedString): string
    {
        $components = explode('@', $encryptedString);
        if (count($components) !== 2) {
            throw new RuntimeException('Invalid format for the encrypted string.');
        }

        $encryptedData = base64_decode($components[0]);
        $iv = base64_decode($components[1]);

        $decryptedData = openssl_decrypt(
            $encryptedData,
            'AES-256-CBC',
            $this->opensslKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decryptedData === false) {
            throw new RuntimeException('Decryption failed.');
        }

        return $decryptedData;
    }

    /**
     * Encodes a string to Base64 URL.
     *
     * @param string $string The string to encode.
     * * *Example:* `'Hello World'`
     * 
     * @return string The Base64 URL encoded string.
     * * *Example:* `'SGVsbG8gV29ybGQ'`
     */
    public function encodeBase64URL(string $string): string
    {
        $base64 = base64_encode($string);
        $urlSafe = strtr($base64, '+/', '-_');
        return rtrim($urlSafe, '=');
    }

    /**
     * Decodes a Base64 URL encoded string.
     *
     * @param string $encodedString The Base64 URL encoded string to decode.
     * * *Example:* `'SGVsbG8gV29ybGQ'`
     * 
     * @return string The decoded string.
     * * *Example:* `'Hello World'`
     */
    public function decodeBase64URL(string $encodedString): string
    {
        $str = strtr($encodedString, '-_', '+/');
        $padding = strlen($str) % 4;
        if ($padding !== 0) {
            $str = str_pad($str, strlen($str) + (4 - $padding), '=', STR_PAD_RIGHT);
        }
        return base64_decode($str);
    }
}
