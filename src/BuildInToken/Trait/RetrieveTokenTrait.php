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

use Osirisgate\Component\Tokenizer\BuildInToken\Entity\Token;
use Osirisgate\Component\Tokenizer\BuildInToken\Repository\TokenRepositoryInterface;

/**
 * A trait providing functionality to retrieve built-in tokens based on user ID and token type.
 *
 * This trait is designed to be used by classes that need to fetch token entities
 * from a storage via a {@link TokenRepositoryInterface} implementation. It encapsulates
 * the dependency on the repository and provides a simple method to perform the retrieval.
 *
 * **Usage:** Classes using this trait must declare a private readonly property
 * `$tokenRepository` of type {@link TokenRepositoryInterface} and ensure it is
 * properly initialized (e.g., via constructor injection).
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
trait RetrieveTokenTrait
{
    /**
     * @var TokenRepositoryInterface the repository used to interact with token data storage
     *
     * @readonly
     */
    private readonly TokenRepositoryInterface $tokenRepository;

    /**
     * Retrieves a token entity based on the associated user ID and token type.
     *
     * This method uses the injected {@link TokenRepositoryInterface} to fetch the
     * token from the storage. The repository implementation is responsible for
     * handling cases where no matching token is found.
     *
     * @param string $userId    the unique identifier of the user who owns the token
     * @param string $tokenType The type of the token to retrieve (e.g., 'reset_password_token').
     *
     * @return Token the retrieved {@link Token} entity
     */
    private function findByUserIdAndTokenType(string $userId, string $tokenType): Token
    {
        return $this->tokenRepository->findByUserIdAndType($userId, $tokenType);
    }

    /**
     * Retrieves a token entity based on the associated user ID and token type.
     *
     * This method uses the injected {@link TokenRepositoryInterface} to fetch the
     * token from the storage. The repository implementation is responsible for
     * handling cases where no matching token is found.
     *
     * @param string $tokenValue the value of the token to retrieve
     * @param string $tokenType The type of the token to retrieve (e.g., 'reset_password_token').
     *
     * @return Token the retrieved {@link Token} entity
     */
    private function findByTokenTypeAndValue(string $tokenType, string $tokenValue): Token
    {
        return $this->tokenRepository->findByTypeAndValue($tokenType, $tokenValue);
    }
}
