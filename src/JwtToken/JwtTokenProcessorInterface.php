<?php

declare(strict_types=1);

namespace Osirisgate\Component\Tokenizer\JwtToken;

use Osirisgate\Component\Tokenizer\JwtToken\Entity\JwtToken;
use Osirisgate\Component\Tokenizer\JwtToken\ValueObject\TokenPayload;

/**
 * Defines the contract for a service that can encode and decode JSON Web Tokens (JWTs).
 * This interface ensures that implementing classes provide the basic functionalities
 * for creating and interpreting JWTs.
 */
interface JwtTokenProcessorInterface
{
    /**
     * Encodes data into a JSON Web Token (JWT).
     *
     * @param string               $userId The unique identifier of the user for whom the token is being created.
     *                                     This is typically included as a claim in the JWT payload.
     * @param array<string, mixed> $data   an associative array of data to be included as claims in the JWT payload
     * @param array<string, mixed> $header an optional associative array of headers to be included in the JWT header
     *
     * @return JwtToken an object representing the generated JWT, which typically contains the raw token string
     */
    public function encode(string $userId, array $data, array $header = []): JwtToken;

    /**
     * Decodes a JSON Web Token (JWT) string into a TokenPayload object.
     * This process typically involves verifying the token's signature and extracting
     * the header and payload information.
     *
     * @param string $token the JWT string to decode
     *
     * @return TokenPayload an object containing the decoded information from the JWT,
     *                      including the header and payload as associative arrays, and potentially
     *                      methods to access specific claims like the user ID
     *
     * @throws \Exception If the token is invalid, cannot be decoded, or if the signature is invalid.
     *                    The specific exception type might vary depending on the underlying
     *                    JWT library used by the implementing class.
     */
    public function decode(string $token): TokenPayload;
}
