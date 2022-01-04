<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

class JsonException extends \InvalidArgumentException
{
    /**
     * This mapping is based on the PHP manual.
     *
     * Why this isn't built into the language somewhere I have no idea.
     */
    protected const EXCEPTION_MESSAGES = [
        \JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
        \JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
        \JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
        \JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
        \JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        \JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded',
        \JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded',
        \JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given',
        \JSON_ERROR_INVALID_PROPERTY_NAME => 'A property name that cannot be encoded was given',
        \JSON_ERROR_UTF16 => 'Malformed UTF-16 characters, possibly incorrectly encoded',
    ];

    /**
     * @var mixed
     */
    protected $failedValue;

    /**
     * Maps a JSON error code to a human-friendly error message.
     *
     * @param int $jsonError
     *   the JSON error code, as returned by json_last_error().
     * @return string
     */
    protected static function getExceptionMessage(int $jsonError): string
    {
        return self::EXCEPTION_MESSAGES[$jsonError] ?? 'Unknown error';
    }

    /**
     * Creates a new exception object based on the JSON error code.
     *
     * @param int $jsonError
     *   the JSON error code.
     * @param mixed $failedValue
     *   The value that failed to parse or encode.
     * @return JsonException
     *   A new exception object.
     */
    public static function fromJsonError(int $jsonError, $failedValue): self
    {
        // This is a valid use of `new static`, even if PHPStan is wrong about it.
        // @phpstan-ignore-next-line
        return new static(static::getExceptionMessage($jsonError), $jsonError, null, $failedValue);
    }

    /**
     * @param mixed $failedValue
     */
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null, $failedValue = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setFailedValue($failedValue);
    }

    /**
     * Sets the value that failed to parse or encode so it can be analyzed later.
     *
     * @param mixed $failedValue
     *   The value that failed to parse or encode correctly.
     * @return JsonException
     *   The invoked object.
     */
    public function setFailedValue($failedValue) : self
    {
        $this->failedValue = $failedValue;
        return $this;
    }

    /**
     * Returns the value that failed to parse or encode properly.
     *
     * @return mixed
     *   The value that failed to process.
     */
    public function getFailedValue()
    {
        return $this->failedValue;
    }
}
