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
use Osirisgate\Component\Tokenizer\BuildInToken\Exception\ExpiredTokenTypeException;
use Osirisgate\Component\Tokenizer\BuildInToken\Exception\UnknownTokenException;
use Osirisgate\Component\Tokenizer\BuildInToken\Repository\TokenRepositoryInterface;
use Osirisgate\Component\Tokenizer\BuildInToken\Trait\DeleteTokenTrait;
use Osirisgate\Component\Tokenizer\BuildInToken\Trait\TokenBuilderTrait;
use Osirisgate\Component\Tokenizer\Shared\Constant\Constant;
use Osirisgate\Core\Exception\ExceptionInterface;
use Osirisgate\Core\Exception\RuntimeException;

/**
 * A concrete implementation of the {@link TokenizerInterface} for managing built-in tokens.
 *
 * This service provides methods to generate and validate tokens for specific users and types,
 * such as password reset tokens or email verification tokens. It utilizes a {@link TokenRepositoryInterface}
 * for persistence and the {@link TokenBuilderTrait} for common token management logic.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 *
 * @readonly
 */
final readonly class TokenizerService implements TokenizerInterface
{
    use DeleteTokenTrait;
    use TokenBuilderTrait;

    /**
     * @var TokenGenerator an instance of the token generator service used to create random token values
     */
    private TokenGenerator $tokenGenerator;

    /**
     * Constructor for the `TokenizerService`.
     *
     * @param TokenRepositoryInterface $tokenRepository the repository for managing built-in token entities
     */
    public function __construct(
        private TokenRepositoryInterface $tokenRepository,
    ) {
        $this->tokenGenerator = new TokenGenerator();
    }

    /**
     * Generates a new token for a given user and token type, or retrieves an existing valid one.
     *
     * The generated token value and its expiration timestamp are returned in an array.
     *
     * @param string $userId    the unique identifier of the user
     * @param string $tokenType the type of the token to generate
     * @param int    $length    the desired length of the token value (default: {@see Constant::DEFAULT_BUILDIN_TOKEN_LENGTH})
     * @param int    $tokenTtl  the time-to-live (in seconds) for the new token (default: {@see Constant::DEFAULT_BUILDIN_TTL})
     * @param string $timezone  the timezone to use for setting the expiration date (default: {@see Constant::UTC_TIMEZONE})
     * @param bool   $isOtp     whether to generate a One-Time Password (OTP) (default: false)
     *
     * @return array<string, string> an associative array containing the generated token 'value' and its formatted 'expires_at' timestamp
     *
     * @throws RuntimeException          if an error occurs during token generation or retrieval
     * @throws ExceptionInterface        if an unexpected exception occurs
     */
    public function generate(
        string $userId,
        string $tokenType,
        int $length = Constant::DEFAULT_BUILDIN_TOKEN_LENGTH,
        int $tokenTtl = Constant::DEFAULT_BUILDIN_TTL,
        string $timezone = Constant::UTC_TIMEZONE,
        bool $isOtp = false,
    ): array {
        $token = $this->getOrCreateToken(
            userId: $userId,
            tokenType: $tokenType,
            length: $length,
            tokenTtl: $tokenTtl,
            timezone: $timezone,
            isOtp: $isOtp,
        );

        return [
            'value' => $token->value(),
            'expires_at' => $token->formattedExpireAt(),
        ];
    }

    /**
     * Checks if a given token value is valid for a specific user and token type.
     *
     * It retrieves the token, verifies if it exists and is not expired, and then
     * Delete the token if it's valid.
     *
     * @param string $userId     the unique identifier of the user
     * @param string $tokenType  the type of the token to validate
     * @param string $tokenValue the token value to check
     * @param bool   $deleteIfValid whether to delete the token if it is valid (default: true)
     *
     * @return bool true if the token is valid for the user and type, and has been deleted; false otherwise
     *
     * @throws UnknownTokenException|ExpiredTokenTypeException if no token is found for the given user and type
     */
    public function isValidForUser(string $userId, string $tokenType, string $tokenValue, bool $deleteIfValid = true): bool
    {
        $token = $this->findByUserIdAndTokenType($userId, $tokenType);

        $this->assertTokenIsKnown($token, $tokenType, $tokenValue);
        $this->assertTokenIsNotExpired($token, $tokenType, $tokenValue);

        return $this->deleteTokenIfHasGivenValue($token, $tokenValue, $deleteIfValid);
    }

    /**
     * @throws UnknownTokenException
     * @throws ExpiredTokenTypeException
     */
    public function isValid(string $tokenType, string $tokenValue, bool $deleteIfValid = true): bool
    {
        $token = $this->findByTokenTypeAndValue($tokenType, $tokenValue);

        $this->assertTokenIsKnown($token, $tokenType, $tokenValue);
        $this->assertTokenIsNotExpired($token, $tokenType, $tokenValue);

        return $this->deleteTokenIfHasGivenValue($token, $tokenValue, $deleteIfValid);
    }

    /**
     * Delete a token by its unique value.
     *
     * This method removes the token from the repository based on its value.
     *
     * @param string $tokenType the unique type of the token to delete
     * @param string $tokenValue the unique value of the token to delete
     * @throws UnknownTokenException
     */
    public function delete(string $tokenType, string $tokenValue): void
    {
        if ($this->tokenRepository->has($tokenType, $tokenValue)) {
            $this->deleteTokenByTypeAndValue($tokenType, $tokenValue);

            return;
        }

        throw new UnknownTokenException($this->buildTokenDetails($tokenType, $tokenValue));
    }

    /**
     * Retrieves a token by its type and value.
     *
     * This method fetches the token entity from the repository and checks if it exists
     * and is not expired. If the token is not found or has expired, appropriate exceptions
     * are thrown.
     *
     * @param string $tokenType the type of the token to retrieve
     * @param string $tokenValue the value of the token to retrieve
     *
     * @return Token the retrieved token object
     * @throws UnknownTokenException
     */
    public function findBy(string $tokenType, string $tokenValue): Token
    {
        $token = $this->tokenRepository->findByTypeAndValue($tokenType, $tokenValue);

        $this->assertTokenIsKnown($token, $tokenType, $tokenValue);

        return $token;
    }

    /**
     * Asserts that a retrieved token entity is not a "not found" entity.
     *
     * @param Token  $token      the retrieved {@link Token} entity
     * @param string $tokenType  the type of the token being checked
     * @param string $tokenValue the value of the token being checked
     *
     * @throws UnknownTokenException if the token entity indicates that no token was found
     */
    private function assertTokenIsKnown(Token $token, string $tokenType, string $tokenValue): void
    {
        if ($token->isNotValidEntity()) {
            throw new UnknownTokenException($this->buildTokenDetails($tokenType, $tokenValue));
        }
    }

    /**
     * Asserts that a given token entity has not expired.
     *
     * @param Token  $token      the retrieved {@link Token} entity
     * @param string $tokenType  the type of the token being checked
     * @param string $tokenValue the value of the token being checked
     *
     * @throws ExpiredTokenTypeException if the token entity indicates that the token has expired
     */
    private function assertTokenIsNotExpired(Token $token, string $tokenType, string $tokenValue): void
    {
        if ($token->isExpired()) {
            throw new ExpiredTokenTypeException($this->buildTokenDetails($tokenType, $tokenValue));
        }
    }

    /**
     * Builds an array of details to include in token-related exception messages.
     *
     * @param string $tokenType  the type of the token
     * @param string $tokenValue the value of the token
     *
     * @return array<string, array<string, string>> an associative array containing token details
     */
    private function buildTokenDetails(string $tokenType, string $tokenValue): array
    {
        return [
            'token' => [
                'type' => $tokenType,
                'value' => $tokenValue,
            ],
        ];
    }
}
