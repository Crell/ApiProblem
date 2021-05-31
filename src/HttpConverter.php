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
     * @param Problem $problem
     *   The problem to convert.
     *
     * @return ResponseInterface
     *   The appropriate response object.
     */
    public function toJsonResponse(Problem $problem) : ResponseInterface
    {
        $response = $this
            ->toResponse($problem)
            ->withHeader('Content-Type', ApiProblem::CONTENT_TYPE_JSON)
        ;

        $content = $this->problemToJsonString($problem);

        $body = $response->getBody();
        $body->write($content);
        $body->rewind();

        return $response;
    }

    /**
     * Converts a problem to an XML HTTP Response object, provided.
     *
     * @param Problem $problem
     *   The problem to convert.
     *
     * @return ResponseInterface
     *   The appropriate response object.
     */
    public function toXmlResponse(Problem $problem) : ResponseInterface
    {
        $response = $this
            ->toResponse($problem)
            ->withHeader('Content-Type', ApiProblem::CONTENT_TYPE_XML)
        ;

        $content = $this->problemToXMLString($problem);

        $body = $response->getBody();
        $body->write($content);
        $body->rewind();

        return $response;
    }

    protected function problemToXMLString(Problem $problem): string
    {
        $doc = new \SimpleXMLElement('<problem></problem>');

        $this->arrayToXml($problem->asArray(), $doc);

        /** @var \DOMElement */
        $dom = dom_import_simplexml($doc);
        if ($this->pretty) {
            $dom->ownerDocument->preserveWhiteSpace = false;
            $dom->ownerDocument->formatOutput = true;
        }

        return $dom->ownerDocument->saveXML();
    }

    protected function problemToJsonString(Problem $problem): string
    {
        $options = 0;
        if ($this->pretty) {
            $options = \JSON_UNESCAPED_SLASHES | \JSON_PRETTY_PRINT;
        }

        return json_encode($problem->asArray(), $options);
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

    /**
     * Adds a nested array to a SimpleXML element.
     *
     * This method was shamelessly coped from the Nocarrier\Hal library:
     *
     * @link https://github.com/blongden/hal
     *
     * @param array $data
     *   The data to add to the element.
     * @param \SimpleXMLElement $element
     *   The XML object to which to add data.
     * @param mixed $parent
     *   Used for internal recursion only.
     */
    protected function arrayToXml(array $data, \SimpleXMLElement $element, $parent = null): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    if (count($value) > 0 && isset($value[0])) {
                        $this->arrayToXml($value, $element, $key);
                    } else {
                        $subnode = $element->addChild($key);
                        $this->arrayToXml($value, $subnode, $key);
                    }
                } else {
                    $subnode = $element->addChild($parent);
                    $this->arrayToXml($value, $subnode, $parent);
                }
            } else {
                if (!is_numeric($key)) {
                    if ($key[0] === '@') {
                        $element->addAttribute(substr($key, 1), $value);
                    } elseif ($key === 'value') {
                        $element->{0} = $value;
                    } elseif (is_bool($value)) {
                        $element->addChild($key, strval($value));
                    } else {
                        $element->addChild($key, htmlspecialchars((string) $value, ENT_QUOTES));
                    }
                } else {
                    $element->addChild($parent, htmlspecialchars($value, ENT_QUOTES));
                }
            }
        }
    }
}
