<?php

/*
 * This file is part of the Osirisgate package.
 *
 * (c) Ulrich Geraud AHOGLA <developer@osirisgate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Osirisgate\Component\Tokenizer\JwtToken\Strategy\RsaJwtToken;

use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Osirisgate\Component\Tokenizer\JwtToken\Entity\JwtToken;
use Osirisgate\Component\Tokenizer\JwtToken\JwtTokenProcessorInterface;
use Osirisgate\Component\Tokenizer\JwtToken\ValueObject\TokenPayload;
use Osirisgate\Component\Tokenizer\Shared\Constant\Constant;
use Osirisgate\Component\Tokenizer\Shared\Exception\HttpExceptionUtil;
use Osirisgate\Core\Exception\ExceptionInterface;
use Osirisgate\Core\Exception\RuntimeException;

/**
 * A concrete implementation of the JwtTokenProcessorInterface that uses RSA
 * (Rivest–Shamir–Adleman) algorithm for signing and verifying JSON Web Tokens (JWTs).
 * It leverages the Firebase/JWT library for JWT encoding and decoding and utilizes
 * OpenSSL for handling the private key.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final readonly class RsaJwtTokenProcessor implements JwtTokenProcessorInterface
{
    /**
     * Private constructor to enforce the use of the static `init` method for instantiation.
     *
     * @param string $privateKeyPath    path to the private key file
     * @param string $passphrase        passphrase for the private key
     * @param string $algorithm         the JWT signing algorithm (default: 'RS256')
     * @param string $timezone          the timezone for date/time operations (default: 'UTC')
     * @param int    $expirationSeconds the token expiration time in seconds
     */
    private function __construct(
        private string $privateKeyPath,
        private string $passphrase,
        private string $algorithm,
        private string $timezone,
        private int $expirationSeconds,
    ) {
    }

    /**
     * Static factory method to initialize an instance of RsaJwtTokenProcessor.
     *
     * @param string $privateKeyPath    path to the private key file
     * @param string $passphrase        passphrase for the private key
     * @param int    $expirationSeconds the token expiration time in seconds
     * @param string $algorithm         the JWT signing algorithm (default: 'RS256')
     * @param string $timezone          the timezone for date/time operations (default: 'UTC')
     *
     * @return self a new instance of RsaJwtTokenProcessor
     */
    public static function init(
        string $privateKeyPath,
        string $passphrase,
        int $expirationSeconds,
        string $algorithm = Constant::DEFAULT_JWT_ALGORITHM,
        string $timezone = Constant::UTC_TIMEZONE
    ): self {
        return new self(
            privateKeyPath: $privateKeyPath,
            passphrase: $passphrase,
            algorithm: $algorithm,
            timezone: $timezone,
            expirationSeconds: $expirationSeconds
        );
    }

    /**
     * Encodes user ID and data into a signed JWT using the RSA algorithm.
     *
     * @param string                $userId The unique identifier of the user. This will be the 'sub' (subject) claim.
     * @param array<string, mixed>  $data   an associative array of data to include in the JWT payload
     * @param array<string, string> $header an optional associative array of headers to include in the JWT header
     *
     * @return JwtToken an object containing the generated JWT string and its expiration DateTimeImmutable
     *
     * @throws ExceptionInterface if an error occurs during token encoding
     */
    public function encode(string $userId, array $data, array $header = []): JwtToken
    {
        try {
            $issuedAt = new \DateTimeImmutable(timezone: new \DateTimeZone($this->timezone));
            $expiresAt = $issuedAt->modify("+{$this->expirationSeconds} seconds");

            $payload = array_merge([
                'iat' => $issuedAt->getTimestamp(), // Issued At
                'exp' => $expiresAt->getTimestamp(), // Expiration Time
                'sub' => $userId,                   // Subject (user ID)
            ], $data);

            $token = JWT::encode(
                payload: $payload,
                key: $this->getPrivateKey(),
                alg: $this->algorithm,
                head: $header
            );

            return JwtToken::create($token, $expiresAt);
        } catch (\Throwable $e) {
            throw HttpExceptionUtil::throw($e);
        }
    }

    /**
     * Decodes a JWT string and returns a TokenPayload object containing the decoded information.
     * It verifies the token's signature using the public key associated with the private key.
     *
     * @param string $token the JWT string to decode
     *
     * @return TokenPayload An object containing the decoded payload data, header, validation status,
     *                      and the user ID ('sub' claim) if the token is valid. If decoding fails, it returns
     *                      a TokenPayload object with isValid set to false and the error message.
     */
    public function decode(string $token): TokenPayload
    {
        try {
            /** @var array<string, \OpenSSLAsymmetricKey|\OpenSSLCertificate|resource|string> $privateKeyDetails */
            $privateKeyDetails = openssl_pkey_get_details($this->getPrivateKey());
            $publicKey = $privateKeyDetails['key'];
            $header = new \stdClass();

            $decoded = JWT::decode(
                jwt: $token,
                keyOrKeyArray: new Key($publicKey, $this->algorithm),
                headers: $header
            );

            return TokenPayload::create(
                data: (array)$decoded,
                header: (array)$header,
                isValid: true,
                userId: $decoded->sub
            );
        } catch (\Throwable $e) {
            return TokenPayload::create(
                data: [],
                header: [],
                isValid: false,
                error: $e->getMessage()
            );
        }
    }

    /**
     * Retrieves the private key resource from the configured file path.
     *
     * @return \OpenSSLAsymmetricKey the private key resource
     *
     * @throws RuntimeException if the private key file is unreadable or the private key is invalid
     */
    private function getPrivateKey(): \OpenSSLAsymmetricKey
    {
        $contents = @file_get_contents($this->privateKeyPath);

        if ($contents === false) {
            throw new RuntimeException([
                'message' => 'jwt.private_key.unreadable',
            ]);
        }

        $privateKey = openssl_pkey_get_private($contents, $this->passphrase);

        if (!$privateKey) {
            throw new RuntimeException([
                'message' => 'jwt.invalid.private_key',
            ]);
        }

        return $privateKey;
    }
}
