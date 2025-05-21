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

use Osirisgate\Core\Enum\StatusCode;
use Osirisgate\Core\Exception\Exception;

/**
 * An exception class specifically for scenarios where a JSON Web Token (JWT) is invalid due to an incorrect owner or subject.
 *
 * This exception extends the base {@link Exception} class and sets
 * the default HTTP status code to {@see StatusCode::UNAUTHORIZED}, as this typically indicates
 * an authorization failure where the token is not valid for the requested resource or action.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class InvalidJwtTokenOwnerException extends Exception
{
    /**
     * @var StatusCode the default HTTP status code associated with this exception (Unauthorized)
     */
    protected StatusCode $statusCode = StatusCode::UNAUTHORIZED;
}
