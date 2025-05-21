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

namespace Osirisgate\Component\Tokenizer\BuildInToken\Enum;

use Osirisgate\Core\Enum\StatusCode;
use Osirisgate\Core\Exception\RuntimeException;

/**
 * An enumeration (`enum`) defining the different types of built-in tokens used
 * within the Tokenizer component. Each case represents a specific purpose for
 * which a token might be generated.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
enum TokenType: string
{
    /**
     * Represents a token used for the process of resetting a user's password.
     */
    case RESET_PASSWORD_TOKEN = 'reset_password_token';

    /**
     * Represents a token used for verifying a user's email address, typically after registration.
     */
    case EMAIL_VERIFICATION_TOKEN = 'email_verification_token';

    /**
     * Represents a one-time password code, often used for two-factor authentication.
     */
    case OTP_CODE = 'otp_code';

    /**
     * Returns a human-readable description for each token type.
     *
     * @return string a descriptive string explaining the purpose of the token type
     */
    public function description(): string
    {
        return match ($this) {
            self::RESET_PASSWORD_TOKEN => 'Token used for resetting user password.',
            self::EMAIL_VERIFICATION_TOKEN => 'Token used to validate user email address after registration.',
            self::OTP_CODE => 'One-time password code used for two-factor authentication.',
        };
    }

    /**
     * Creates a `TokenType` enum instance from a given string value.
     *
     * This method attempts to match the input string with the raw values of the
     * enum cases. If no match is found, it throws a {@link RuntimeException}
     * indicating an invalid token type.
     *
     * @param string $tokenType the string representation of the token type
     *
     * @return self the corresponding `TokenType` enum instance
     *
     * @throws RuntimeException if the provided `$tokenType` string does not match any of the defined enum cases
     */
    public function fromString(string $tokenType): self
    {
        return match ($tokenType) {
            'reset_password_token' => self::RESET_PASSWORD_TOKEN,
            'email_verification_token' => self::EMAIL_VERIFICATION_TOKEN,
            'otp_code' => self::OTP_CODE,
            default => throw new RuntimeException([
                'message' => 'invalid.token.type.',
                'code' => StatusCode::BAD_REQUEST->getValue(),
                'data' => [
                    'token_type' => $tokenType,
                    'available_types' => self::values(),
                ],
            ]),
        };
    }

    /**
     * Returns an array containing the raw string values of all `TokenType` enum cases.
     *
     * This can be useful for providing a list of valid token types.
     *
     * @return string[] an array of strings, where each string is the raw value of a `TokenType` case
     */
    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
