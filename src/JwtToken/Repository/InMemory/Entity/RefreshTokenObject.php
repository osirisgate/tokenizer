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

namespace Osirisgate\Component\Tokenizer\JwtToken\Repository\InMemory\Entity;

/**
 * Represents the persistence object for a refresh token, used specifically
 * within the in-memory repository implementation. This class is a simple
 * container for the refresh token's data, mirroring the structure of the domain
 * {@link \Osirisgate\Component\Tokenizer\JwtToken\Entity\RefreshToken\RefreshToken}
 * entity but intended for direct storage.
 *
 * It extends `\stdClass` for simplicity and direct property access.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class RefreshTokenObject extends \stdClass
{
    /**
     * @var string the unique identifier of the refresh token entity
     */
    private string $id;

    /**
     * @var string the unique identifier of the user associated with this refresh token
     */
    private string $userId;

    /**
     * @var string the actual refresh token value (the string representation)
     */
    private string $token;

    /**
     * @var \DateTimeInterface the date and time when this refresh token expires
     */
    private \DateTimeInterface $expiresAt;

    /**
     * Constructor for the `RefreshTokenObject`.
     *
     * @param string             $id        the unique identifier of the refresh token
     * @param string             $userId    the ID of the associated user
     * @param string             $token     the refresh token value
     * @param \DateTimeInterface $expiresAt the expiration date and time
     */
    public function __construct(
        string $id,
        string $userId,
        string $token,
        \DateTimeInterface $expiresAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->token = $token;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Static factory method to create a new `RefreshTokenObject` instance.
     *
     * @param string             $id        the unique identifier of the refresh token
     * @param string             $userId    the ID of the associated user
     * @param string             $token     the refresh token value
     * @param \DateTimeInterface $expiresAt the expiration date and time
     *
     * @return self a new `RefreshTokenObject` instance
     */
    public static function create(
        string $id,
        string $userId,
        string $token,
        \DateTimeInterface $expiresAt
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            token: $token,
            expiresAt: $expiresAt
        );
    }

    /**
     * Returns the unique identifier of the refresh token entity.
     *
     * @return string the entity ID
     */
    public function getId(): string
    {
        return $this->id;
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
     * Returns the refresh token value.
     *
     * @return string the refresh token value
     */
    public function value(): string
    {
        return $this->token;
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
