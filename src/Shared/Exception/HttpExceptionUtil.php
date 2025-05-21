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

namespace Osirisgate\Component\Tokenizer\Shared\Exception;

use Osirisgate\Core\Enum\StatusCode;
use Osirisgate\Core\Exception\ExceptionInterface;
use Osirisgate\Core\Exception\RuntimeException;

/**
 * A utility class providing helper methods for handling HTTP-related exceptions
 * within the Tokenizer component. Specifically, it assists in converting generic
 * {@link \Throwable} instances (including {@link ExceptionInterface}) into
 * {@link RuntimeException} with HTTP status code information, suitable for
 * use within an SDK or API context.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 *
 * @final
 */
abstract class HttpExceptionUtil
{
    /**
     * Converts a given {@link \Throwable} (exception) into a {@link RuntimeException}
     * that includes HTTP status code, reason phrase, and error details.
     *
     * If the original exception has a numeric code, it is used as the HTTP status code.
     * If the code is 0 or not a valid HTTP status code, it defaults to
     * {@see StatusCode::INTERNAL_SERVER_ERROR}.
     *
     * The resulting {@link RuntimeException} will contain the following details:
     * - `status_code`: An instance of {@link StatusCode} representing the HTTP status.
     * - `message`: The reason phrase corresponding to the HTTP status code.
     * - `details`: An array containing the original exception's message under the 'error' key,
     * and any additional details provided by the exception if it implements
     * {@link ExceptionInterface}.
     *
     * @param \Throwable $exception the exception to convert
     *
     * @return ExceptionInterface the converted {@link RuntimeException} with HTTP information
     */
    public static function throw(\Throwable $exception): ExceptionInterface
    {
        $statusCode = $exception->getCode();
        if ($statusCode === 0) {
            $statusCode = StatusCode::INTERNAL_SERVER_ERROR->value;
        }

        return new RuntimeException([
            'status_code' => StatusCode::fromValue($statusCode),
            'message' => self::getReasonPhrase($statusCode),
            'details' => [
                'error' => $exception->getMessage(),
            ] + ($exception instanceof ExceptionInterface ? $exception->getDetails() : []),
        ]);
    }

    /**
     * Retrieves the standard HTTP reason phrase for a given HTTP status code.
     *
     * It uses the {@see StatusCode::statusTexts()} method to get the mapping of
     * status codes to their corresponding reason phrases. If the provided status
     * code is not found, it defaults to the reason phrase for
     * {@see StatusCode::INTERNAL_SERVER_ERROR}.
     *
     * @param int $status the HTTP status code for which to retrieve the reason phrase
     *
     * @return string the HTTP reason phrase corresponding to the given status code
     */
    public static function getReasonPhrase(int $status): string
    {
        $statusTexts = StatusCode::statusTexts();

        return $statusTexts[$status] ?? $statusTexts[StatusCode::INTERNAL_SERVER_ERROR->value];
    }
}
