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

namespace Osirisgate\Component\Tokenizer\Tests\BuildInToken;

use Osirisgate\Component\Tokenizer\BuildInToken\Exception\ExpiredTokenTypeException;
use Osirisgate\Component\Tokenizer\BuildInToken\Exception\UnknownTokenException;
use Osirisgate\Component\Tokenizer\BuildInToken\Repository\InMemory\InMemoryTokenRepository;
use Osirisgate\Component\Tokenizer\BuildInToken\Repository\TokenRepositoryInterface;
use Osirisgate\Component\Tokenizer\BuildInToken\TokenizerInterface;
use Osirisgate\Component\Tokenizer\BuildInToken\TokenizerService;
use Osirisgate\Core\Enum\Status;
use Osirisgate\Core\Enum\StatusCode;
use Osirisgate\Core\Exception\ExceptionInterface;
use Osirisgate\Core\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the {@see TokenizerService}.
 *
 * This class tests the generation and reuse of built-in tokens (like email
 * verification tokens) using the {@see TokenizerService} and an in-memory
 * token repository.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
final class TokenizerServiceTest extends TestCase
{
    /**
     * @var TokenizerInterface The tokenizer service under test.
     */
    private TokenizerInterface $tokenizer;

    /**
     * @var TokenRepositoryInterface The in-memory token repository.
     */
    private TokenRepositoryInterface $repository;

    /**
     * Sets up the test environment before each test.
     *
     * Initializes the in-memory token repository and the {@see TokenizerService}.
     */
    protected function setUp(): void
    {
        $this->repository = new InMemoryTokenRepository();
        $this->tokenizer = new TokenizerService($this->repository);
    }

    /**
     * Tests that the {@see TokenizerService} generates a new token for a user
     * with the specified length and TTL, and that the token is stored in the repository.
     *
     * @throws RuntimeException
     * @throws ExceptionInterface
     */
    public function testGeneratesTokenForUser(): void
    {
        $userId = $this->generateUserId();

        $result = $this->tokenizer->generate(
            userId: $userId,
            tokenType: 'email_verification_token',
            length: 10,
            tokenTtl: 3600,
            timezone: 'Europe/Paris'
        );

        $this->assertValidToken($result, 10);
        $this->assertRepositoryContainsTokens();
    }

    /**
     * Tests that the {@see TokenizerService} reuses an existing valid token for
     * the same user and token type if it has not expired.
     *
     * @throws RuntimeException
     * @throws ExceptionInterface
     */
    public function testReusesExistingTokenIfNotExpired(): void
    {
        $userId = $this->generateUserId();

        $first = $this->tokenizer->generate(
            userId: $userId,
            tokenType: 'email_verification_token',
            length: 20,
            tokenTtl: 3600,
            timezone: 'Europe/Paris'
        );

        $second = $this->tokenizer->generate(
            userId: $userId,
            tokenType: 'email_verification_token',
            length: 20,
            tokenTtl: 3600,
            timezone: 'Europe/Paris'
        );

        self::assertSame($first['value'], $second['value']);
        $this->assertValidToken($second, 20);
        $this->assertRepositoryContainsTokens();
    }

    /**
     * Tests that the {@see TokenizerService} generates a new token if no matching
     * token exists in the repository for the given user and token type.
     *
     * @throws RuntimeException
     * @throws ExceptionInterface
     */
    public function testGeneratesNewTokenIfNoMatchingInRepository(): void
    {
        $userId = 'jean_682a6d85169c6';
        $this->repository->add();

        $result = $this->tokenizer->generate(
            userId: $userId,
            tokenType: 'email_verification_token',
            length: 6,
            tokenTtl: 3600,
            timezone: 'Europe/Paris'
        );

        $this->assertValidToken($result, 6);
        $this->assertRepositoryContainsTokens();
    }

    /**
     * @throws RuntimeException
     * @throws ExceptionInterface
     */
    public function testCanGetTokenByValue(): void
    {
        $userId = 'jean_682a6d85169c6';
        $this->repository->add();

        $result = $this->tokenizer->generate(
            userId: $userId,
            tokenType: 'email_verification_token',
            length: 6,
            tokenTtl: 3600,
            timezone: 'Europe/Paris'
        );

        $tokenFromRepository = $this->repository->findByTypeAndValue(
            tokenType: 'email_verification_token',
            tokenValue: $result['value']
        );
        self::assertEquals($result['value'], $tokenFromRepository->value());
        self::assertEquals('email_verification_token', $tokenFromRepository->type());
    }

    /**
     * Tests that the {@see TokenizerService} generates an uppercase OTP token
     * when the `isOtp` flag is set to true.
     *
     * @throws RuntimeException
     * @throws ExceptionInterface
     */
    public function testGeneratesUppercaseOtpToken(): void
    {
        $userId = 'jean_682a6d85169c6';
        $this->repository->add();

        $result = $this->tokenizer->generate(
            userId: $userId,
            tokenType: 'email_verification_token',
            length: 8,
            tokenTtl: 3600,
            timezone: 'Europe/Paris',
            isOtp: true
        );

        $value = $result['value'];
        self::assertSame(mb_strtoupper($value, 'UTF-8'), $value, 'OTP token should be uppercase');

        $this->assertValidToken($result, 8);
        $this->assertRepositoryContainsTokens();
    }

    /**
     * @throws RuntimeException
     * @throws ExceptionInterface
     */
    public function testGeneratedTokenIsValidAndDeleted(): void
    {
        $userId = $this->generateUserId();

        $result = $this->tokenizer->generate(
            userId: $userId,
            tokenType: 'email_verification_token',
            length: 10,
            tokenTtl: 3600,
            timezone: 'Europe/Paris'
        );

        self::assertTrue($this->tokenizer->isValid('email_verification_token', $result['value']));
        self::assertCount(0, $this->repository->getTokens());
    }

    /**
     * @throws RuntimeException
     * @throws UnknownTokenException
     * @throws ExpiredTokenTypeException
     * @throws ExceptionInterface
     */
    public function testGeneratedTokenIsValidAndNotDeleted(): void
    {
        $userId = $this->generateUserId();

        $result = $this->tokenizer->generate(
            userId: $userId,
            tokenType: 'email_verification_token',
            length: 10,
            tokenTtl: 3600,
            timezone: 'Europe/Paris'
        );

        self::assertTrue($this->tokenizer->isValid('email_verification_token', $result['value'], false));
        self::assertCount(1, $this->repository->getTokens());
    }

    /**
     * @throws RuntimeException
     * @throws ExceptionInterface
     */
    public function testGeneratedTokenIsValidForUser(): void
    {
        $userId = $this->generateUserId();

        $result = $this->tokenizer->generate(
            userId: $userId,
            tokenType: 'email_verification_token',
            length: 10,
            tokenTtl: 3600,
            timezone: 'Europe/Paris'
        );

        self::assertTrue($this->tokenizer->isValidForUser($userId, 'email_verification_token', $result['value']));
        self::assertCount(0, $this->repository->getTokens());
    }

    /**
     * @throws RuntimeException
     * @throws ExceptionInterface
     */
    public function testCanRetrieveTokenByTypeAndValue(): void
    {
        $userId = $this->generateUserId();

        $result = $this->tokenizer->generate(
            userId: $userId,
            tokenType: 'email_verification_token',
            length: 10,
            tokenTtl: 3600,
            timezone: 'Europe/Paris'
        );

        self::assertCount(1, $this->repository->getTokens());
        $this->tokenizer->delete('email_verification_token', $result['value']);
        self::assertCount(0, $this->repository->getTokens());
    }

    /**
     * @throws RuntimeException
     * @throws ExceptionInterface
     */
    public function testCanDeleteGeneratedTokenByValue(): void
    {
        $userId = $this->generateUserId();

        $result = $this->tokenizer->generate(
            userId: $userId,
            tokenType: 'email_verification_token',
            length: 10,
            tokenTtl: 3600,
            timezone: 'Europe/Paris'
        );

        self::assertCount(1, $this->repository->getTokens());
        $this->tokenizer->delete('email_verification_token', $result['value']);
        self::assertCount(0, $this->repository->getTokens());
    }

    /**
     * @throws RuntimeException
     * @throws ExceptionInterface
     */
    public function testCanNotDeleteUnknownTokenByValue(): void
    {
        $userId = $this->generateUserId();

        $result = $this->tokenizer->generate(
            userId: $userId,
            tokenType: 'email_verification_token',
            length: 10,
            tokenTtl: 3600,
            timezone: 'Europe/Paris'
        );

        try {
            $this->tokenizer->delete('email_verification_token', $result['value'] . 'unknown');
        } catch (UnknownTokenException $exception) {
            $exceptionFormat = $exception->format();
            self::assertEquals(Status::ERROR->getValue(), $exceptionFormat['status']);
            self::assertEquals(StatusCode::BAD_REQUEST->getValue(), $exceptionFormat['error_code']);
            self::assertEquals([
                'token' => [
                    'type' => 'email_verification_token',
                    'value' => $result['value'] . 'unknown',
                ],
            ], $exceptionFormat['details']);
            self::assertCount(1, $this->repository->getTokens());
        }
    }

    /**
     * Helper method to generate a unique user ID for testing.
     *
     * @return string A unique user ID.
     */
    private function generateUserId(): string
    {
        return uniqid('user_');
    }

    /**
     * Helper method to assert that the given token array contains the expected
     * 'value' and 'expires_at' keys, and that the 'value' has the expected length.
     *
     * @param array $token The token array to validate.
     * @param int $expectedLength The expected length of the token value.
     */
    private function assertValidToken(array $token, int $expectedLength): void
    {
        self::assertArrayHasKey('value', $token);
        self::assertArrayHasKey('expires_at', $token);
        self::assertIsString($token['value']);
        self::assertEquals($expectedLength, strlen($token['value']));
    }

    /**
     * Helper method to assert that the in-memory token repository contains at least one token.
     */
    private function assertRepositoryContainsTokens(): void
    {
        self::assertCount(1, $this->repository->getTokens());
    }
}
