<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

use PHPUnit\Framework\TestCase;

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
     * @expectedException \Crell\ApiProblem\JsonParseException
     * @expectedExceptionCode JSON_ERROR_SYNTAX
     */
    public function testMalformedJson() : void
    {
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
            $this->assertEquals($json, $e->getJson());
        }
    }

    public function testConstructorDefaults(): void
    {
        $exception = new JsonParseException();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertSame('', $exception->getJson());
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
        $this->assertSame('json', $exception->getJson());
    }

    public function testFromJsonError(): void
    {
        $json = 'foo';
        $exception = JsonParseException::fromJsonError(\JSON_ERROR_SYNTAX, $json);

        $this->assertSame('Syntax error, malformed JSON', $exception->getMessage());
        $this->assertSame(\JSON_ERROR_SYNTAX, $exception->getCode());
        $this->assertSame($json, $exception->getJson());
    }
}
