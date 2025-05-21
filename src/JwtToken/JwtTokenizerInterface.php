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

namespace Osirisgate\Component\Tokenizer\JwtToken;

use Osirisgate\Component\Tokenizer\JwtToken\Entity\JwtToken;
use Osirisgate\Component\Tokenizer\JwtToken\Entity\RefreshToken\RefreshToken;
use Osirisgate\Component\Tokenizer\JwtToken\Exception\InvalidJwtTokenException;
use Osirisgate\Component\Tokenizer\JwtToken\Exception\InvalidJwtTokenOwnerException;
use Osirisgate\Component\Tokenizer\JwtToken\ValueObject\TokenPayload;

/**
 * Defines the contract for a service that manages the generation, revocation,
 * and validation of JSON Web Tokens (JWTs) and their associated refresh tokens.
 *
 * Implementations of this interface will provide methods to create new JWTs and
 * refresh tokens for a user, revoke existing JWTs (typically by invalidating
 * the associated refresh token), and validate the authenticity and content of a JWT.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
interface JwtTokenizerInterface
{
    /**
     * Generates a new JWT and a corresponding refresh token for a given user.
     *
     * The JWT's payload and header can be customized. The refresh token is associated
     * with the user and can be used to obtain new JWTs after the current one expires.
     *
     * @param string               $userId  the unique identifier of the user for whom to generate the tokens
     * @param array<string, mixed> $payload the data to include in the JWT payload
     * @param array<string, mixed> $header  Optional header parameters for the JWT (e.g., algorithm).
     *
     * @return array{token: JwtToken, refresh_token: RefreshToken} an associative array containing
     *                                                             the generated {@link JwtToken} and {@link RefreshToken} entities
     */
    public function generate(string $userId, array $payload, array $header = []): array;

    /**
     * Revokes the current JWT for a user by invalidating their associated refresh token.
     *
     * This action typically prevents the generation of new JWTs using the revoked refresh token,
     * effectively logging the user out or requiring them to re-authenticate.
     *
     * @param string $userId the unique identifier of the user whose JWT should be revoked
     */
    public function revoke(string $userId): void;

    /**
     * Validates a given JWT string for a specific user against an expected payload and header.
     *
     * This method should decode the JWT, verify its signature, check its expiration, and
     * compare its payload and header against the provided arrays. It should also ensure
     * that the JWT belongs to the specified user.
     *
     * @param string               $jwtToken the raw JWT string to validate
     * @param string               $userId   the unique identifier of the user who should own the JWT
     * @param array<string, mixed> $payload  the expected data in the JWT payload for validation
     * @param array<string, mixed> $header   the expected header parameters for validation
     *
     * @return TokenPayload an object containing the decoded JWT payload and header, along with
     *                      a validity status and any potential errors
     *
     * @throws InvalidJwtTokenException      If the JWT is invalid (e.g., expired, wrong signature).
     * @throws InvalidJwtTokenOwnerException if the JWT does not belong to the specified user
     */
    public function validate(string $jwtToken, string $userId, array $payload, array $header = []): TokenPayload;
}
