<?php

namespace Crell\ApiProblem;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Stream;

/**
 * Utility class to convert a problem object to an HTTP Response, using PSR-7.
 */
class HttpConverter
{
    /**
     * Whether or not the response body should be "pretty-printed".
     *
     * @var bool
     */
    protected $pretty;

    /**
     * HttpConverter constructor.
     *
     * @param bool $pretty
     *   Whether or not the response body should be pretty-printed.
     */
    public function __construct($pretty = false)
    {
        $this->pretty = $pretty;
    }

    /**
     * Converts a problem to a JSON HTTP Response object, provided.
     *
     * @param ApiProblem $problem
     *   The problem to convert.
     * @param ResponseInterface $response
     *   The Response object to populate.
     *
     * @return ResponseInterface
     *   The appropriate response object.
     */
    public function toJsonResponse(ApiProblem $problem, ResponseInterface $response)
    {
        $body = $response->getBody();
        $body->rewind();
        $body->write($problem->asJson($this->pretty));


        return $this->toResponse($problem, $response)
            ->withHeader('Content-Type', 'application/problem+json')
            ->withBody($body);
    }

    /**
     * Converts a problem to an XML HTTP Response object, provided.
     *
     * @param ApiProblem $problem
     *   The problem to convert.
     * @param ResponseInterface $response
     *   The Response object to populate.
     *
     * @return ResponseInterface
     *   The appropriate response object.
     */
    public function toXmlResponse(ApiProblem $problem, ResponseInterface $response)
    {
        // @todo Figure out why Diactoros' stream implementation isn't handling
        // this for us, and for that matter is there a way to avoid relying on
        // Diactoros?
        $stream = fopen('php://temp', 'w');
        fwrite($stream, $problem->asXml($this->pretty));
        rewind($stream);

        return $this->toResponse($problem, $response)
            ->withHeader('Content-Type', 'application/problem+xml')
            ->withBody(new Stream($stream));
    }

    /**
     * Converts a problem to a provided Response, without the format-sensitive bits.
     *
     * @param ApiProblem $problem
     *   The problem to convert.
     * @param ResponseInterface $response
     *   The Response object to populate.
     *
     * @return ResponseInterface
     *   The appropriate response object.
     */
    protected function toResponse(ApiProblem $problem, ResponseInterface $response)
    {
        if ($status = $problem->getStatus()) {
            $response = $response->withStatus($status);
        }

        return $response;
    }
}
