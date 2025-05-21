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

namespace Osirisgate\Component\Tokenizer\JwtToken\Entity\RefreshToken;

use Osirisgate\Component\Tokenizer\JwtToken\Generator\RefreshTokenGenerator;
use Osirisgate\Component\Tokenizer\JwtToken\ValueObject\RefreshTokenValue;
use Osirisgate\Component\Tokenizer\Shared\Entity\BaseEntity;
use Osirisgate\Core\Exception\ExceptionInterface;
use Osirisgate\Core\Exception\RuntimeException;

/**
 * Represents a refresh token entity.
 *
 * Refresh tokens are used to obtain new access tokens without requiring the user
 * to re-authenticate fully. This entity stores the user ID associated with the
 * refresh token, the token's value, and its expiration date.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class RefreshToken extends BaseEntity
{
    /**
     * @var string the unique identifier of the user associated with this refresh token
     */
    private string $userId;

    /**
     * @var RefreshTokenValue an encapsulated object holding the actual refresh token value
     */
    private RefreshTokenValue $value;

    /**
     * @var \DateTimeInterface the date and time when this refresh token expires
     */
    private \DateTimeInterface $expiresAt;

    /**
     * Hydrates a `RefreshToken` entity from existing data (e.g., from storage).
     *
     * @param string|null        $id        the unique identifier of the refresh token
     * @param string             $userId    the unique identifier of the associated user
     * @param string             $value     the string value of the refresh token
     * @param \DateTimeInterface $expiresAt the expiration date and time of the refresh token
     *
     * @return self the hydrated `RefreshToken` entity
     *
     * @throws RuntimeException if there is an issue creating the {@link RefreshTokenValue}
     */
    public static function hydrate(
        ?string $id,
        string $userId,
        string $value,
        \DateTimeInterface $expiresAt
    ): self {
        $refreshToken = new self($id);
        $refreshToken->userId = $userId;
        $refreshToken->value = new RefreshTokenValue($value);
        $refreshToken->expiresAt = $expiresAt;
        $refreshToken->markAsValid();

        return $refreshToken;
    }

    /**
     * Creates a new `RefreshToken` entity.
     *
     * This method generates a new refresh token value and sets its expiration date based
     * on the provided parameters.
     *
     * @param string $id                the unique identifier for this refresh token
     * @param string $userId            the unique identifier of the user for whom to create the refresh token
     * @param int    $expirationSeconds the expiration time in seconds for the refresh token
     * @param string $timezone          the timezone to use for setting the expiration date
     * @param int    $length            the desired length of the refresh token value
     *
     * @return self the newly created and valid `RefreshToken` entity
     *
     * @throws ExceptionInterface if an error occurs during token generation
     */
    public static function create(
        string $id,
        string $userId,
        int $expirationSeconds,
        string $timezone,
        int $length
    ): self {
        $tokenData = RefreshTokenGenerator::generate(
            expirationInSeconds: $expirationSeconds,
            timezone: $timezone,
            length: $length,
        );

        /** @var RefreshTokenValue $token */
        $token = $tokenData['token'];
        /** @var \DateTimeInterface $expiresAt */
        $expiresAt = $tokenData['expires_at'];

        return self::hydrate(
            id: $id,
            userId: $userId,
            value: $token->value(),
            expiresAt: $expiresAt
        );
    }

    /**
     * Creates a special `RefreshToken` entity representing a "not found" state.
     *
     * This token is marked as invalid and can be used to indicate that a refresh
     * token for a given user was not found.
     *
     * @return self an invalid `RefreshToken` entity representing a "not found" state
     */
    public static function notFound(): self
    {
        $refreshToken = new self();
        $refreshToken->markAsInvalid();

        return $refreshToken;
    }

    /**
     * Returns the unique identifier of the user associated with this refresh token.
     *
     * @return string the user ID
     */
    public function userId(): string
    {
        return $this->userId;
    }

    /**
     * Returns the string value of the refresh token.
     *
     * @return string the refresh token value
     */
    public function value(): string
    {
        return $this->value->value();
    }

    /**
     * Returns the expiration date and time of the refresh token.
     *
     * @return \DateTimeInterface the expiration date and time
     */
    public function expiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }
}
