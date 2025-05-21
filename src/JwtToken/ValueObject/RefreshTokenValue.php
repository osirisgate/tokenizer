<?php

/*
 * This file is part of the Osirisgate package.
 *
 * (c) Ulrich Geraud AHOGLA <developer@osirisgate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osirisgate\Component\Tokenizer\JwtToken\ValueObject;

use Osirisgate\Core\Exception\RuntimeException;

/**
 * Represents the value of a refresh token.
 *
 * This final class encapsulates the refresh token string value and provides
 * validation to ensure it adheres to the expected format (hexadecimal).
 * It throws a {@link RuntimeException} if an invalid format is detected during instantiation.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final readonly class RefreshTokenValue
{
    /**
     * Constructor for the `RefreshTokenValue`.
     *
     * Validates that the provided token value is a hexadecimal string. If not,
     * it throws a {@link RuntimeException}.
     *
     * @param string $value the refresh token string value
     *
     * @throws RuntimeException if the provided value is not a valid hexadecimal string
     */
    public function __construct(
        private string $value
    ) {
        if (!self::isValid($value)) {
            throw new RuntimeException([
                'message' => 'invalid.refresh_token.format',
                'details' => [
                    'value' => $value,
                ],
            ]);
        }
    }

    /**
     * Magic method to allow casting the `RefreshTokenValue` object to a string.
     *
     * @return string the underlying refresh token value
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Checks if a given string is a valid refresh token value (i.e., a hexadecimal string).
     *
     * @param string $value the string to validate
     *
     * @return bool true if the string is hexadecimal, false otherwise
     */
    public static function isValid(string $value): bool
    {
        return ctype_xdigit($value);
    }

    /**
     * Returns the underlying refresh token value.
     *
     * @return string the refresh token value
     */
    public function value(): string
    {
        return $this->value;
    }
}
