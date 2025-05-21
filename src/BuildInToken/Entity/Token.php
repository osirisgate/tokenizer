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

namespace Osirisgate\Component\Tokenizer\BuildInToken\Entity;

use DateTimeInterface;
use Osirisgate\Component\Tokenizer\Shared\Entity\BaseEntity;

/**
 * Represents a built-in token entity within the Tokenizer component.
 *
 * This entity extends {@link BaseEntity} and stores information about a specific
 * token, including the user it belongs to, its value, expiration date, and type.
 * Built-in tokens are typically used for internal application purposes like
 * password reset or email verification.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class Token extends BaseEntity
{
    /**
     * @var string defines the default expiration time for a new token as one hour from the current time
     */
    private const string EXPIRE_DEFAULT_DATE_HOUR = '+1 hour';

    /**
     * @var string the unique identifier of the user associated with this token
     */
    private string $userId;

    /**
     * @var string the actual token value (the string representation of the token)
     */
    private string $value;

    /**
     * @var \DateTimeInterface the date and time when this token will expire
     */
    private \DateTimeInterface $expiresAt;

    /**
     * @var string The type of this token (e.g., 'password_reset', 'email_verification').
     */
    private string $tokenType;

    /**
     * Creates a new `Token` entity.
     *
     * If no expiration date is provided, it defaults to one hour from the time of creation.
     * The newly created token is automatically marked as valid.
     *
     * @param string                  $id        the unique identifier for this token
     * @param string                  $userId    the unique identifier of the user associated with this token
     * @param string                  $value     the string value of the token
     * @param string                  $type      the type of the token
     * @param \DateTimeInterface|null $expiresAt The expiration date and time for the token. If null, defaults to one hour from now.
     *
     * @return self the newly created and valid `Token` entity
     */
    public static function create(
        string $id,
        string $userId,
        string $value,
        string $type,
        ?\DateTimeInterface $expiresAt = null
    ): self {
        if ($expiresAt === null) {
            $expiresAt = new \DateTimeImmutable(self::EXPIRE_DEFAULT_DATE_HOUR);
        }

        $token = new self($id);
        $token->userId = $userId;
        $token->value = $value;
        $token->expiresAt = $expiresAt;
        $token->tokenType = $type;

        $token->markAsValid();

        return $token;
    }

    /**
     * Returns the unique identifier of the token.
     *
     * @return string|null the token's ID, inherited from {@link BaseEntity}
     */
    public function id(): ?string
    {
        return $this->id;
    }

    /**
     * Creates a special `Token` entity representing a "not found" state.
     *
     * This token is marked as invalid and has a null ID, which can be used
     * to easily identify cases where a token could not be retrieved.
     *
     * @return self an invalid `Token` entity representing a "not found" state
     */
    public static function notFound(): self
    {
        $token = new self();
        $token->markAsInvalid();

        return $token;
    }

    /**
     * Checks if this token entity represents a "not found" state.
     *
     * This is determined by whether the token's ID is null.
     *
     * @return bool true if the token is a "not found" entity, false otherwise
     */
    public function isNotValidEntity(): bool
    {
        return $this->id === null;
    }

    /**
     * Returns the unique identifier of the user associated with this token.
     *
     * @return string the user ID
     */
    public function userId(): string
    {
        return $this->userId;
    }

    /**
     * Returns the actual string value of the token.
     *
     * @return string the token value
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Returns the expiration date and time of the token as a `DateTimeInterface` object.
     *
     * @return \DateTimeInterface the expiration date and time
     */
    public function expiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * Returns the expiration date and time of the token formatted as a string ('Y-m-d H:i:s e (P)').
     *
     * @return string the formatted expiration date and time
     */
    public function formattedExpireAt(): string
    {
        return $this->expiresAt->format('Y-m-d H:i:s e (P)');
    }

    /**
     * Returns the type of the token.
     *
     * @return string the token type
     */
    public function type(): string
    {
        return $this->tokenType;
    }

    /**
     * Checks if the token has expired based on the current time.
     *
     * @return bool true if the token's expiration date is in the past, false otherwise
     */
    public function isExpired(): bool
    {
        return !$this->notExpired();
    }

    /**
     * Checks if the token's value matches a given token value.
     *
     * This also ensures that the token entity itself is valid before performing the comparison.
     *
     * @param string $tokenValue the token value to compare against
     *
     * @return bool true if the entity is valid and its value matches the given value, false otherwise
     */
    public function hasGivenValue(string $tokenValue): bool
    {
        return $this->isValidEntity() && $this->value() === $tokenValue;
    }

    /**
     * Checks if the token has not yet expired.
     *
     * This also ensures that the token entity is valid before checking the expiration date.
     *
     * @return bool true if the entity is valid and its expiration date is in the future, false otherwise
     */
    private function notExpired(): bool
    {
        return $this->isValidEntity() && new \DateTimeImmutable() < $this->expiresAt();
    }
}
