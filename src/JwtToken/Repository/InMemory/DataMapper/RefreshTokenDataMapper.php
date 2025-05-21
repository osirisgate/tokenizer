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

namespace Osirisgate\Component\Tokenizer\JwtToken\Repository\InMemory\DataMapper;

use Osirisgate\Component\Tokenizer\JwtToken\Entity\RefreshToken\RefreshToken;
use Osirisgate\Component\Tokenizer\JwtToken\Repository\InMemory\Entity\RefreshTokenObject;
use Osirisgate\Core\Exception\RuntimeException;

/**
 * A data mapper class responsible for converting between the domain entity
 * {@link RefreshToken} and the persistence object {@link RefreshTokenObject} used
 * in the in-memory repository. This class provides static methods for bidirectional
 * mapping.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 *
 * @final
 */
abstract class RefreshTokenDataMapper
{
    /**
     * Converts a {@link RefreshTokenObject} (persistence object) to a {@link RefreshToken} (domain entity).
     *
     * This method takes a {@link RefreshTokenObject} as input and extracts the necessary
     * data to create and return a new instance of the {@link RefreshToken} entity using
     * the `hydrate()` method.
     *
     * @param RefreshTokenObject $refreshTokenObject the persistence object to convert
     *
     * @return RefreshToken the resulting domain entity
     *
     * @throws RuntimeException if there is an issue during the hydration process
     */
    public static function toDomain(RefreshTokenObject $refreshTokenObject): RefreshToken
    {
        return RefreshToken::hydrate(
            id: $refreshTokenObject->getId(),
            userId: $refreshTokenObject->userId(),
            value: $refreshTokenObject->value(),
            expiresAt: $refreshTokenObject->expiresAt()
        );
    }

    /**
     * Converts a {@link RefreshToken} (domain entity) to a {@link RefreshTokenObject} (persistence object).
     *
     * This method takes a {@link RefreshToken} entity as input and extracts the necessary
     * data to create and return a new instance of the {@link RefreshTokenObject} for storage.
     *
     * @param RefreshToken $refreshToken the domain entity to convert
     *
     * @return RefreshTokenObject the resulting persistence object
     */
    public static function toPersistence(RefreshToken $refreshToken): RefreshTokenObject
    {
        return RefreshTokenObject::create(
            id: (string)$refreshToken->getId(),
            userId: $refreshToken->userId(),
            token: $refreshToken->value(),
            expiresAt: $refreshToken->expiresAt()
        );
    }
}
