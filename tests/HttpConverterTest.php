<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response;

class HttpConverterTest extends TestCase
{

    protected function getMockResponseFactory() : ResponseFactoryInterface
    {
        return new class implements ResponseFactoryInterface
        {
            public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
            {
                return new Response('php://memory', $code);
            }
        };
    }

    public function testToJson() : void
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(404);
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $converter = new HttpConverter($this->getMockResponseFactory());
        $response = $converter->toJsonResponse($problem);

        self::assertEquals(404, $response->getStatusCode());
        self::assertEquals('application/problem+json', $response->getHeaderLine('Content-Type'));
        $returned_problem = ApiProblem::fromJson($response->getBody()->getContents());

        self::assertEquals('Title', $returned_problem->getTitle());
        self::assertEquals('URI', $returned_problem->getType());
        self::assertEquals(404, $returned_problem->getStatus());
        self::assertEquals('Gir', $returned_problem['sir']);
        self::assertEquals('Zim', $returned_problem['irken']['invader']);
    }

    public function testToXml() : void
    {
        $problem = new ApiProblem('Title', 'URI');
        $problem->setStatus(404);
        $problem['sir'] = 'Gir';
        $problem['irken']['invader'] = 'Zim';

        $converter = new HttpConverter($this->getMockResponseFactory());
        $response = $converter->toXmlResponse($problem);

        self::assertEquals(404, $response->getStatusCode());
        self::assertEquals('application/problem+xml', $response->getHeaderLine('Content-Type'));
        $returned_problem = ApiProblem::fromXml($response->getBody()->getContents());

        self::assertEquals('Title', $returned_problem->getTitle());
        self::assertEquals('URI', $returned_problem->getType());
        self::assertEquals(404, $returned_problem->getStatus());
        self::assertEquals('Gir', $returned_problem['sir']);
        self::assertEquals('Zim', $returned_problem['irken']['invader']);
    }
}
