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

use Osirisgate\Component\Tokenizer\BuildInToken\Repository\TokenRepositoryInterface;

/**
 * A trait providing functionality to delete built-in tokens.
 *
 * This trait is designed to be used by classes that need to delete token entities
 * from a storage via a {@link TokenRepositoryInterface} implementation. It encapsulates
 * the dependency on the repository and provides a simple method to perform the deletion.
 *
 * **Usage:** Classes using this trait must declare a private readonly property
 * `$tokenRepository` of type {@link TokenRepositoryInterface} and ensure it is
 * properly initialized (e.g., via constructor injection).
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
trait DeleteTokenTrait
{
    /**
     * @var TokenRepositoryInterface the repository used to interact with token data storage
     *
     * @readonly
     */
    private readonly TokenRepositoryInterface $tokenRepository;

    /**
     * Delete a token from the storage based on its unique identifier.
     *
     * This method uses the injected {@link TokenRepositoryInterface} to perform the
     * deletion operation.
     *
     * @param string $tokenId the unique identifier of the token to be deleted
     */
    private function deleteById(string $tokenId): void
    {
        $this->tokenRepository->delete($tokenId);
    }

    /**
     * Delete a token from the storage based on its unique value.
     *
     * This method uses the injected {@link TokenRepositoryInterface} to perform the
     * deletion operation.
     *
     * @param string $tokenType the unique type of the token to be deleted
     * @param string $tokenValue the unique value of the token to be deleted
     */
    private function deleteTokenByTypeAndValue(string $tokenType, string $tokenValue): void
    {
        $this->tokenRepository->deleteByTypeAndValue($tokenType, $tokenValue);
    }
}
