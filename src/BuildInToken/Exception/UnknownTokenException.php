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
 * An exception class specifically for scenarios where a requested built-in token is not found or does not exist.
 *
 * This exception extends the base {@link Exception} class and is used
 * to indicate that an operation (e.g., retrieval, validation) was attempted on a token that could not be identified
 * within the system.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class UnknownTokenException extends Exception
{
    /**
     * @var string the default error message for this exception
     */
    private const string UNKNOWN_TOKEN_MESSAGE = 'unknown.token';

    /**
     * Constructor for the `UnknownTokenException`.
     *
     * Accepts an array of details that can provide more context about the expired token value.
     * This information is included in the exception's details.
     *
     * @param array<string, mixed> $details an associative array containing details about the expired token value,
     */
    public function __construct(array $details)
    {
        parent::__construct([
            'message' => self::UNKNOWN_TOKEN_MESSAGE,
            'details' => $details,
        ]);
    }
}
