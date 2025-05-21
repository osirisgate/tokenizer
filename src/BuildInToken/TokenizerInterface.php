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

use Osirisgate\Component\Tokenizer\BuildInToken\Entity\Token;
use Osirisgate\Component\Tokenizer\Shared\Constant\Constant;

/**
 * Defines the contract for a service that manages the generation and validation
 * of built-in tokens (e.g., for password reset, email verification).
 *
 * Implementations of this interface will provide methods to create new tokens
 * associated with a user and a specific type, and to verify if a given token
 * is valid for a particular user and type.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
interface TokenizerInterface
{
    /**
     * Generates a new token for a given user and token type.
     *
     * This method should handle the creation of a unique token value, associate it
     * with the specified user and token type, set an expiration time, and persist
     * the token in a storage mechanism.
     *
     * @param string $userId    the unique identifier of the user for whom to generate the token
     * @param string $tokenType The type of the token to generate (e.g., 'reset_password_token').
     * @param int    $length    the desired length of the generated token value (default: {@see Constant::DEFAULT_BUILDIN_TOKEN_LENGTH})
     * @param int    $tokenTtl  the time-to-live (in seconds) for the generated token (default: {@see Constant::DEFAULT_BUILDIN_TTL})
     * @param string $timezone  the timezone to use for setting the expiration date (default: 'UTC')
     * @param bool   $isOtp     whether to generate a One-Time Password (OTP) instead of a regular token (default: false)
     *
     * @return array<string, string> an associative array containing the generated token information,
     *                               typically including the 'token' value
     */
    public function generate(
        string $userId,
        string $tokenType,
        int $length = Constant::DEFAULT_BUILDIN_TOKEN_LENGTH,
        int $tokenTtl = Constant::DEFAULT_BUILDIN_TTL,
        string $timezone = Constant::UTC_TIMEZONE,
        bool $isOtp = false,
    ): array;

    /**
     * Checks if a given token value is valid for a specific user and token type.
     *
     * This method should retrieve the token associated with the user and type,
     * verify if the provided token value matches, and ensure that the token has not expired.
     *
     * @param string $userId     the unique identifier of the user
     * @param string $tokenType  the type of the token to validate
     * @param string $tokenValue the token value to check
     * @param bool   $deleteIfValid whether to delete the token if it is valid (default: true)
     *
     * @return bool true if the token is valid for the user and type, false otherwise
     */
    public function isValidForUser(string $userId, string $tokenType, string $tokenValue, bool $deleteIfValid = true): bool;

    /**
     * Checks if a given token value is valid for a specific token type.
     *
     * This method should retrieve the token associated with the type,
     * verify if the provided token value matches, and ensure that the token has not expired.
     *
     * @param string $tokenType  the type of the token to validate
     * @param string $tokenValue the token value to check
     * @param bool   $deleteIfValid whether to delete the token if it is valid (default: true)
     *
     * @return bool true if the token is valid for the type, false otherwise
     */
    public function isValid(string $tokenType, string $tokenValue, bool $deleteIfValid = true): bool;

    /**
     * Delete a token by its unique value.
     *
     * This method should remove the token from the storage based on its value.
     *
     * @param string $tokenType the unique type of the token to delete
     * @param string $tokenValue the unique value of the token to delete
     */
    public function delete(string $tokenType, string $tokenValue): void;

    /**
     * Retrieves a token by its type and value.
     *
     * This method should return the token object if it exists and is valid,
     * or throw an exception if the token is not found or has expired.
     *
     * @param string $tokenType  the type of the token to retrieve
     * @param string $tokenValue the value of the token to retrieve
     *
     * @return Token the retrieved token object
     */
    public function findBy(string $tokenType, string $tokenValue): Token;
}
