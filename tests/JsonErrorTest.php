<?php


namespace Crell\ApiProblem\Test;


use Crell\ApiProblem\ApiProblem;
use Crell\ApiProblem\JsonParseException;

/**
 * Test for the JSON error handling.
 *
 * @todo Add tests for something other than invalid syntax, as that's all I
 * can figure out how to cause. :-)
 */
class JsonErrorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Crell\ApiProblem\JsonParseException
     * @expectedExceptionCode JSON_ERROR_SYNTAX
     */
    public function testMalformedJson()
    {
        // Note the stray comma.
        $json = '{"a": "b",}';
        ApiProblem::fromJson($json);
    }

    public function testJsonExceptionString()
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
}
