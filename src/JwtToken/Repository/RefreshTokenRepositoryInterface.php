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

namespace Osirisgate\Component\Tokenizer\JwtToken\Repository;

use Osirisgate\Component\Tokenizer\JwtToken\Entity\RefreshToken\RefreshToken;

/**
 * Defines the contract for a repository that manages {@link RefreshToken} entities.
 *
 * This interface specifies the methods that any concrete implementation of a
 * refresh token repository must provide. It outlines operations for checking
 * existence, finding, saving, deleting, and updating refresh tokens, specifically
 * in relation to user IDs.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
interface RefreshTokenRepositoryInterface
{
    /**
     * Generates a unique identifier for a new refresh token.
     *
     * Implementations should ensure that the generated ID is sufficiently unique
     * to avoid collisions within the refresh token storage.
     *
     * @return string the generated unique refresh token identifier
     */
    public function generateId(): string;

    /**
     * Finds a refresh token entity by its value and the associated user ID.
     *
     * Implementations should handle cases where no matching refresh token is found,
     * potentially by returning a specific "not found" entity or throwing an exception.
     *
     * @param string $userId the unique identifier of the user associated with the token
     * @param string $token  the refresh token value to search for
     *
     * @return RefreshToken The retrieved {@link RefreshToken} entity. If no token is found,
     *                      implementations may return a special "not found" {@link RefreshToken} or throw an exception.
     */
    public function findByTokenAndUserId(string $token, string $userId): RefreshToken;

    /**
     * Saves a refresh token entity to the repository.
     *
     * This method is used for creating new refresh tokens. Implementations should
     * handle the persistence logic according to the underlying storage mechanism.
     *
     * @param RefreshToken $refreshToken the {@link RefreshToken} entity to save
     */
    public function save(RefreshToken $refreshToken): void;

    /**
     * Delete all refresh tokens associated with a given user ID.
     *
     * @param string $userId the unique identifier of the user whose refresh tokens should be deleted
     */
    public function delete(string $userId): void;

    /**
     * Updates an existing refresh token entity in the repository.
     *
     * @param RefreshToken $refreshToken the {@link RefreshToken} entity to update
     */
    public function update(RefreshToken $refreshToken): void;

    /**
     * Checks if a user has any refresh token associated with their account.
     *
     * @param string $userId the unique identifier of the user
     *
     * @return bool true if the user has at least one refresh token, false otherwise
     */
    public function userHasRefreshToken(string $userId): bool;

    /**
     * Retrieves a token entity by its unique value.
     *
     * Implementations should handle cases where no matching token is found,
     * potentially by returning a specific "not found" entity or throwing an exception.
     *
     * @param string $token the unique value of the token to retrieve
     *
     * @return RefreshToken The retrieved {@link RefreshToken} entity. If no token is found, implementations
     *               may return a special "not found" {@link RefreshToken} or throw an exception.
     */
    public function findByToken(string $token): RefreshToken;
}
