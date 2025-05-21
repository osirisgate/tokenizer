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
 * A trait providing functionality to save (persist) built-in token entities.
 *
 * This trait is designed to be used by classes that need to store or update
 * {@link Token} entities in a storage via a {@link TokenRepositoryInterface}
 * implementation. It encapsulates the dependency on the repository and provides
 * a simple method to perform the save operation.
 *
 * **Usage:** Classes using this trait must declare a private readonly property
 * `$tokenRepository` of type {@link TokenRepositoryInterface} and ensure it is
 * properly initialized (e.g., via constructor injection).
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
trait SaveTokenTokenTrait
{
    /**
     * @var TokenRepositoryInterface the repository used to interact with token data storage
     *
     * @readonly
     */
    private readonly TokenRepositoryInterface $tokenRepository;

    /**
     * Saves a given {@link Token} entity to the storage.
     *
     * This method uses the injected {@link TokenRepositoryInterface} to persist the
     * provided token. The repository implementation handles the underlying storage logic.
     *
     * @param Token $token the {@link Token} entity to be saved or updated
     */
    private function saveToken(Token $token): void
    {
        $this->tokenRepository->save($token);
    }
}
