<?php


namespace Crell\ApiProblem\Test;


use Crell\ApiProblem\ApiProblem;
use Crell\ApiProblem\HttpConverter;
use Zend\Diactoros\Response;

class HttpConverterTest extends \PHPUnit_Framework_TestCase
{

    public function testToJson()
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(404);
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $converter = new HttpConverter();
        $response = $converter->toJsonResponse($problem, new Response());

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeaderLine('Content-Type'));
        $returned_problem = ApiProblem::fromJson($response->getBody()->getContents());

        $this->assertEquals('Title', $returned_problem->getTitle());
        $this->assertEquals('URI', $returned_problem->getType());
        $this->assertEquals(404, $returned_problem->getStatus());
        $this->assertEquals('Gir', $returned_problem['sir']);
        $this->assertEquals('Zim', $returned_problem['irken']['invader']);
    }

    public function testToXml()
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(404);
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $converter = new HttpConverter();
        $response = $converter->toXmlResponse($problem, new Response());

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+xml', $response->getHeaderLine('Content-Type'));
        $returned_problem = ApiProblem::fromXml($response->getBody()->getContents());

        $this->assertEquals('Title', $returned_problem->getTitle());
        $this->assertEquals('URI', $returned_problem->getType());
        $this->assertEquals(404, $returned_problem->getStatus());
        $this->assertEquals('Gir', $returned_problem['sir']);
        $this->assertEquals('Zim', $returned_problem['irken']['invader']);
    }
}
