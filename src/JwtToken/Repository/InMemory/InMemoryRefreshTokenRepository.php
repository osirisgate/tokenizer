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

namespace Osirisgate\Component\Tokenizer\JwtToken\Repository\InMemory;

use Osirisgate\Component\Tokenizer\JwtToken\Entity\RefreshToken\RefreshToken;
use Osirisgate\Component\Tokenizer\JwtToken\Repository\InMemory\DataMapper\RefreshTokenDataMapper;
use Osirisgate\Component\Tokenizer\JwtToken\Repository\InMemory\Entity\RefreshTokenObject;
use Osirisgate\Component\Tokenizer\JwtToken\Repository\RefreshTokenRepositoryInterface;
use Osirisgate\Core\Exception\RuntimeException;

/**
 * An in-memory implementation of the {@link RefreshTokenRepositoryInterface}.
 *
 * This repository stores {@link RefreshTokenObject} entities in a simple PHP array.
 * It is primarily intended for testing and development purposes where a persistent
 * storage mechanism is not required. It provides basic CRUD operations for
 * {@link RefreshToken} entities.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class InMemoryRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @var array<string, RefreshTokenObject> an associative array where the key is the refresh token ID
     *                                        and the value is the corresponding {@link RefreshTokenObject}
     */
    private array $refreshTokens = [];

    /**
     * Generates a unique ID using PHP's {@see uniqid()} function.
     *
     * @return string a unique string identifier
     */
    public function generateId(): string
    {
        return uniqid();
    }

    /**
     * Finds a {@link RefreshToken} entity by its value and the associated user ID.
     *
     * It iterates through the stored {@link RefreshTokenObject} entities and returns the
     * corresponding domain {@link RefreshToken} if a match is found. If no matching token
     * is found, it returns a "not found" {@link RefreshToken} entity with added error context.
     *
     * @param string $token  the refresh token value to search for
     * @param string $userId the unique identifier of the user associated with the token
     *
     * @return RefreshToken the retrieved {@link RefreshToken} entity, or a "not found" token if no match is found
     *
     * @throws RuntimeException
     */
    public function findByTokenAndUserId(string $token, string $userId): RefreshToken
    {
        foreach ($this->refreshTokens as $refreshTokenObject) {
            if (
                $refreshTokenObject->value() === $token
                && $refreshTokenObject->userId() === $userId
            ) {
                return RefreshTokenDataMapper::toDomain($refreshTokenObject);
            }
        }

        $unknowToken = RefreshToken::notFound();
        $unknowToken->addErrorContext('token', $token);
        $unknowToken->addErrorContext('userId', $userId);

        return $unknowToken;
    }

    /**
     * Saves a {@link RefreshToken} entity to the in-memory storage.
     *
     * It converts the domain {@link RefreshToken} entity to a persistence
     * {@link RefreshTokenObject} and stores it in the `$refreshTokens` array,
     * using the token's ID as the key.
     *
     * @param RefreshToken $refreshToken the {@link RefreshToken} entity to save
     */
    public function save(RefreshToken $refreshToken): void
    {
        $this->refreshTokens[(string)$refreshToken->getId()] = RefreshTokenDataMapper::toPersistence($refreshToken);
    }

    /**
     * Delete all refresh tokens associated with a given user ID from the in-memory storage.
     *
     * @param string $userId the unique identifier of the user whose refresh tokens should be deleted
     */
    public function delete(string $userId): void
    {
        foreach ($this->refreshTokens as $key => $refreshTokenObject) {
            if ($refreshTokenObject->userId() === $userId) {
                unset($this->refreshTokens[$key]);
            }
        }
    }

    /**
     * Updates an existing {@link RefreshToken} entity in the in-memory storage.
     *
     * This implementation simply calls the `save()` method as the in-memory storage
     * overwrites the existing entry if the ID already exists.
     *
     * @param RefreshToken $refreshToken the {@link RefreshToken} entity to update
     */
    public function update(RefreshToken $refreshToken): void
    {
        $this->save($refreshToken);
    }

    /**
     * Checks if a user has any refresh token associated with their account in the in-memory storage.
     *
     * @param string $userId the unique identifier of the user
     *
     * @return bool true if the user has at least one refresh token, false otherwise
     */
    public function userHasRefreshToken(string $userId): bool
    {
        return array_any($this->refreshTokens, fn (RefreshTokenObject $refreshToken) => $refreshToken->userId() === $userId);
    }

    /**
     * Returns the entire array of stored {@link RefreshTokenObject} entities.
     *
     * This method is primarily for testing and debugging purposes.
     *
     * @return array<string, RefreshTokenObject> the array of stored refresh token objects
     */
    public function getRefreshTokens(): array
    {
        return $this->refreshTokens;
    }

    /**
     * Finds a {@link RefreshToken} by its refresh token value.
     *
     * This method is not implemented in the in-memory repository.
     *
     * @param string $token the refresh token value to search for
     *
     * @return RefreshToken the corresponding {@link RefreshToken} entity if found, or a "not found" token
     * @throws RuntimeException
     */
    public function findByToken(string $token): RefreshToken
    {
        foreach ($this->refreshTokens as $refreshTokenObject) {
            if ($refreshTokenObject->value() === $token) {
                return RefreshTokenDataMapper::toDomain($refreshTokenObject);
            }
        }

        $unknowToken = RefreshToken::notFound();
        $unknowToken->addErrorContext('token_value', $token);

        return $unknowToken;
    }
}
