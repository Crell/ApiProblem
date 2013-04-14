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

}

