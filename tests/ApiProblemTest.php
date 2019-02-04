<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

use PHPUnit\Framework\TestCase;

/**
 * Tests for the ApiProblem object.
 *
 * @autor Larry Garfield
 */
class ApiProblemTest extends TestCase
{

    public function testConstructor() : void
    {
        $problem = new ApiProblem('Title', 'URI');
        $this->assertEquals("Title", $problem->getTitle());
        $this->assertEquals("URI", $problem->getType());
    }

    public function testConstructorWithDefaults() : void
    {
        $problem = new ApiProblem();
        $this->assertSame('', $problem->getTitle());
        $this->assertSame('about:blank', $problem->getType());
        $this->assertSame('', $problem->getDetail());
        $this->assertSame('', $problem->getInstance());
        $this->assertSame('', $problem->getTitle());
    }

    public function testSimpleExtraProperty() : void
    {
        $problem = new ApiProblem('Title', 'URI');

        $problem['sir'] = 'Gir';
        $this->assertEquals('Gir', $problem['sir']);

        unset($problem['sir']);
        $this->assertFalse(isset($problem['sir']));
        $this->assertNull($problem['sir']);
    }

    public function testComplexExtraProperty() : void
    {
        $problem = new ApiProblem('Title', 'URI');

        $problem['irken']['invader'] = 'Zim';
        $this->assertTrue(isset($problem['irken']['invader']));
        $this->assertEquals('Zim', $problem['irken']['invader']);
    }

    public function testSimpleJsonCompileWithJsonException() : void
    {
        $this->expectException(JsonEncodeException::class);
        $this->expectExceptionCode(\JSON_ERROR_UTF8);
        $this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');

        // an invalid string value
        $problem = new ApiProblem(\hex2bin('58efa99d4e19ff4e93efd93f7afb10a5'), 'URI');

        $json = $problem->asJson();

        // This line should throw an exception.
        $result = json_decode($json, true);
    }

    public function testSimpleJsonCompile() : void
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

    public function testExtraPropertyJsonCompile() : void
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
     *
     * @doesNotPerformAssertions
     */
    public function testNoTitleAllowed() : void
    {
        // This should result in no error.
        $problem = new ApiProblem();
        $json = $problem->asJson();
    }

    /**
     * Confirms that the type property defaults to "about:blank"
     */
    public function testTypeDefault() : void
    {
        $problem = new ApiProblem('Title');
        $json = $problem->asJson();
        $result = json_decode($json, true);
        $this->assertEquals('about:blank', $result['type']);
    }

    public function testSimpleXmlCompile() : void
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

    public function testExtraPropertyXmlCompile() : void
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
        foreach ($invader as $node) {
            $this->assertEquals('Zim', $node);
        }
    }

    public function testParseJsonWithEmptyString() : void
    {
        $this->expectException(JsonParseException::class);
        $this->expectExceptionCode(\JSON_ERROR_SYNTAX);
        $this->expectExceptionMessage('An empty string is not a valid JSON value');

        ApiProblem::fromJson('');
    }

    public function testParseJsonWithInvalidString() : void
    {
        $this->expectException(JsonParseException::class);
        $this->expectExceptionCode(\JSON_ERROR_SYNTAX);
        $this->expectExceptionMessage('Syntax error, malformed JSON');

        ApiProblem::fromJson(\hex2bin('58efa99d4e19ff4e93efd93f7afb10a5'));
    }

    public function testParseJson() : void
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(403);
        $problem->setInstance('Instance');
        $problem->setDetail('Detail');
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $result = ApiProblem::fromJson($problem->asJson());

        $this->assertEquals('Title', $result->getTitle());
        $this->assertEquals('Instance', $result->getInstance());
        $this->assertEquals('Detail', $result->getDetail());
        $this->assertEquals(403, $result->getStatus());
        $this->assertEquals('Gir', $result['sir']);
        $this->assertEquals('Zim', $result['irken']['invader']);
    }

    public function testParseXml() : void
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

    public function testArray() : void
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

    public function testPrettyPrintJson() : void
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(403);
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $json = $problem->asJson(true);
        $this->assertTrue(strpos($json, '  ') !== FALSE);
    }
}

