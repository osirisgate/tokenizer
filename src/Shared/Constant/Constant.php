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

namespace Osirisgate\Component\Tokenizer\Shared\Constant;

/**
 * Defines various constants used within the Tokenizer component.
 *
 * This abstract class serves as a central repository for default values
 * and predefined settings related to token generation, expiration, and algorithms.
 * It prevents instantiation as it is intended to hold only constant values.
 *
 * @final
 */
abstract class Constant
{
    /**
     * @var int the default time-to-live (TTL) in seconds for refresh tokens (5 days)
     */
    public const int DEFAULT_REFRESH_TOKEN_TTL = 432000;

    /**
     * @var int the default length of generated refresh tokens in characters
     */
    public const int REFRESH_TOKEN_LENGTH = 32;

    /**
     * @var int The default length of built-in tokens (e.g., for internal processes) in characters.
     */
    public const int DEFAULT_BUILDIN_TOKEN_LENGTH = 32;

    /**
     * @var int the default time-to-live (TTL) in seconds for built-in tokens (1 hour)
     */
    public const int DEFAULT_BUILDIN_TTL = 3600;

    /**
     * @var int the default time-to-live (TTL) in seconds for JSON Web Tokens (JWTs) (1 hour)
     */
    public const int DEFAULT_JWT_TTL = 3600;

    /**
     * @var string the default algorithm used for signing JSON Web Tokens (JWTs) (RSA with SHA-256)
     */
    public const string DEFAULT_JWT_ALGORITHM = 'RS256';

    /**
     * @var string the default timezone used for setting expiration dates, which is Coordinated Universal Time (UTC)
     */
    public const string UTC_TIMEZONE = 'UTC';
}
