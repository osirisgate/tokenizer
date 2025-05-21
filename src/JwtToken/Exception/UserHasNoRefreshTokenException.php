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

namespace Osirisgate\Component\Tokenizer\JwtToken\Exception;

use Osirisgate\Core\Exception\Exception;

/**
 * An exception class specifically for scenarios where an operation requiring a refresh token
 * is attempted for a user who does not have one associated with their account.
 *
 * This exception extends the base {@link Exception} class and is used
 * to indicate a specific business logic failure within the JWT refresh token management process.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class UserHasNoRefreshTokenException extends Exception
{
}
