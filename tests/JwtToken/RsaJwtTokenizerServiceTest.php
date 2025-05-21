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

namespace Osirisgate\Component\Tokenizer\Tests\JwtToken;

use Osirisgate\Component\Tokenizer\JwtToken\Entity\RefreshToken\RefreshToken;
use Osirisgate\Component\Tokenizer\JwtToken\Exception\InvalidJwtTokenException;
use Osirisgate\Component\Tokenizer\JwtToken\Exception\InvalidJwtTokenOwnerException;
use Osirisgate\Component\Tokenizer\JwtToken\JwtTokenizerInterface;
use Osirisgate\Component\Tokenizer\JwtToken\Repository\InMemory\InMemoryRefreshTokenRepository;
use Osirisgate\Component\Tokenizer\JwtToken\Repository\RefreshTokenRepositoryInterface;
use Osirisgate\Component\Tokenizer\JwtToken\Strategy\RsaJwtToken\RsaJwtTokenizer;
use Osirisgate\Core\Enum\Status;
use Osirisgate\Core\Enum\StatusCode;
use Osirisgate\Core\Exception\ExceptionInterface;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the {@see RsaJwtTokenizer} service.
 *
 * This class tests the generation, validation, and error handling of JWTs
 * using the RSA algorithm, along with the associated refresh token management.
 * It uses an in-memory refresh token repository for isolation.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class RsaJwtTokenizerServiceTest extends TestCase
{
    /**
     * @var RefreshTokenRepositoryInterface The in-memory repository for refresh tokens.
     */
    private RefreshTokenRepositoryInterface $repository;

    /**
     * @var JwtTokenizerInterface The JWT tokenizer service under test.
     */
    private JwtTokenizerInterface $jwtTokenizer;

    /**
     * Sets up the test environment before each test.
     *
     * Initializes the in-memory refresh token repository and the RSA JWT tokenizer
     * with a private key and passphrase.
     */
    protected function setUp(): void
    {
        $this->repository = new InMemoryRefreshTokenRepository();
        $this->jwtTokenizer = new RsaJwtTokenizer(
            refreshTokenRepository: $this->repository,
            jwtPrivateKeyFile: dirname(__DIR__, 2) . '/jwt/private.pem',
            jwtPassphrase: '4d25d0ac969134d25b8f28b2fbd1c930a30b576d3e53751ee693138aea3fdb92',
            timezone: 'Europe/Paris'
        );
    }

    /**
     * Tests that generating a token returns a valid JWT and a refresh token,
     * and that the refresh token is stored in the repository.
     *
     * @throws ExceptionInterface
     */
    public function testGenerateTokenReturnsValidJwtAndRefreshToken(): void
    {
        $userId = uniqid('user_');
        $result = $this->generateJwt($userId);

        self::assertIsString($result['token']->value());
        self::assertIsString($result['refresh_token']->value());

        $interval = $result['token']->expiresAt()->diff($result['refresh_token']->expiresAt());
        self::assertEquals('4 days, 23 hours, 0 minutes, 0 seconds', $interval->format('%a days, %h hours, %i minutes, %s seconds'));
        self::assertTrue($this->repository->userHasRefreshToken($userId));
        self::assertCount(1, $this->repository->getRefreshTokens());
    }

    /**
     * Tests that a valid JWT token can be decoded correctly, and its payload,
     * header, and user ID are accessible.
     *
     * @throws InvalidJwtTokenException
     * @throws ExceptionInterface
     * @throws InvalidJwtTokenOwnerException
     */
    public function testValidJwtTokenIsDecodedCorrectly(): void
    {
        $userId = uniqid('user_');
        $result = $this->generateJwt($userId);

        $decoded = $this->jwtTokenizer->validate(
            jwtToken: $result['token']->value(),
            userId: $userId,
            payload: [
                'email' => 'jean@example.com',
            ],
            header: [
                'custom' => 'value',
            ]
        );

        self::assertSame('jean@example.com', $decoded->getFromData('email'));
        self::assertSame('value', $decoded->getFromHeader('custom'));
        self::assertSame('JWT', $decoded->getFromHeader('typ'));
        self::assertSame('RS256', $decoded->getFromHeader('alg'));
        self::assertSame($userId, $decoded->userId());
    }

    /**
     * Tests that a valid JWT token can be decoded correctly, and its payload,
     * header, and user ID are accessible.
     *
     * @throws InvalidJwtTokenException
     * @throws ExceptionInterface
     * @throws InvalidJwtTokenOwnerException
     */
    public function testCanRetrieveJwtRefreshTokenValue(): void
    {
        $userId = uniqid('user_');
        $result = $this->generateJwt($userId);

        /** @var RefreshToken $refreshToken */
        $refreshToken = $result['refresh_token'];
        $refreshTokenFromRepository = $this->repository->findByToken($refreshToken->value());

        self::assertSame($refreshToken->getId(), $refreshTokenFromRepository->getId());
        self::assertSame($refreshToken->userId(), $refreshTokenFromRepository->userId());
        self::assertSame($refreshToken->value(), $refreshTokenFromRepository->value());
    }

    /**
     * Tests that validating a JWT with an incorrect user ID throws an
     * {@see InvalidJwtTokenOwnerException}.
     */
    public function testInvalidTokenOwnerThrowsException(): void
    {
        $this->expectExceptionWithFormattedOutput(
            InvalidJwtTokenOwnerException::class,
            [
                'status' => Status::ERROR->getValue(),
                'error_code' => StatusCode::UNAUTHORIZED->getValue(),
                'message' => 'invalid.jwt.token.owner',
                'details' => [
                    'user_id' => 'user_682dd421acb0cINVALID_ID',
                ],
            ],
            fn () => $this->validateWithModifiedUserId()
        );
    }

    /**
     * Tests that validating a JWT with a modified payload value throws an
     * {@see InvalidJwtTokenException}.
     */
    public function testInvalidTokenPayloadThrowsException(): void
    {
        $this->expectExceptionWithFormattedOutput(
            InvalidJwtTokenException::class,
            [
                'status' => Status::ERROR->getValue(),
                'error_code' => StatusCode::UNAUTHORIZED->getValue(),
                'message' => 'invalid.jwt.token.payload',
                'details' => [
                    'error' => 'invalid.value',
                    'key' => 'email',
                    'value' => 'jeanq@example.com',
                ],
            ],
            fn () => $this->validateWithModifiedPayload([
                'email' => 'jeanq@example.com',
            ])
        );
    }

    /**
     * Tests that validating a JWT with an unknown key in the provided payload
     * throws an {@see InvalidJwtTokenException}.
     */
    public function testUnknownPayloadKeyThrowsException(): void
    {
        $this->expectExceptionWithFormattedOutput(
            InvalidJwtTokenException::class,
            [
                'status' => Status::ERROR->getValue(),
                'error_code' => StatusCode::UNAUTHORIZED->getValue(),
                'message' => 'invalid.jwt.token.payload',
                'details' => [
                    'error' => 'unexpected.key',
                    'key' => 'type',
                ],
            ],
            fn () => $this->validateWithModifiedPayload([
                'email' => 'jean@example.com',
                'type' => 'email',
            ])
        );
    }

    /**
     * Tests that validating a JWT with a modified header value throws an
     * {@see InvalidJwtTokenException}.
     */
    public function testInvalidJwtHeaderThrowsException(): void
    {
        $this->expectExceptionWithFormattedOutput(
            InvalidJwtTokenException::class,
            [
                'status' => Status::ERROR->getValue(),
                'error_code' => StatusCode::UNAUTHORIZED->getValue(),
                'message' => 'invalid.jwt.token.header',
                'details' => [
                    'error' => 'invalid.value',
                    'key' => 'custom',
                    'value' => 'invalid',
                ],
            ],
            fn () => $this->validateWithModifiedHeader()
        );
    }

    /**
     * Tests that attempting to validate an expired JWT throws an
     * {@see InvalidJwtTokenException}.
     */
    public function testExpiredJwtTokenThrowsException(): void
    {
        $this->expectExceptionWithFormattedOutput(
            InvalidJwtTokenException::class,
            [
                'status' => Status::ERROR->getValue(),
                'error_code' => StatusCode::UNAUTHORIZED->getValue(),
                'message' => 'invalid.jwt.token',
                'details' => [
                    'error' => 'Expired token',
                ],
            ],
            fn () => $this->validateExpiredJwtToken()
        );
    }

    /**
     * Helper method to generate a JWT and refresh token for a given user.
     *
     * @param string $userId The user ID for whom to generate the tokens.
     * @return array An array containing the generated {@see JwtToken} and {@see RefreshToken}.
     * @throws ExceptionInterface
     */
    private function generateJwt(string $userId): array
    {
        return $this->jwtTokenizer->generate(
            userId: $userId,
            payload: [
                'email' => 'jean@example.com',
            ],
            header: [
                'custom' => 'value',
            ]
        );
    }

    /**
     * Helper method to validate a JWT with a modified user ID.
     *
     * @throws InvalidJwtTokenOwnerException
     * @throws InvalidJwtTokenException
     * @throws ExceptionInterface
     */
    private function validateWithModifiedUserId(): void
    {
        $userId = 'user_682dd421acb0c';
        $token = $this->generateJwt($userId)['token'];

        $this->jwtTokenizer->validate(
            jwtToken: $token->value(),
            userId: $userId . 'INVALID_ID',
            payload: [
                'email' => 'jean@example.com',
            ],
            header: [
                'custom' => 'value',
            ]
        );
    }

    /**
     * Helper method to validate a JWT with a modified payload.
     *
     * @param array<string, mixed> $payload The modified payload for validation.
     * @throws InvalidJwtTokenOwnerException
     * @throws InvalidJwtTokenException
     * @throws ExceptionInterface
     */
    private function validateWithModifiedPayload(array $payload): void
    {
        $userId = uniqid('user_');
        $token = $this->generateJwt($userId)['token'];

        $this->jwtTokenizer->validate(
            jwtToken: $token->value(),
            userId: $userId,
            payload: $payload,
            header: [
                'custom' => 'value',
            ]
        );
    }

    /**
     * Helper method to validate a JWT with a modified header.
     *
     * @throws InvalidJwtTokenOwnerException
     * @throws InvalidJwtTokenException
     * @throws ExceptionInterface
     */
    private function validateWithModifiedHeader(): void
    {
        $header = [
            'custom' => 'invalid',
        ];
        $userId = uniqid('user_');
        $token = $this->generateJwt($userId)['token'];

        $this->jwtTokenizer->validate(
            jwtToken: $token->value(),
            userId: $userId,
            payload: [
                'email' => 'jean@example.com',
            ],
            header: $header
        );
    }

    /**
     * Helper method to attempt validation of a hardcoded expired JWT.
     *
     * @throws InvalidJwtTokenOwnerException
     * @throws InvalidJwtTokenException
     * @throws ExceptionInterface
     */
    private function validateExpiredJwtToken(): void
    {
        $userId = uniqid('user_');
        $this->jwtTokenizer->validate(
            jwtToken: 'eyJ0eXAiOiJKV1QiLCIwIjoidHQiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDc3MzgzNjMsImV4cCI6MTc0Nzc0MTk2MywiZGF0YSI6eyJlbWFpbCI6ImplYW5AZXhhbXBsZS5jb20ifX0.ZpInQxmQigGlDsHDk5JSCOllYJ6bzs4F0MhC1NRy18jPdKya4xN_AqjQKx_ShxA_7x90OpOFdJVpn9sLLhTqOY5LVjUh4LStWNT4Iz1DkNXNzNFttbVCXtPbyD53HHRp0pvHZO-LJZHAx-n6mHfMzn4JBxiE8CHZXg5FtU9hPK3JDddUq2Tnyy3QpEdSnbQqkac3qJr-iiPtbDqRaUFjMoZ4IITk5sxxsbuwxz8hv8-jJOGTOEdiOsONAFpDpsk70saMYrIKj8L-j_btHhq-wLQfKD9LN_F3R3mm0cUJRyLm20xwhYAWD-0W-ta6o9IjTjtjmlZWYhpCWKkE-LWpng',
            userId: $userId,
            payload: [
                'email' => 'jean@example.com',
            ],
            header: [
                'custom' => 'invalid',
            ]
        );
    }

    /**
     * Helper method to assert that an expected exception with a specific formatted
     * output is thrown by a given callable.
     *
     * @param string $exceptionClass The fully qualified class name of the expected exception.
     * @param array $expectedFormat The expected formatted output of the exception.
     * @param callable $callback The callable that should throw the exception.
     */
    private function expectExceptionWithFormattedOutput(
        string $exceptionClass,
        array $expectedFormat,
        callable $callback
    ): void {
        try {
            $callback();
            self::fail("Expected exception of type {$exceptionClass} was not thrown.");
        } catch (ExceptionInterface $exception) {
            self::assertInstanceOf($exceptionClass, $exception);
            self::assertSame($expectedFormat, $exception->format());
        }
    }
}
