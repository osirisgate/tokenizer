<?php

/*
 * This file is part of the Osirisgate package.
 *
 * (c) Ulrich Geraud AHOGLA <developer@osirisgate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osirisgate\Component\Tokenizer\BuildInToken\Repository\InMemory\Entity;

/**
 * Represents the persistence object for a built-in token, used specifically
 * within the in-memory repository implementation. This class is a simple
 * container for the token's data, mirroring the structure of the domain
 * {@link \Osirisgate\Component\Tokenizer\BuildInToken\Entity\Token} entity
 * but intended for direct storage.
 *
 * It extends `\stdClass` for simplicity and direct property access.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class TokenObject extends \stdClass
{
    /**
     * @var string the unique identifier of the token entity
     */
    private string $entityId;

    /**
     * @var string the actual token value (the string representation)
     */
    private string $value;

    /**
     * @var string The type of the token (e.g., 'reset_password_token').
     */
    private string $type;

    /**
     * @var \DateTimeInterface the date and time when this token expires
     */
    private \DateTimeInterface $expiresAt;

    /**
     * @var string the unique identifier of the user associated with this token
     */
    private string $userId;

    /**
     * Private constructor to enforce the use of the static `create()` method.
     *
     * @param string             $entityId  the unique identifier of the token
     * @param string             $value     the token value
     * @param string             $type      the token type
     * @param \DateTimeInterface $expiresAt the expiration date and time
     * @param string             $userId    the ID of the associated user
     */
    private function __construct(
        string $entityId,
        string $value,
        string $type,
        \DateTimeInterface $expiresAt,
        string $userId
    ) {
        $this->entityId = $entityId;
        $this->value = $value;
        $this->type = $type;
        $this->expiresAt = $expiresAt;
        $this->userId = $userId;
    }

    /**
     * Static factory method to create a new `TokenObject` instance.
     *
     * @param string             $entityId  the unique identifier of the token
     * @param string             $value     the token value
     * @param string             $type      the token type
     * @param \DateTimeInterface $expiresAt the expiration date and time
     * @param string             $userId    the ID of the associated user
     *
     * @return self a new `TokenObject` instance
     */
    public static function create(
        string $entityId,
        string $value,
        string $type,
        \DateTimeInterface $expiresAt,
        string $userId
    ): self {
        return new self(
            entityId: $entityId,
            value: $value,
            type: $type,
            expiresAt: $expiresAt,
            userId: $userId
        );
    }

    /**
     * Returns the unique identifier of the token entity.
     *
     * @return string the entity ID
     */
    public function getEntityId(): string
    {
        return $this->entityId;
    }

    /**
     * Returns the token value.
     *
     * @return string the token value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Returns the type of the token.
     *
     * @return string the token type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the expiration date and time of the token.
     *
     * @return \DateTimeInterface the expiration date and time
     */
    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * Returns the unique identifier of the user associated with the token.
     *
     * @return string the user ID
     */
    public function getUserId(): string
    {
        return $this->userId;
    }
}
