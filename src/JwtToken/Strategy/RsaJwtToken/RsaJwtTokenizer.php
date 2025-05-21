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

namespace Osirisgate\Component\Tokenizer\JwtToken\Strategy\RsaJwtToken;

use Osirisgate\Component\Tokenizer\JwtToken\Entity\JwtToken;
use Osirisgate\Component\Tokenizer\JwtToken\Entity\RefreshToken\RefreshToken;
use Osirisgate\Component\Tokenizer\JwtToken\Exception\InvalidJwtTokenException;
use Osirisgate\Component\Tokenizer\JwtToken\Exception\InvalidJwtTokenOwnerException;
use Osirisgate\Component\Tokenizer\JwtToken\Exception\UserHasNoRefreshTokenException;
use Osirisgate\Component\Tokenizer\JwtToken\JwtTokenizerInterface;
use Osirisgate\Component\Tokenizer\JwtToken\Repository\RefreshTokenRepositoryInterface;
use Osirisgate\Component\Tokenizer\JwtToken\Trait\JwtValidator;
use Osirisgate\Component\Tokenizer\JwtToken\ValueObject\TokenPayload;
use Osirisgate\Component\Tokenizer\Shared\Constant\Constant;
use Osirisgate\Core\Exception\ExceptionInterface;

/**
 * A concrete implementation of the {@link JwtTokenizerInterface} that utilizes RSA (Rivest–Shamir–Adleman)
 * cryptography for signing and verifying JSON Web Tokens (JWTs). This tokenizer also manages
 * refresh tokens for users.
 *
 * It leverages the {@link RsaJwtTokenProcessor} for the underlying JWT encoding and decoding
 * operations. The class enforces the use of RSA algorithms for token generation and validation.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 *
 * @readonly
 */
final readonly class RsaJwtTokenizer implements JwtTokenizerInterface
{
    use JwtValidator;

    /**
     * @var RsaJwtTokenProcessor the processor responsible for encoding and decoding RSA-based JWTs
     */
    private RsaJwtTokenProcessor $jwtToken;

    /**
     * @var string the default timezone used for token expiration dates
     */
    private string $timezone;

    /**
     * @var int the default expiration time in seconds for refresh tokens
     */
    private int $jwtRefreshExpirationTime;

    /**
     * @var int the default length of generated refresh tokens
     */
    private int $refreshTokenLength;

    /**
     * @var string The JWT algorithm used for signing the tokens (e.g., RS256).
     */
    private string $jwtTokenAlgorithm;

    /**
     * Constructor for the `RsaJwtTokenizer` class.
     *
     * Initializes the tokenizer with necessary dependencies and configuration parameters for
     * RSA-based JWT handling and refresh token management.
     *
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository   the repository for managing refresh tokens
     * @param string                          $jwtPrivateKeyFile        the path to the private key file used for signing JWTs
     * @param string                          $jwtPassphrase            the passphrase for the private key, if any
     * @param string                          $jwtTokenAlgorithm        the JWT algorithm to use for signing (default: {@see Constant::DEFAULT_JWT_ALGORITHM})
     * @param int                             $jwtExpirationSeconds     the expiration time in seconds for generated JWTs (default: {@see Constant::DEFAULT_JWT_TTL})
     * @param int                             $jwtRefreshExpirationTime the expiration time in seconds for generated refresh tokens (default: {@see Constant::DEFAULT_REFRESH_TOKEN_TTL})
     * @param int                             $refreshTokenLength       the length of generated refresh tokens (default: {@see Constant::REFRESH_TOKEN_LENGTH})
     * @param string                          $timezone                 the timezone to use for setting expiration dates (default: {@see Constant::UTC_TIMEZONE})
     */
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        string $jwtPrivateKeyFile,
        string $jwtPassphrase,
        string $jwtTokenAlgorithm = Constant::DEFAULT_JWT_ALGORITHM,
        int $jwtExpirationSeconds = Constant::DEFAULT_JWT_TTL,
        int $jwtRefreshExpirationTime = Constant::DEFAULT_REFRESH_TOKEN_TTL,
        int $refreshTokenLength = Constant::REFRESH_TOKEN_LENGTH,
        string $timezone = Constant::UTC_TIMEZONE
    ) {
        $this->timezone = $timezone;
        $this->jwtTokenAlgorithm = $jwtTokenAlgorithm;
        $this->jwtRefreshExpirationTime = $jwtRefreshExpirationTime;
        $this->refreshTokenLength = $refreshTokenLength;
        $this->jwtToken = RsaJwtTokenProcessor::init(
            privateKeyPath: $jwtPrivateKeyFile,
            passphrase: $jwtPassphrase,
            expirationSeconds: $jwtExpirationSeconds,
            algorithm: $jwtTokenAlgorithm,
            timezone: $timezone,
        );
    }

    /**
     * Generates a new JWT and a corresponding refresh token for a given user.
     *
     * The JWT is encoded with the provided payload and optional header, and signed using the configured
     * RSA private key. A new refresh token is generated and associated with the user. If the user
     * already has a refresh token, it will be revoked (deleted) before a new one is created.
     *
     * @param string                $userId  the unique identifier of the user for whom to generate the tokens
     * @param array<string, mixed>  $payload an associative array containing the data to be included in the JWT payload
     * @param array<string, string> $header  an optional associative array containing additional header parameters for the JWT
     *
     * @return array{token: JwtToken, refresh_token: RefreshToken} An array containing the generated JWT and refresh token objects.
     *                                                             - `token`: The generated {@link JwtToken} object.
     *                                                             - `refresh_token`: The generated {@link RefreshToken} object.
     *
     * @throws ExceptionInterface if an error occurs during token generation or saving
     */
    public function generate(string $userId, array $payload, array $header = []): array
    {
        $jwtToken = $this->jwtToken->encode(
            userId: $userId,
            data: $payload,
            header: $header
        );

        if ($this->refreshTokenRepository->userHasRefreshToken($userId)) {
            $this->refreshTokenRepository->delete($userId);
        }

        $refreshToken = RefreshToken::create(
            id: $this->refreshTokenRepository->generateId(),
            userId: $userId,
            expirationSeconds: $this->jwtRefreshExpirationTime,
            timezone: $this->timezone,
            length: $this->refreshTokenLength
        );
        $this->refreshTokenRepository->save($refreshToken);

        return [
            'token' => $jwtToken,
            'refresh_token' => $refreshToken,
        ];
    }

    /**
     * Validates a JWT token for a specific user by verifying the owner's identity and ensuring
     * that the user-provided data exactly matches the decoded token's payload and (optionally) header.
     * The decoded token is considered the source of truth for the data comparison.
     *
     * @param string               $jwtToken the JWT token string to validate
     * @param string               $userId   the expected user ID that should match the token's owner
     * @param array<string, mixed> $payload  the associative array representing the payload data that the user *must* provide,
     *                                       and this data will be compared against the decoded token's payload
     * @param array<string, mixed> $header   the optional associative array representing the header data that the user *must* provide,
     *                                       and this data will be compared against the decoded token's header
     *
     * @return TokenPayload the decoded {@link TokenPayload} object if the validation is successful
     *
     * @throws InvalidJwtTokenOwnerException if the user ID in the token does not match the provided `$userId`
     * @throws InvalidJwtTokenException      if the token is invalid due to a mismatch in the provided
     *                                       `$expectedPayload` or `$expectedHeader` compared to the decoded token, or if the token signature is invalid
     */
    public function validate(string $jwtToken, string $userId, array $payload, array $header = []): TokenPayload
    {
        $decodedJwtToken = $this->jwtToken->decode($jwtToken);

        $header['alg'] = $this->jwtTokenAlgorithm;
        $this->validateDecodedJwtToken($decodedJwtToken, $userId, $payload, $header);

        return $decodedJwtToken;
    }

    /**
     * Revokes (Delete) the refresh token associated with a given user.
     *
     * This action effectively invalidates any future attempts to refresh the user's JWT using the revoked token.
     *
     * @param string $userId the unique identifier of the user whose refresh token should be revoked
     *
     * @throws UserHasNoRefreshTokenException if no refresh token exists for the specified user
     */
    public function revoke(string $userId): void
    {
        if ($this->refreshTokenRepository->userHasRefreshToken($userId)) {
            $this->refreshTokenRepository->delete($userId);

            return;
        }

        throw new UserHasNoRefreshTokenException([
            'message' => 'user.has_no_refresh_token',
            'details' => [
                'user_id' => $userId,
            ],
        ]);
    }
}
