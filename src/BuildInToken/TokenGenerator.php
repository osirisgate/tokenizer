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

namespace Osirisgate\Component\Tokenizer\BuildInToken;

use Random\RandomException;

/**
 * A utility class responsible for generating random token strings, including
 * both general-purpose tokens and One-Time Passwords (OTPs).
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class TokenGenerator
{
    /**
     * @var int the default length for OTPs if not explicitly specified
     */
    private const int DEFAULT_OTP_LENGTH = 6;

    /**
     * @var int the default length for general-purpose tokens if not explicitly specified
     */
    private const int DEFAULT_LENGTH = 32;

    /**
     * @var string the set of characters used for generating OTPs
     */
    private const string CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * Generates a random token string of a specified length.
     *
     * If no length is provided or if the provided length is non-positive, it defaults
     * to {@see self::DEFAULT_LENGTH}. The token is generated using cryptographically
     * secure random bytes and converted to a hexadecimal string.
     *
     * @param int $length The desired length of the token (in characters). Defaults to {@see self::DEFAULT_LENGTH}.
     *
     * @return string the generated random token string
     *
     * @throws RandomException if there is an issue with the underlying random number generator
     */
    public function generate(int $length = self::DEFAULT_LENGTH): string
    {
        if ($length <= 0) {
            $length = self::DEFAULT_LENGTH;
        }

        return substr(bin2hex(random_bytes(max(1, (int)ceil($length / 2)))), 0, $length);
    }

    /**
     * Generates a random One-Time Password (OTP) of a specified length.
     *
     * If no length is provided or if the provided length is non-positive, it defaults
     * to {@see self::DEFAULT_OTP_LENGTH}. The OTP is generated using characters
     * from the {@see self::CHARACTERS} constant.
     *
     * @param int $length The desired length of the OTP (in digits/characters). Defaults to {@see self::DEFAULT_OTP_LENGTH}.
     *
     * @return string the generated random OTP string
     *
     * @throws RandomException if there is an issue with the underlying random number generator
     */
    public function generateOtp(int $length = self::DEFAULT_OTP_LENGTH): string
    {
        if ($length <= 0) {
            $length = self::DEFAULT_OTP_LENGTH;
        }

        $charactersLength = strlen(self::CHARACTERS);
        $otp = '';

        for ($i = 0; $i < $length; $i++) {
            $otp .= self::CHARACTERS[random_int(0, $charactersLength - 1)];
        }

        return $otp;
    }
}
