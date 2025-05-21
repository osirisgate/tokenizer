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

namespace Osirisgate\Component\Tokenizer\BuildInToken\Exception;

use Osirisgate\Core\Exception\Exception;

/**
 * An exception class specifically for scenarios where an expired built-in token type is encountered.
 *
 * This exception extends the base {@link \Osirisgate\Core\Exception\Exception} class and is used
 * to indicate that a provided or expected token type is expired
 * the built-in token functionality. It includes details about the invalid type to aid in debugging.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class ExpiredTokenTypeException extends Exception
{
    /**
     * @var string the default error message for this exception
     */
    private const string EXPIRED_TOKEN_MESSAGE = 'expired.token.value';

    /**
     * Constructor for the `ExpiredTokenTypeException`.
     *
     * Accepts an array of details that can provide more context about the expired token value.
     * This information is included in the exception's details.
     *
     * @param array<string, mixed> $details an associative array containing details about the expired token value,
     */
    public function __construct(array $details)
    {
        parent::__construct([
            'message' => self::EXPIRED_TOKEN_MESSAGE,
            'details' => $details,
        ]);
    }
}
