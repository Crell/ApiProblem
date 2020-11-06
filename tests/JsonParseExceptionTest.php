<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

use PHPUnit\Framework\TestCase;
use const JSON_ERROR_SYNTAX;

/**
 * Test for the JSON error handling.
 *
 * @todo Add tests for something other than invalid syntax, as that's all I
 * can figure out how to cause. :-)
 */
class JsonParseExceptionTest extends TestCase
{

    /**
     * @coversNothing
     */
    public function testMalformedJson() : void
    {
        $this->expectException(JsonParseException::class);
        $this->expectExceptionCode(JSON_ERROR_SYNTAX);

        // Note the stray comma.
        $json = '{"a": "b",}';
        ApiProblem::fromJson($json);
    }

    /**
     * @coversNothing
     */
    public function testJsonExceptionString() : void
    {
        // Note the stray comma.
        $json = '{"a": "b",}';

        try {
            ApiProblem::fromJson($json);
        }
        catch (JsonParseException $e) {
            $this->assertEquals($json, $e->getFailedValue());
        }
    }

    public function testConstructorDefaults(): void
    {
        $exception = new JsonParseException();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertSame('', $exception->getFailedValue());
    }

    public function testConstructor(): void
    {
        $previous = new \RuntimeException();
        $exception = new JsonParseException(
            'title',
            2,
            $previous,
            'json'
        );

        $this->assertSame('title', $exception->getMessage());
        $this->assertSame(2, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame('json', $exception->getFailedValue());
    }

    public function testFromJsonError(): void
    {
        $json = 'foo';
        $exception = JsonParseException::fromJsonError(\JSON_ERROR_SYNTAX, $json);

        $this->assertSame('Syntax error, malformed JSON', $exception->getMessage());
        $this->assertSame(\JSON_ERROR_SYNTAX, $exception->getCode());
        $this->assertSame($json, $exception->getFailedValue());
    }
}
