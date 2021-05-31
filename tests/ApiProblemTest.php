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
        self::assertEquals("Title", $problem->getTitle());
        self::assertEquals("URI", $problem->getType());
    }

    public function testConstructorWithDefaults() : void
    {
        $problem = new ApiProblem();
        self::assertSame('', $problem->getTitle());
        self::assertSame('about:blank', $problem->getType());
        self::assertSame('', $problem->getDetail());
        self::assertSame('', $problem->getInstance());
        self::assertSame('', $problem->getTitle());
    }

    public function testSimpleExtraProperty() : void
    {
        $problem = new ApiProblem('Title', 'URI');

        $problem['sir'] = 'Gir';
        self::assertEquals('Gir', $problem['sir']);

        unset($problem['sir']);
        self::assertFalse(isset($problem['sir']));
        self::assertNull($problem['sir']);
    }

    public function testComplexExtraProperty() : void
    {
        $problem = new ApiProblem('Title', 'URI');

        $problem['irken']['invader'] = 'Zim';
        self::assertTrue(isset($problem['irken']['invader']));
        self::assertEquals('Zim', $problem['irken']['invader']);
    }

    public function testSimpleJsonCompileWithJsonException() : void
    {
        self::expectException(JsonEncodeException::class);
        self::expectExceptionCode(\JSON_ERROR_UTF8);
        self::expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');

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

        self::assertArrayHasKey('title', $result);
        self::assertEquals('Title', $result['title']);
        self::assertArrayHasKey('type', $result);
        self::assertEquals('URI', $result['type']);

        // Ensure that empty properties are not included.
        self::assertArrayNotHasKey('detail', $result);
    }

    public function testExtraPropertyJsonCompile() : void
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $json = $problem->asJson();
        $result = json_decode($json, true);

        self::assertArrayHasKey('sir', $result);
        self::assertEquals('Gir', $result['sir']);
        self::assertArrayHasKey('irken', $result);
        self::assertArrayHasKey('invader', $result['irken']);
        self::assertEquals('Zim', $result['irken']['invader']);
    }

    public function testSimpleJsonEncode() : void
    {
        $problem = new ApiProblem('Title', 'URI');

        $json = json_encode($problem);
        $result = json_decode($json, true);

        self::assertArrayHasKey('title', $result);
        self::assertEquals('Title', $result['title']);
        self::assertArrayHasKey('type', $result);
        self::assertEquals('URI', $result['type']);

        // Ensure that empty properties are not included.
        self::assertArrayNotHasKey('detail', $result);
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
        self::assertEquals('about:blank', $result['type']);
    }

    public function testSimpleXmlCompile() : void
    {
        $problem = new ApiProblem('Title', 'URI');

        $xml = $problem->asXml();
        $result = simplexml_load_string($xml);

        self::assertEquals('problem', $result->getName());
        $dom = dom_import_simplexml($result);

        $titles = $dom->getElementsByTagName('title');
        self::assertEquals(1, $titles->length);
        self::assertEquals('Title', $titles->item(0)->textContent);
    }

    public function testExtraPropertyXmlCompile() : void
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $xml = $problem->asXml(true);
        $result = simplexml_load_string($xml);

        self::assertEquals('problem', $result->getName());
        $dom = dom_import_simplexml($result);

        $titles = $dom->getElementsByTagName('title');
        self::assertEquals(1, $titles->length);
        self::assertEquals('Title', $titles->item(0)->textContent);

        $sir = $dom->getElementsByTagName('sir');
        self::assertEquals(1, $sir->length);
        self::assertEquals('Gir', $sir->item(0)->textContent);

        $invader = $result->xpath('/problem/irken/invader');
        self::assertCount(1, $invader);
        foreach ($invader as $node) {
            self::assertEquals('Zim', $node);
        }
    }

    public function testParseJsonWithEmptyString() : void
    {
        self::expectException(JsonParseException::class);
        self::expectExceptionCode(\JSON_ERROR_SYNTAX);
        self::expectExceptionMessage('An empty string is not a valid JSON value');

        ApiProblem::fromJson('');
    }

    public function testParseJsonWithInvalidString() : void
    {
        self::expectException(JsonParseException::class);
        self::expectExceptionCode(\JSON_ERROR_SYNTAX);
        self::expectExceptionMessage('Syntax error, malformed JSON');

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

        self::assertEquals('Title', $result->getTitle());
        self::assertEquals('Instance', $result->getInstance());
        self::assertEquals('Detail', $result->getDetail());
        self::assertEquals(403, $result->getStatus());
        self::assertEquals('Gir', $result['sir']);
        self::assertEquals('Zim', $result['irken']['invader']);
    }

    public function testParseXml() : void
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(403);
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $result = ApiProblem::fromXml($problem->asXml());

        self::assertEquals('Title', $result->getTitle());
        self::assertEquals(403, $result->getStatus());
        self::assertEquals('Gir', $result['sir']);
        self::assertEquals('Zim', $result['irken']['invader']);
    }

    public function testArray() : void
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(403);
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $array = $problem->asArray();
        self::assertEquals('Gir', $array['sir']);
        self::assertEquals(403, $array['status']);
        self::assertEquals('Title', $array['title']);
        self::assertEquals('URI', $array['type']);
        self::assertEquals('Zim', $array['irken']['invader']);
    }

    public function testPrettyPrintJson() : void
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(403);
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $json = $problem->asJson(true);
        self::assertTrue(strpos($json, '  ') !== FALSE);
    }

    public function testParseFromArrayWithEmptyArray() : void
    {
        $problem = new ApiProblem();
        $problemFromArray = ApiProblem::fromArray([]);

        self::assertEquals($problem, $problemFromArray);
    }

    public function testParseFromArray() : void
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(403);
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $newProblem = ApiProblem::fromArray($problem->asArray());

        self::assertEquals($problem['sir'], $newProblem['sir']);
        self::assertEquals($problem->getTitle(), $newProblem->getTitle());
        self::assertEquals($problem->getType(), $newProblem->getType());
        self::assertEquals($problem->getStatus(), $newProblem->getStatus());
        self::assertEquals($problem->getDetail(), $newProblem->getDetail());
        self::assertEquals($problem->getInstance(), $newProblem->getInstance());
        self::assertEquals($problem['irken']['invader'], $newProblem['irken']['invader']);
    }

    public function testFalsyFieldArePresentOnConversion(): void
    {
        $problem = new ApiProblem('0', '0');
        $problem->setStatus(0);
        $problem->setDetail('0');
        $problem->setInstance('0');

        $expected = [
            "title"=> "0",
            "type"=> "0",
            "detail"=> "0",
            "instance" => "0"
        ];

        self::assertSame($expected, $problem->asArray());
    }

    public function testStrictEmptyFieldAreRemovedOnConversion(): void
    {
        $problem = new ApiProblem('', '');
        $problem->setStatus(0);
        $problem->setDetail('');
        $problem->setInstance('');

        $expected = [];

        self::assertSame($expected, $problem->asArray());
    }

    public function testJsonWithFalsyFieldsIsCorrectlyConverted(): void
    {
        $input = '{"title":"0", "type":"", "detail":"0", "instance":"0", "status":0}';
        $expected = [
            "title"=> "0",
            "type"=> "about:blank",
            "detail"=> "0",
            "instance" => "0",
        ];

        self::assertSame($expected, ApiProblem::fromJson($input)->asArray());
    }

    public function testXmlWithFalsyFieldsIsCorrectlyConverted(): void
    {
        $xml = '<?xml version="1.0"?>'."\n".'<problem><type>0</type><status>400</status></problem>'."\n";
        $arr = ["type" => "0", "status" => 400];

        self::assertSame($arr, ApiProblem::fromXml($xml)->asArray());
        self::assertSame($xml, ApiProblem::fromArray($arr)->asXml());
    }

    public function testXmlWithEmptyTypeIsConvertedtoAboutBlank(): void
    {
        $xml = '<?xml version="1.0"?>'."\n".'<problem><type></type></problem>'."\n";
        $arr = ["type" => "about:blank"];

        self::assertSame($arr, ApiProblem::fromXml($xml)->asArray());
    }

    public function testXmlWithInvalidStatusIsSkipped(): void
    {
        $xml = '<?xml version="1.0"?>'."\n".'<problem><status>23foobar</status></problem>'."\n";
        $arr = ["type" => "about:blank"];

        self::assertSame($arr, ApiProblem::fromXml($xml)->asArray());
    }

    public function testXmlWithFloatStatusIsSkipped(): void
    {
        $xml = '<?xml version="1.0"?>'."\n".'<problem><status>2.3</status></problem>'."\n";
        $arr = ["type" => "about:blank"];

        self::assertSame($arr, ApiProblem::fromXml($xml)->asArray());
    }

    public function testItCanAcceptsIterableConstructForExtensions(): void
    {
        $data = ['foo' => 'bar', 'status' => 403];
        $extensions = new \ArrayIterator($data);

        $problem = new ApiProblem();
        $problem->setExtensions($extensions);

        self::assertSame($data, $problem->getExtensions());
    }
}

