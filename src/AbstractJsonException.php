<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

abstract class AbstractJsonException extends \InvalidArgumentException
{
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

    protected static function getExceptionMessage(int $jsonError): string
    {
        return self::EXCEPTION_MESSAGES[$jsonError] ?? 'Unknown error';
    }
}
