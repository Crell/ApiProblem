<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

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
     * @param ResponseFactoryInterface $responseFactory
     *   An HTTP Response factory that can give us a Response object.
     * @param bool $pretty
     *   Whether or not the response body should be pretty-printed.
     */
    public function __construct(ResponseFactoryInterface $responseFactory, bool $pretty = false)
    {
        $this->responseFactory = $responseFactory;
        $this->pretty = $pretty;
    }

    /**
     * Converts a problem to a JSON HTTP Response object, provided.
     *
     * @param ApiProblem $problem
     *   The problem to convert.
     *
     * @return ResponseInterface
     *   The appropriate response object.
     */
    public function toJsonResponse(ApiProblem $problem) : ResponseInterface
    {
        $response = $this->toResponse($problem);

        $body = $response->getBody();
        $body->write($problem->asJson($this->pretty));
        $body->rewind();

        return $response
            ->withHeader('Content-Type', ApiProblem::CONTENT_TYPE_JSON)
            ->withBody($body);
    }

    /**
     * Converts a problem to an XML HTTP Response object, provided.
     *
     * @param ApiProblem $problem
     *   The problem to convert.
     *
     * @return ResponseInterface
     *   The appropriate response object.
     */
    public function toXmlResponse(ApiProblem $problem) : ResponseInterface
    {
        $response = $this->toResponse($problem);

        $body = $response->getBody();
        $body->write($problem->asXml($this->pretty));
        $body->rewind();

        return $this->toResponse($problem)
            ->withHeader('Content-Type', ApiProblem::CONTENT_TYPE_XML)
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
    protected function toResponse(ApiProblem $problem) : ResponseInterface
    {
        $status = $problem->getStatus() ?: 500;

        return $this->responseFactory->createResponse($status);
    }
}
