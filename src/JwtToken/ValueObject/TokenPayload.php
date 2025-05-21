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

namespace Osirisgate\Component\Tokenizer\JwtToken\ValueObject;

use Osirisgate\Component\Tokenizer\Shared\Exception\HttpExceptionUtil;
use Osirisgate\Core\Exception\ExceptionInterface;

/**
 * Represents the decoded payload and header of a JSON Web Token (JWT).
 *
 * This readonly class holds the data, header information, validity status,
 * any potential error message from decoding, and the user ID extracted from the token.
 * It provides methods to access these properties and to retrieve specific fields
 * from the payload and header.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 *
 * @readonly
 */
final readonly class TokenPayload
{
    /**
     * Private constructor to enforce the use of the static `create()` method.
     *
     * @param array<string, mixed> $data    the decoded payload data of the JWT
     * @param array<string, mixed> $header  the decoded header of the JWT
     * @param bool                 $isValid a boolean indicating whether the token was successfully decoded and is considered valid
     * @param string|null          $error   an optional error message if the token decoding or validation failed
     * @param string|null          $userId  the user ID extracted from the token payload, if available
     */
    private function __construct(
        private array $data,
        private array $header,
        private bool $isValid,
        private ?string $error = null,
        private ?string $userId = null,
    ) {
    }

    /**
     * Static factory method to create a new `TokenPayload` instance.
     *
     * @param array<string, mixed> $data    the decoded payload data
     * @param array<string, mixed> $header  the decoded header
     * @param bool                 $isValid the validity status of the token
     * @param string|null          $error   an optional error message
     * @param string|null          $userId  the user ID from the token
     *
     * @return self a new `TokenPayload` instance
     */
    public static function create(array $data, array $header, bool $isValid, ?string $error = null, ?string $userId = null): self
    {
        return new self($data, $header, $isValid, $error, $userId);
    }

    /**
     * Returns the user ID extracted from the token payload, if present.
     *
     * @return string|null the user ID, or null if not available
     */
    public function userId(): ?string
    {
        return $this->userId;
    }

    /**
     * Returns the decoded payload data of the JWT.
     *
     * @return array<string, mixed> the payload data as an associative array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Returns the decoded header of the JWT.
     *
     * @return array<string, mixed> the header as an associative array
     */
    public function header(): array
    {
        return $this->header;
    }

    /**
     * Indicates whether the token was successfully decoded and is considered valid.
     *
     * @return bool true if the token is valid, false otherwise
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Returns any error message that occurred during token decoding or validation.
     *
     * @return string|null the error message, or null if no error occurred
     */
    public function error(): ?string
    {
        return $this->error;
    }

    /**
     * Retrieves a specific field from the payload data using dot notation for nested fields.
     *
     * If the field is not found, the provided default value is returned.
     *
     * @param string $fieldName The name of the field to retrieve (e.g., 'user.email').
     * @param mixed  $default   the value to return if the field is not found (default: null)
     *
     * @return mixed the value of the requested field, or the default value if not found
     *
     * @throws ExceptionInterface if an error occurs during the retrieval process
     */
    public function getFromData(string $fieldName, mixed $default = null): mixed
    {
        try {
            $data = $this->data();
            foreach (explode('.', $fieldName) as $key) {
                if (!isset($data[$key])) {
                    return $default;
                }

                $data = $data[$key];
            }

            return $data;
        } catch (\Throwable $exception) {
            throw HttpExceptionUtil::throw($exception);
        }
    }

    /**
     * Retrieves a specific field from the header using dot notation for nested fields.
     *
     * If the field is not found, the provided default value is returned.
     *
     * @param string $fieldName The name of the header field to retrieve (e.g., 'alg').
     * @param mixed  $default   the value to return if the field is not found (default: null)
     *
     * @return mixed the value of the requested header field, or the default value if not found
     *
     * @throws ExceptionInterface if an error occurs during the retrieval process
     */
    public function getFromHeader(string $fieldName, mixed $default = null): mixed
    {
        try {
            $data = $this->header();
            foreach (explode('.', $fieldName) as $key) {
                if (!isset($data[$key])) {
                    return $default;
                }

                $data = $data[$key];
            }

            return $data;
        } catch (\Throwable $exception) {
            throw HttpExceptionUtil::throw($exception);
        }
    }
}
