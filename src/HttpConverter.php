<?php

namespace Crell\ApiProblem;

use Psr\Http\Message\ResponseInterface;

use Psr\Http\Message\ResponseFactoryInterface;

/**
 * Utility class to convert a problem object to an HTTP Response, using PSR-7/17.
 */
class HttpConverter
{
    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

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
    public function __construct(ResponseFactoryInterface $responseFactory, $pretty = false)
    {
        $this->responseFactory = $responseFactory;
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
    public function toJsonResponse(ApiProblem $problem)
    {
        $response = $this->toResponse($problem);

        $body = $response->getBody();
        $body->write($problem->asJson($this->pretty));

        return $response
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
    public function toXmlResponse(ApiProblem $problem)
    {
        $response = $this->toResponse($problem);

        $body = $response->getBody();
        $body->write($problem->asJson($this->pretty));

        return $this->toResponse($problem)
            ->withHeader('Content-Type', 'application/problem+xml')
            ->withBody($body);
    }

    /**
     * Converts a problem to a provided Response, without the format-sensitive bits.
     *
     * @param ApiProblem $problem
     *   The problem to convert.
     *
     * @return ResponseInterface
     *   The appropriate response object.
     */
    protected function toResponse(ApiProblem $problem)
    {
        $status = $problem->getStatus() ?: 500;

        $response = $this->responseFactory->createResponse($status);

        return $response;
    }
}
