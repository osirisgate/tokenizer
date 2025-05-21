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

namespace Osirisgate\Component\Tokenizer\BuildInToken\Repository\InMemory;

use Osirisgate\Component\Tokenizer\BuildInToken\Entity\Token;
use Osirisgate\Component\Tokenizer\BuildInToken\Repository\InMemory\DataMapper\TokenDataMapper;
use Osirisgate\Component\Tokenizer\BuildInToken\Repository\InMemory\Entity\TokenObject;
use Osirisgate\Component\Tokenizer\BuildInToken\Repository\TokenRepositoryInterface;

/**
 * An in-memory implementation of the {@link TokenRepositoryInterface}.
 *
 * This repository stores {@link TokenObject} entities in a simple PHP array. It is primarily
 * intended for testing and development purposes where a persistent storage mechanism is not required.
 * It provides basic CRUD operations for {@link Token} entities.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class InMemoryTokenRepository implements TokenRepositoryInterface
{
    /**
     * @var array<string, TokenObject> an associative array where the key is the token ID and the value is the corresponding {@link TokenObject}
     */
    private array $tokens = [];

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
     * Retrieves a {@link Token} entity by its associated user ID and token type.
     *
     * It iterates through the stored {@link TokenObject} entities and returns the
     * corresponding domain {@link Token} if a match is found. If no matching token
     * is found, it returns a "not found" {@link Token} entity with added error context.
     *
     * @param string $userId    the unique identifier of the user associated with the token
     * @param string $tokenType the type of the token to retrieve
     *
     * @return Token the retrieved {@link Token} entity, or a "not found" token if no match is found
     * checks the stored data, not the input type itself)
     */
    public function findByUserIdAndType(string $userId, string $tokenType): Token
    {
        foreach ($this->tokens as $tokenObject) {
            if (
                $tokenObject->getUserId() === $userId
                && $tokenObject->getType() === $tokenType
            ) {
                return TokenDataMapper::toDomain($tokenObject);
            }
        }

        $unknowToken = Token::notFound();
        $unknowToken->addErrorContext('userId', $userId);
        $unknowToken->addErrorContext('token_type', $tokenType);

        return $unknowToken;
    }

    /**
     * Saves a {@link Token} entity to the in-memory storage.
     *
     * It converts the domain {@link Token} entity to a persistence {@link TokenObject}
     * and stores it in the `$tokens` array, using the token's ID as the key.
     *
     * @param Token $token the {@link Token} entity to save
     */
    public function save(Token $token): void
    {
        $this->tokens[(string)$token->id()] = TokenDataMapper::toPersistence($token);
    }

    /**
     * Delete a token from the in-memory storage based on its ID.
     *
     * @param string $tokenId the unique identifier of the token to delete
     */
    public function delete(string $tokenId): void
    {
        unset($this->tokens[$tokenId]);
    }

    /**
     * Delete a token from the in-memory storage based on its ID.
     *
     * @param string $tokenType the unique type of the token to delete
     * @param string $tokenValue the unique identifier of the token to delete
     */
    public function deleteByTypeAndValue(string $tokenType, string $tokenValue): void
    {
        foreach ($this->tokens as $tokenId => $tokenObject) {
            if ($tokenObject->getType() === $tokenType && $tokenObject->getValue() === $tokenValue) {
                unset($this->tokens[$tokenId]);

                return;
            }
        }
    }

    /**
     * Checks if a token with the given value exists in the in-memory storage.
     *
     * @param string $tokenType the type of the token to check
     * @param string $tokenValue the value of the token to check
     *
     * @return bool true if a token with the given value exists, false otherwise
     */
    public function has(string $tokenType, string $tokenValue): bool
    {
        return array_any($this->tokens, fn ($tokenObject) => $tokenObject->getType() === $tokenType && $tokenObject->getValue() === $tokenValue);
    }

    /**
     * Returns the entire array of stored {@link TokenObject} entities.
     *
     * This method is primarily for testing and debugging purposes.
     *
     * @return array<string, TokenObject> the array of stored token objects
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * Adds a predefined {@link TokenObject} to the in-memory storage for testing purposes.
     *
     * This method directly manipulates the `$tokens` array by adding a specific
     * {@link TokenObject} with a hardcoded ID and data.
     */
    public function add(): void
    {
        $this->tokens['682a6d8516ccd'] = TokenObject::create(
            entityId: '682a6d8516ccd',
            value: 'VDQN3H4L11',
            type: 'email_verification_token',
            expiresAt: new \DateTimeImmutable('2024-05-19 02:30:13'),
            userId: 'jean_682a6d85169c6'
        );
    }

    /**
     * Retrieves a {@link Token} entity by its type and value.
     *
     * It searches through the stored {@link TokenObject} entities and returns the
     * corresponding domain {@link Token} if a match is found. If no matching token
     * is found, it returns a "not found" {@link Token} entity with added error context.
     *
     * @param string $tokenType  the type of the token to retrieve
     * @param string $tokenValue the value of the token to retrieve
     *
     * @return Token the retrieved {@link Token} entity, or a "not found" token if no match is found
     */
    public function findByTypeAndValue(string $tokenType, string $tokenValue): Token
    {
        foreach ($this->tokens as $tokenObject) {
            if (
                $tokenObject->getType() === $tokenType
                && $tokenObject->getValue() === $tokenValue
            ) {
                return TokenDataMapper::toDomain($tokenObject);
            }
        }

        $unknowToken = Token::notFound();
        $unknowToken->addErrorContext('token_type', $tokenType);
        $unknowToken->addErrorContext('token_value', $tokenValue);

        return $unknowToken;
    }
}
