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

namespace Osirisgate\Component\Tokenizer\JwtToken\Trait;

use Osirisgate\Component\Tokenizer\JwtToken\Exception\InvalidJwtTokenException;
use Osirisgate\Component\Tokenizer\JwtToken\Exception\InvalidJwtTokenOwnerException;
use Osirisgate\Component\Tokenizer\JwtToken\ValueObject\TokenPayload;

/**
 * A trait providing methods for validating decoded JSON Web Tokens (JWTs).
 *
 * This trait offers functionalities to ensure the integrity, ownership, and
 * content (payload and header) of a decoded JWT. It throws specific exceptions
 * if the token is found to be invalid or doesn't match the expected criteria.
 */
trait JwtValidator
{
    /**
     * Validates a decoded JWT token against an expected user ID, payload, and header.
     *
     * This method performs several checks:
     * - Ensures the token was successfully decoded and is considered valid.
     * - Verifies that the user ID extracted from the token matches the expected $userId.
     * - Compares the token's payload against the $payload array to ensure all expected
     * keys exist and have the correct values. It also ensures no unexpected keys
     * are present in the provided payload for validation.
     * - Compares the token's header against the $header array (with a default 'typ' => 'JWT')
     * to ensure all expected keys exist and have the correct values.
     *
     * @param TokenPayload         $decodedJwtToken the object containing the decoded JWT information
     * @param string               $userId          the expected user ID associated with the token
     * @param array<string, mixed> $payload         the expected payload content for validation
     * @param array<string, mixed> $header          the expected header content for validation (optional)
     *
     * @throws InvalidJwtTokenOwnerException if the user ID in the token does not match the expected ID
     * @throws InvalidJwtTokenException      if the token is invalid, or if the payload or header
     *                                       do not match the expected content
     */
    private function validateDecodedJwtToken(TokenPayload $decodedJwtToken, string $userId, array $payload, array $header = []): void
    {
        if (!$decodedJwtToken->isValid()) {
            throw new InvalidJwtTokenException([
                'message' => 'invalid.jwt.token',
                'details' => [
                    'error' => $decodedJwtToken->error(),
                ],
            ]);
        }

        $this->assertTokenOwner($decodedJwtToken, $userId);
        $this->assertPayloadMatches($decodedJwtToken->data(), $payload);
        $this->assertHeaderMatches($decodedJwtToken->header(), $header);
    }

    /**
     * Asserts that the user ID extracted from the token's payload matches the expected $userId.
     *
     * @param TokenPayload $tokenPayload the object containing the decoded JWT information
     * @param string       $userId       the expected user ID
     *
     * @throws InvalidJwtTokenOwnerException if the user IDs do not match
     */
    private function assertTokenOwner(TokenPayload $tokenPayload, string $userId): void
    {
        if ($userId !== $tokenPayload->userId()) {
            throw new InvalidJwtTokenOwnerException([
                'message' => 'invalid.jwt.token.owner',
                'details' => [
                    'user_id' => $userId,
                ],
            ]);
        }
    }

    /**
     * Asserts that the user-provided $payloadToValidate exactly matches the decoded JWT's payload.
     * The decoded JWT's payload is the source of truth.
     *
     * @param array<string, mixed> $jwtPayload        the decoded payload from the JWT
     * @param array<string, mixed> $payloadToValidate the payload provided by the user for validation
     *
     * @throws InvalidJwtTokenException if a key in the $payloadToValidate is missing from the $jwtPayload
     *                                  or if the values for a key do not match, or if there are
     *                                  unexpected keys in the $payloadToValidate
     */
    private function assertPayloadMatches(array $jwtPayload, array $payloadToValidate): void
    {
        foreach ($payloadToValidate as $key => $value) {
            if (!array_key_exists($key, $jwtPayload)) {
                throw new InvalidJwtTokenException([
                    'message' => 'invalid.jwt.token.payload',
                    'details' => [
                        'error' => 'unexpected.key',
                        'key' => $key,
                    ],
                ]);
            }

            if ($jwtPayload[$key] !== $value) {
                throw new InvalidJwtTokenException([
                    'message' => 'invalid.jwt.token.payload',
                    'details' => [
                        'error' => 'invalid.value',
                        'key' => $key,
                        'value' => $value,
                    ],
                ]);
            }
        }

        // Ensure no extra keys are provided in $payloadToValidate that are not in the token
        if (count($payloadToValidate) !== count($jwtPayload)) {
            $diff = array_diff_key($payloadToValidate, $jwtPayload);
            if (count($diff) > 0) {
                throw new InvalidJwtTokenException([
                    'message' => 'invalid.jwt.token.payload',
                    'details' => [
                        'error' => 'unexpected_keys',
                        'provided_keys' => array_keys($diff),
                    ],
                ]);
            }
        }
    }

    /**
     * Asserts that the user-provided $headerToValidate exactly matches the decoded JWT's header
     * (including the default 'typ' => 'JWT'). The decoded JWT's header is the source of truth.
     *
     * @param array<string, mixed> $jwtHeader        the decoded header from the JWT
     * @param array<string, mixed> $headerToValidate the header provided by the user for validation
     *
     * @throws InvalidJwtTokenException if a key in the merged header (default + provided) is missing
     *                                  from the $jwtHeader or if the values for a key do not match
     */
    private function assertHeaderMatches(array $jwtHeader, array $headerToValidate): void
    {
        $headers = array_merge([
            'typ' => 'JWT',
        ], $headerToValidate);

        foreach ($headers as $key => $value) {
            if (!array_key_exists($key, $jwtHeader)) {
                throw new InvalidJwtTokenException([
                    'message' => 'invalid.jwt.token.header',
                    'details' => [
                        'error' => 'unexpected.key',
                        'key' => $key,
                    ],
                ]);
            }

            if ($jwtHeader[$key] !== $value) {
                throw new InvalidJwtTokenException([
                    'message' => 'invalid.jwt.token.header',
                    'details' => [
                        'error' => 'invalid.value',
                        'key' => $key,
                        'value' => $value,
                    ],
                ]);
            }
        }
    }
}
