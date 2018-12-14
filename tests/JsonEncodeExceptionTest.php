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

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertNull($exception->getJson());
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

        $this->assertSame('title', $exception->getMessage());
        $this->assertSame(2, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame('json', $exception->getJson());
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

        $this->assertSame('title', $exception->getMessage());
        $this->assertSame(2, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame($json, $exception->getJson());
    }

    public function testFromJsonError(): void
    {
        $json = 'foo';
        $exception = JsonEncodeException::fromJsonError(\JSON_ERROR_SYNTAX, $json);

        $this->assertSame('Syntax error, malformed JSON', $exception->getMessage());
        $this->assertSame(\JSON_ERROR_SYNTAX, $exception->getCode());
        $this->assertSame($json, $exception->getJson());
    }
}
