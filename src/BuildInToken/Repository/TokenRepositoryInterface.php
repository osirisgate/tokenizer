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

namespace Osirisgate\Component\Tokenizer\BuildInToken\Repository;

use Osirisgate\Component\Tokenizer\BuildInToken\Entity\Token;

/**
 * Defines the contract for a repository that manages {@link Token} entities.
 *
 * This interface specifies the methods that any concrete implementation of a
 * built-in token repository must provide. It outlines the basic CRUD (Create, Read,
 * Update, Delete) operations and a method for generating unique token identifiers.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
interface TokenRepositoryInterface
{
    /**
     * Generates a unique identifier for a new token.
     *
     * Implementations should ensure that the generated ID is sufficiently unique
     * to avoid collisions within the token storage.
     *
     * @return string the generated unique token identifier
     */
    public function generateId(): string;

    /**
     * Retrieves a token entity based on the associated user ID and the token type.
     *
     * Implementations should handle cases where no matching token is found,
     * potentially by returning a specific "not found" entity or throwing an exception.
     *
     * @param string $userId    the unique identifier of the user who owns the token
     * @param string $tokenType The type of the token to retrieve (e.g., 'reset_password_token').
     *
     * @return Token The retrieved {@link Token} entity. If no token is found, implementations
     *               may return a special "not found" {@link Token} or throw an exception.
     */
    public function findByUserIdAndType(string $userId, string $tokenType): Token;

    /**
     * Saves a token entity to the repository.
     *
     * This method is used for both creating new tokens and updating existing ones.
     * Implementations should handle the persistence logic according to the underlying storage mechanism.
     *
     * @param Token $token the {@link Token} entity to save
     */
    public function save(Token $token): void;

    /**
     * Delete a token entity from the repository based on its unique identifier.
     *
     * @param string $tokenId the unique identifier of the token to delete
     */
    public function delete(string $tokenId): void;

    /**
     * Delete a token entity from the repository based on its unique value.
     *
     * @param string $tokenType the unique type of the token to delete
     * @param string $tokenValue the unique value of the token to delete
     */
    public function deleteByTypeAndValue(string $tokenType, string $tokenValue): void;

    /**
     * Retrieves a token entity based on its type and value.
     *
     * This method is useful for validating tokens by their value, such as when checking
     * a reset password token or an email verification token.
     *
     * @param string $tokenType  the type of the token to retrieve (e.g., 'reset_password_token')
     * @param string $tokenValue the value of the token to retrieve
     *
     * @return Token the retrieved {@link Token} entity, or null if no matching token is found
     */
    public function findByTypeAndValue(string $tokenType, string $tokenValue): Token;

    /**
     * Checks if a token with the given value exists in the repository.
     *
     * This method is used to verify the existence of a token before performing operations
     * such as deletion or validation.
     *
     * @param string $tokenType the type of the token to check for existence
     * @param string $tokenValue the value of the token to check for existence
     *
     * @return bool true if a token with the given value exists, false otherwise
     */
    public function has(string $tokenType, string $tokenValue): bool;
}
