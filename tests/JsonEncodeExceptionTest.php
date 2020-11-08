<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

use PHPUnit\Framework\TestCase;

/**
 * Test for the JSON error handling.
 */
class JsonEncodeExceptionTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $exception = new JsonEncodeException();

        self::assertSame('', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
        self::assertNull($exception->getFailedValue());
    }

    public function testConstructor(): void
    {
        $previous = new \RuntimeException();
        $exception = new JsonEncodeException(
            'title',
            2,
            $previous,
            'json'
        );

        self::assertSame('title', $exception->getMessage());
        self::assertSame(2, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
        self::assertSame('json', $exception->getFailedValue());
    }

    public function testWithJsonObject(): void
    {
        $json = new \stdClass();

        $previous = new \RuntimeException();
        $exception = new JsonEncodeException(
            'title',
            2,
            $previous,
            $json
        );

        self::assertSame('title', $exception->getMessage());
        self::assertSame(2, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
        self::assertSame($json, $exception->getFailedValue());
    }

    public function testFromJsonError(): void
    {
        $json = 'foo';
        $exception = JsonEncodeException::fromJsonError(\JSON_ERROR_SYNTAX, $json);

        self::assertSame('Syntax error, malformed JSON', $exception->getMessage());
        self::assertSame(\JSON_ERROR_SYNTAX, $exception->getCode());
        self::assertSame($json, $exception->getFailedValue());
    }
}
