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

namespace Osirisgate\Component\Tokenizer\BuildInToken\Trait;

use DateTimeImmutable;
use Osirisgate\Component\Tokenizer\BuildInToken\Entity\Token;
use Osirisgate\Component\Tokenizer\BuildInToken\TokenGenerator;
use Osirisgate\Component\Tokenizer\Shared\Exception\HttpExceptionUtil;
use Osirisgate\Core\Exception\ExceptionInterface;
use Osirisgate\Core\Exception\RuntimeException;

/**
 * A trait providing utility methods for building, retrieving, and managing built-in tokens.
 *
 * This trait encapsulates the logic for creating new tokens, retrieving existing valid tokens,
 * and handling token expiration. It leverages the {@see DeleteTokenTrait},
 * {@see RetrieveTokenTrait}, and {@see SaveTokenTokenTrait} for repository interactions.
 *
 * **Usage:** Classes using this trait must also use the {@see DeleteTokenTrait},
 * {@see RetrieveTokenTrait}, and {@see SaveTokenTokenTrait} to have access to the
 * necessary repository interaction methods. Additionally, they must have access to a
 * `$tokenGenerator` object capable of generating random token values.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
trait TokenBuilderTrait
{
    use DeleteTokenTrait;
    use RetrieveTokenTrait;
    use SaveTokenTokenTrait;

    /**
     * @var TokenGenerator An object (e.g., a service) responsible for generating random token values (both regular and OTP).
     *                     Must have methods `generate(int $length): string` and `generateOtp(int $length): string`.
     */
    private readonly TokenGenerator $tokenGenerator;

    /**
     * Retrieves an existing valid token for the given user and type, or creates a new one if none exists or is expired.
     *
     * @param string $userId    the unique identifier of the user
     * @param string $tokenType the type of the token to retrieve or create
     * @param int    $length    the desired length of the token value
     * @param int    $tokenTtl  the time-to-live (in seconds) for the new token
     * @param string $timezone  the timezone to use for setting the expiration date
     * @param bool   $isOtp     whether the token should be an OTP (One-Time Password)
     *
     * @return Token the retrieved or newly created {@link Token} entity
     *
     * @throws RuntimeException          if an error occurs during token generation or retrieval
     * @throws ExceptionInterface        if an unexpected exception occurs
     */
    private function getOrCreateToken(
        string $userId,
        string $tokenType,
        int $length,
        int $tokenTtl,
        string $timezone,
        bool $isOtp,
    ): Token {
        $token = $this->findTokenIfAlreadyExists(
            userId: $userId,
            tokenType: $tokenType
        );

        if ($token !== null) {
            return $token;
        }

        return $this->createNewToken(
            userId: $userId,
            tokenType: $tokenType,
            length: $length,
            tokenTtl: $tokenTtl,
            timezone: $timezone,
            isOtp: $isOtp
        );
    }

    /**
     * Creates a new token for the given user and type, and persists it.
     *
     * @param string $userId    the unique identifier of the user
     * @param string $tokenType the type of the token to create
     * @param int    $length    the desired length of the token value
     * @param int    $tokenTtl  the time-to-live (in seconds) for the new token
     * @param string $timezone  the timezone to use for setting the expiration date
     * @param bool   $isOtp     whether the token should be an OTP (One-Time Password)
     *
     * @return Token the newly created and saved {@link Token} entity
     *
     * @throws RuntimeException          if an error occurs during token generation
     * @throws ExceptionInterface        if an unexpected exception occurs
     */
    private function createNewToken(
        string $userId,
        string $tokenType,
        int $length,
        int $tokenTtl,
        string $timezone,
        bool $isOtp,
    ): Token {
        $generatedToken = $this->generateToken(
            length: $length,
            tokenTtl: $tokenTtl,
            timezone: $timezone,
            isOtp: $isOtp
        );

        /** @var string $value */
        $value = $generatedToken['value'];

        /** @var \DateTimeInterface|null $expiresAt */
        $expiresAt = $generatedToken['expires_at'];

        $token = Token::create(
            id: $this->tokenRepository->generateId(),
            userId: $userId,
            value: $value,
            type: $tokenType,
            expiresAt: $expiresAt
        );

        $this->saveToken($token);

        return $token;
    }

    /**
     * Generates a random token value and its expiration timestamp.
     *
     * @param int    $length   the desired length of the token
     * @param int    $tokenTtl the time-to-live (in seconds) for the token
     * @param string $timezone the timezone to use for calculating the expiration date
     * @param bool   $isOtp    whether to generate an OTP or a regular random string
     *
     * @return array<string, string|\DateTimeInterface> an associative array containing the 'value' (string) and 'expires_at' (DateTimeInterface)
     *
     * @throws RuntimeException   if an error occurs during token generation
     * @throws ExceptionInterface if an unexpected exception occurs during token generation
     */
    private function generateToken(
        int $length,
        int $tokenTtl,
        string $timezone,
        bool $isOtp,
    ): array {
        try {
            return [
                'value' => $isOtp
                    ? $this->tokenGenerator->generateOtp($length)
                    : $this->tokenGenerator->generate($length),
                'expires_at' => self::secondsToDateTimeImmutable(
                    tokenTtl: $tokenTtl,
                    timezone: $timezone
                ),
            ];
        } catch (\Throwable $throwable) {
            throw HttpExceptionUtil::throw($throwable);
        }
    }

    /**
     * Retrieves an existing token if it's valid (not expired).
     *
     * @param string $userId    the unique identifier of the user
     * @param string $tokenType the type of the token to retrieve
     *
     * @return ?Token the valid {@link Token} entity if found, otherwise null
     */
    private function findTokenIfAlreadyExists(string $userId, string $tokenType): ?Token
    {
        $existingToken = $this->findByUserIdAndTokenType($userId, $tokenType);

        if (!$existingToken->isValidEntity() || $this->isExpiredToken($existingToken)) {
            return null;
        }

        return $existingToken;
    }

    /**
     * Checks if a token is expired and Delete it if it is.
     *
     * @param Token $token the {@link Token} entity to check
     *
     * @return bool true if the token was expired and deleted, false otherwise
     */
    private function isExpiredToken(Token $token): bool
    {
        if ($token->isExpired()) {
            $this->deleteById((string)$token->id());

            return true;
        }

        return false;
    }

    /**
     * Delete a token if its value matches a given value.
     *
     * @param Token  $token      the {@link Token} entity to check
     * @param string $tokenValue the value to compare against
     * @param bool $deleteIfValid whether to delete the token if it is valid (default: true)
     *
     * @return bool true if the token's value matched and it was deleted, false otherwise
     */
    private function deleteTokenIfHasGivenValue(Token $token, string $tokenValue, bool $deleteIfValid = true): bool
    {
        if ($token->hasGivenValue($tokenValue)) {
            if ($deleteIfValid) {
                $this->deleteById((string)$token->id());
            }

            return true;
        }

        return false;
    }

    /**
     * Converts a time-to-live in seconds to a {@link DateTimeImmutable} object representing the expiration time.
     *
     * @param int    $tokenTtl the time-to-live in seconds
     * @param string $timezone the timezone to use for the DateTimeImmutable object
     *
     * @return \DateTimeImmutable the calculated expiration date and time
     *
     * @throws \DateInvalidTimeZoneException if the provided timezone is invalid
     * @throws \Exception                    if an error occurs during DateTimeImmutable creation
     */
    private static function secondsToDateTimeImmutable(int $tokenTtl, string $timezone): \DateTimeImmutable
    {
        $now = new \DateTimeImmutable(timezone: new \DateTimeZone($timezone));

        return $now->add(new \DateInterval('PT' . $tokenTtl . 'S'));
    }
}
