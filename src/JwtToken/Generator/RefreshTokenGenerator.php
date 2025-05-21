<?php

/*
 * This file is part of the Osirisgate package.
 *
 * (c) Ulrich Geraud AHOGLA <developer@osirisgate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osirisgate\Component\Tokenizer\JwtToken\Generator;

use DateTimeInterface;
use Osirisgate\Component\Tokenizer\JwtToken\ValueObject\RefreshTokenValue;
use Osirisgate\Component\Tokenizer\Shared\Constant\Constant;
use Osirisgate\Component\Tokenizer\Shared\Exception\HttpExceptionUtil;
use Osirisgate\Core\Exception\ExceptionInterface;

/**
 * A utility class responsible for generating new refresh token values and their expiration dates.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class RefreshTokenGenerator
{
    /**
     * Generates a new refresh token value and its corresponding expiration date.
     *
     * The refresh token value is a random hexadecimal string. The expiration date is
     * calculated by adding the specified number of seconds to the current time in the
     * given timezone (defaults to UTC).
     *
     * @param int    $expirationInSeconds the number of seconds until the refresh token expires
     * @param string $timezone            the timezone to use for setting the expiration date (default: 'UTC')
     * @param int    $length              the desired length of the refresh token value in characters (default: {@see Constant::REFRESH_TOKEN_LENGTH})
     *
     * @return array{token: RefreshTokenValue, expires_at: \DateTimeInterface} an associative array containing the generated
     *                                                                         {@link RefreshTokenValue} under the 'token' key and the expiration {@link DateTimeInterface} under the 'expires_at' key
     *
     * @throws ExceptionInterface if there is an issue with the random number generation or date/time manipulation
     */
    public static function generate(
        int $expirationInSeconds,
        string $timezone = Constant::UTC_TIMEZONE,
        int $length = Constant::REFRESH_TOKEN_LENGTH
    ): array {
        try {
            $raw = bin2hex(random_bytes(max(1, intdiv($length, 2))));
            $token = new RefreshTokenValue($raw);
            $expiresAt = new \DateTimeImmutable(timezone: new \DateTimeZone($timezone))
                ->modify("+{$expirationInSeconds} seconds");

            return [
                'token' => $token,
                'expires_at' => $expiresAt,
            ];
        } catch (\Throwable $e) {
            throw HttpExceptionUtil::throw($e);
        }
    }
}
