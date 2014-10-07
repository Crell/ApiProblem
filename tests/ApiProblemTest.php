<?php

/**
 * This file is part of the ApiProblem library.
 *
 * (c) Larry Garfield <larry@garfieldtech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Crell\ApiProblem
 */

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
        $this->assertEquals("URI", $problem->getType());
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
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('URI', $result['type']);

        // Ensure that empty properties are not included.
        $this->assertArrayNotHasKey('detail', $result);
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
     * Confirms that the title property is optional.
     */
    public function testNoTitleAllowed()
    {
        // This should result in no error.
        $problem = new ApiProblem();
        $json = $problem->asJson();
    }

    /**
     * Confirms that the type property defaults to "about:blank"
     */
    public function testTypeDefault()
    {
        $problem = new ApiProblem('Title');
        $json = $problem->asJson();
        $result = json_decode($json, true);
        $this->assertEquals('about:blank', $result['type']);
    }

    public function testSimpleXmlCompile()
    {
        $problem = new ApiProblem('Title', 'URI');

        $xml = $problem->asXml();
        $result = simplexml_load_string($xml);

        $this->assertEquals('problem', $result->getName());
        $dom = dom_import_simplexml($result);

        $titles = $dom->getElementsByTagName('title');
        $this->assertEquals(1, $titles->length);
        $this->assertEquals('Title', $titles->item(0)->textContent);
    }

    public function testExtraPropertyXmlCompile()
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $xml = $problem->asXml(true);
        $result = simplexml_load_string($xml);

        $this->assertEquals('problem', $result->getName());
        $dom = dom_import_simplexml($result);

        $titles = $dom->getElementsByTagName('title');
        $this->assertEquals(1, $titles->length);
        $this->assertEquals('Title', $titles->item(0)->textContent);

        $sir = $dom->getElementsByTagName('sir');
        $this->assertEquals(1, $sir->length);
        $this->assertEquals('Gir', $sir->item(0)->textContent);

        $invader = $result->xpath('/problem/irken/invader');
        $this->assertCount(1, $invader);
        while(list( , $node) = each($invader)) {
            $this->assertEquals('Zim', $node);
        }
    }

    public function testParseJson()
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(403);
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $result = ApiProblem::fromJson($problem->asJson());

        $this->assertEquals('Title', $result->getTitle());
        $this->assertEquals(403, $result->getStatus());
        $this->assertEquals('Gir', $result['sir']);
        $this->assertEquals('Zim', $result['irken']['invader']);
    }

    public function testParseXml()
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(403);
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $result = ApiProblem::fromXml($problem->asXml());

        $this->assertEquals('Title', $result->getTitle());
        $this->assertEquals(403, $result->getStatus());
        $this->assertEquals('Gir', $result['sir']);
        $this->assertEquals('Zim', $result['irken']['invader']);
    }

    public function testArray()
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(403);
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $array = $problem->asArray();
        $this->assertEquals('Gir', $array['sir']);
        $this->assertEquals(403, $array['status']);
        $this->assertEquals('Title', $array['title']);
        $this->assertEquals('URI', $array['type']);
        $this->assertEquals('Zim', $array['irken']['invader']);
    }
}

