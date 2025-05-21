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

namespace Osirisgate\Component\Tokenizer\BuildInToken\Repository\InMemory\DataMapper;

use Osirisgate\Component\Tokenizer\BuildInToken\Entity\Token;
use Osirisgate\Component\Tokenizer\BuildInToken\Repository\InMemory\Entity\TokenObject;

/**
 * A data mapper class responsible for converting between the domain entity
 * {@link Token} and the persistence object {@link TokenObject} used in the
 * in-memory repository. This class provides static methods for bidirectional
 * mapping.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 *
 * @final
 */
abstract class TokenDataMapper
{
    /**
     * Converts a {@link TokenObject} (persistence object) to a {@link Token} (domain entity).
     *
     * This method takes a {@link TokenObject} as input and extracts the necessary
     * data to create and return a new instance of the {@link Token} entity.
     *
     * @param TokenObject $tokenObject the persistence object to convert
     *
     * @return Token the resulting domain entity
     */
    public static function toDomain(TokenObject $tokenObject): Token
    {
        return Token::create(
            id: $tokenObject->getEntityId(),
            userId: $tokenObject->getUserId(),
            value: $tokenObject->getValue(),
            type: $tokenObject->getType(),
            expiresAt: $tokenObject->getExpiresAt()
        );
    }

    /**
     * Converts a {@link Token} (domain entity) to a {@link TokenObject} (persistence object).
     *
     * This method takes a {@link Token} entity as input and extracts the necessary
     * data to create and return a new instance of the {@link TokenObject} for storage.
     *
     * @param Token $token the domain entity to convert
     *
     * @return TokenObject the resulting persistence object
     */
    public static function toPersistence(Token $token): TokenObject
    {
        return TokenObject::create(
            entityId: (string)$token->id(),
            value: $token->value(),
            type: $token->type(),
            expiresAt: $token->expiresAt(),
            userId: $token->userId()
        );
    }
}
