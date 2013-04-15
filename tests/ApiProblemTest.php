<?php

namespace Crell\ApiProblem;

/**
 * Tests for the ApiProblem object.
 *
 * @autor Larry Garfield
 */
class ApiProblemTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $problem = new ApiProblem('Title', 'URI');
        $this->assertEquals("Title", $problem->getTitle());
        $this->assertEquals("URI", $problem->getProblemType());
    }

    public function testSimpleExtraProperty()
    {
        $problem = new ApiProblem('Title', 'URI');

        $problem['sir'] = 'Gir';
        $this->assertEquals('Gir', $problem['sir']);

        unset($problem['sir']);
        $this->assertNull($problem['sir']);
    }

    public function testComplexExtraProperty()
    {
        $problem = new ApiProblem('Title', 'URI');

        $problem['irken']['invader'] = 'Zim';
        $this->assertEquals('Zim', $problem['irken']['invader']);
    }

    public function testSimpleJsonCompile()
    {
        $problem = new ApiProblem('Title', 'URI');

        $json = $problem->asJson();
        $result = json_decode($json, true);

        $this->assertArrayHasKey('title', $result);
        $this->assertEquals('Title', $result['title']);
        $this->assertArrayHasKey('problemType', $result);
        $this->assertEquals('URI', $result['problemType']);
    }

    public function testExtraPropertyJsonCompile()
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $json = $problem->asJson();
        $result = json_decode($json, true);

        $this->assertArrayHasKey('sir', $result);
        $this->assertEquals('Gir', $result['sir']);
        $this->assertArrayHasKey('irken', $result);
        $this->assertArrayHasKey('invader', $result['irken']);
        $this->assertEquals('Zim', $result['irken']['invader']);
    }

    /**
     * @expectedException \Crell\ApiProblem\RequiredPropertyNotFoundException
     * @expectedExceptionMessage The "title" property is required
     */
    public function testNoTitleError()
    {
        $problem = new ApiProblem('', 'URI');
        $json = $problem->asJson();
    }

    /**
     * @expectedException \Crell\ApiProblem\RequiredPropertyNotFoundException
     * @expectedExceptionMessage The "problemType" property is required
     */
    public function testNoProblemTypeError()
    {
        $problem = new ApiProblem('Title');
        $json = $problem->asJson();
    }


}

