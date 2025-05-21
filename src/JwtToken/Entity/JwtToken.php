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

namespace Osirisgate\Component\Tokenizer\JwtToken\Entity;

/**
 * Represents a JSON Web Token (JWT) entity.
 *
 * This readonly class holds the raw JWT string value and its expiration date and time.
 * It provides a simple way to encapsulate these two essential properties of a JWT.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 *
 * @readonly
 */
final readonly class JwtToken
{
    /**
     * Private constructor to enforce the use of the static `create()` method.
     *
     * @param string             $value     the raw JWT string value
     * @param \DateTimeInterface $expiresAt the date and time when the JWT expires
     */
    private function __construct(
        private string $value,
        private \DateTimeInterface $expiresAt
    ) {
    }

    /**
     * Static factory method to create a new `JwtToken` instance.
     *
     * @param string             $value     the raw JWT string value
     * @param \DateTimeInterface $expiresAt the expiration date and time
     *
     * @return self a new `JwtToken` instance
     */
    public static function create(string $value, \DateTimeInterface $expiresAt): self
    {
        return new self($value, $expiresAt);
    }

    /**
     * Returns the raw JWT string value.
     *
     * @return string the JWT string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Returns the expiration date and time of the JWT.
     *
     * @return \DateTimeInterface the expiration date and time
     */
    public function expiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }
}
