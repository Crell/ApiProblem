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
 * An API error of some form.
 *
 * This object generates errors in compliance with RFC 7807 "API Problem".
 *
 * This object should be configured via the appropriate methods, and then
 * rendered using the asJson() or asXml() methods. The resulting string is
 * safe to then send in response to an HTTP request. When sent, the response
 * should have a mime type of application/problem+json or
 * application/problem+xml, as appropriate.
 *
 * Subclassing this class to provide defaults for different problem types for
 * your application is encouraged.
 *
 * For problem properties defined by the specification, use the methods provided
 * to get/set those values. For extended values, use the ArrayAccess interface
 * to specify arbitrary additional properties.
 *
 * @link http://tools.ietf.org/html/rfc7807
 *
 * @autor Larry Garfield
 */
class ApiProblem implements \ArrayAccess
{

    /**
     *  A short, human-readable summary of the problem type.
     *
     *  It SHOULD NOT change from occurrence to occurrence of the problem,
     *  except for purposes of localization.
     *
     * @var string
     */
    protected $title;

    /**
     * A URI reference (RFC3986) that identifies the problem type.
     *
     * This specification encourages that, when dereferenced, it provide
     * human-readable documentation for the problem type (e.g., using HTML
     * [W3C.REC-html5-20141028]).  When this member is not present, its value
     * is assumed to be "about:blank".
     *
     * Consumers MUST use the type string as the primary identifier for the
     * problem type.
     *
     * This value may be an absolute or or relative URI. If relative, it MUST be
     * resolved relative to the document's base URI, as per RFC3986, Section 5.
     *
     * @link http://tools.ietf.org/html/rfc3986
     *
     * @var string
     */
    protected $type;

    /**
     * The HTTP status code set by the origin server for this occurrence of the problem.
     *
     * The status member, if present, is only advisory; it conveys the HTTP
     * status code used for the convenience of the consumer. Generators MUST
     * use the same status code in the actual HTTP response, to assure that
     * generic HTTP software that does not understand this format still behaves
     * correctly.
     *
     * @var int
     */
    protected $status;

    /**
     * An human readable explanation specific to this occurrence of the problem.
     *
     * The "detail" member, if present, ought to focus on helping the client
     * correct the problem, rather than giving debugging information.
     *
     * Consumers SHOULD NOT parse the "detail" member for information; extensions
     * are more suitable and less error-prone ways to obtain such information.
     *
     * @var string
     */
    protected $detail;

    /**
     * A URI reference that identifies the specific occurrence of the problem.
     *
     * It may or may not yield further information if dereferenced.
     *
     * This value may be an absolute or or relative URI. If relative, it MUST be
     * resolved relative to the document's base URI, as per RFC3986, Section 5.
     *
     * @link http://tools.ietf.org/html/rfc3986
     *
     * @var string
     */
    protected $instance;

    /**
     * Any arbitrary extension properties that have been assigned on this object.
     *
     * @var array
     */
    protected $extensions = array();

    /**
     * Parses a JSON string into a Problem object.
     *
     * @param string $json
     *   The JSON string to parse.
     * @return ApiProblem
     *   A newly constructed problem object.
     *
     * @throws JsonParseException
     *   Invalid JSON strings will result in a thrown exception.
     */
    public static function fromJson($json)
    {
        if (empty($json)) {
            throw (new JsonParseException('An empty string is not a valid JSON value', JSON_ERROR_SYNTAX))->setJson($json);
        }
        $parsed = json_decode($json, true);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return static::decompile($parsed);
                break;
            case JSON_ERROR_DEPTH:
                throw (new JsonParseException('Maximum stack depth exceeded', JSON_ERROR_DEPTH))->setJson($json);
                break;
            case JSON_ERROR_STATE_MISMATCH:
                throw (new JsonParseException('Underflow or the modes mismatch', JSON_ERROR_STATE_MISMATCH))->setJson($json);
                break;
            case JSON_ERROR_CTRL_CHAR:
                throw (new JsonParseException('Unexpected control character found', JSON_ERROR_CTRL_CHAR))->setJson($json);
                break;
            case JSON_ERROR_SYNTAX:
                throw (new JsonParseException('Syntax error, malformed JSON', JSON_ERROR_SYNTAX))->setJson($json);
                break;
            case JSON_ERROR_UTF8:
                throw (new JsonParseException('Malformed UTF-8 characters, possibly incorrectly encoded', JSON_ERROR_UTF8))->setJson($json);
                break;
            default:
                throw (new JsonParseException('Unknown error'))->setJson($json);
                break;
        }
    }

    /**
     * Converts a SimpleXMLElement to a nested array.
     *
     * @param \SimpleXMLElement $element
     *   The XML
     * @return array
     *   A nested array corresponding to the XML element provided.
     */
    protected static function xmlToArray(\SimpleXMLElement $element)
    {
        $data = (array)$element;
        foreach ($data as $key => $value) {
            if ($value instanceof \SimpleXMLElement) {
                $data[$key] = static::xmlToArray($value);
            }
        }

        return $data;
    }

    /**
     * Parses an XML string into a Problem object.
     *
     * @param string $string
     *   The XML string to parse.
     * @return ApiProblem
     *   A newly constructed problem object.
     */
    public static function fromXml($string)
    {
        $xml = new \SimpleXMLElement($string);

        $data = static::xmlToArray($xml);

        return static::decompile($data);
    }

    /**
     * Decompiles an array into an ApiProblem object.
     *
     * @param array $parsed
     *   An array parsed from JSON or XML to turn into an ApiProblem object.
     * @return ApiProblem
     *   A new ApiProblem object.
     */
    protected static function decompile(array $parsed)
    {
        $problem = new static();


        if (!empty($parsed['title'])) {
            $problem->setTitle($parsed['title']);
        }
        if (!empty($parsed['type'])) {
            $problem->setType($parsed['type']);
        }
        if (!empty($parsed['status'])) {
            $problem->setStatus($parsed['status']);
        }
        if (!empty($parsed['detail'])) {
            $problem->setDetail($parsed['detail']);
        }
        if (!empty($parsed['instance'])) {
            $problem->setInstance($parsed['instance']);
        }

        // Remove the defined keys. That means whatever is left must be a custom
        // extension property.
        unset($parsed['title'], $parsed['type'], $parsed['status'], $parsed['detail'], $parsed['instance']);

        foreach ($parsed as $key => $value) {
            $problem[$key] = $value;
        }

        return $problem;
    }

    /**
     * Constructs a new ApiProblem.
     *
     * @param string $title
     *   A short, human-readable summary of the problem type.  It SHOULD NOT
     *   change from occurrence to occurrence of the problem, except for
     *   purposes of localization.
     * @param string $type
     *   An absolute URI (RFC3986) that identifies the problem type.  When
     *   dereferenced, it SHOULD provide human-readable documentation for the
     *   problem type (e.g., using HTML).
     */
    public function __construct($title = '', $type = 'about:blank')
    {
        if ($title) {
            $this->title = $title;
        }
        if ($type) {
            $this->type = $type;
        }
    }

    /**
     * Retrieves the title of the problem.
     *
     * @return string
     *   The current title.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title for this problem.
     *
     * @param string $title
     *   The title to set.
     *  @return ApiProblem
     *   The invoked object.
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Retrieves the problem type of this problem.
     *
     * @return string
     *   The problem type URI of this problem.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the problem type of this problem.
     *
     * @param string $type
     *   The resolvable problem type URI of this problem.
     * @return ApiProblem
     *   The invoked object.
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Retrieves the detail information of the problem.
     *
     * @return string
     *   The detail of this problem.
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Sets the detail for this problem.
     *
     * @param string $detail
     *   The human-readable detail string about this problem.
     * @return ApiProblem
     *   The invoked object.
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
        return $this;
    }

    /**
     * Returns the problem instance URI of this problem.
     *
     * @return string
     *   The problem instance URI of this problem.
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Sets the problem instance URI of this problem.
     *
     * @param string $instance
     *   An absolute URI that uniquely identifies this problem. It MAY link to
     *   further information about the error, but that is not required.
     *
     * @return ApiProblem
     *   The invoked object.
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
        return $this;
    }

    /**
     * Returns the current HTTP status code.
     *
     * @return int
     *   The current HTTP status code. If not set, it will return 0.
     */
    public function getStatus()
    {
        return $this->status ?: 0;
    }

    /**
     * Sets the HTTP status code for this problem.
     *
     * It is an error for this value to be set to a different value than the
     * actual HTTP response code.
     *
     * @param int $status
     *   A valid HTTP status code.
     * @return ApiProblem
     *   The invoked object.
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Renders this problem as JSON.
     *
     * @param boolean $pretty
     *   Whether or not to pretty-print the JSON string for easier debugging.
     * @return string
     *   A JSON string representing this problem.
     */
    public function asJson($pretty = false)
    {
        $response = $this->compile();

        $options = 0;
        if ($pretty) {
            $options = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
        }

        return json_encode($response, $options);
    }

    /**
     * Renders this problem as XML.
     *
     * @param boolean $pretty
     *   Whether or not to pretty-print the XML string for easier debugging.
     * @return string
     *   An XML string representing this problem.
     */
    public function asXml($pretty = false)
    {
        $doc = new \SimpleXMLElement('<problem></problem>');

        $this->arrayToXml($this->compile(), $doc);

        $dom = dom_import_simplexml($doc);
        if ($pretty) {
            $dom->ownerDocument->preserveWhiteSpace = false;
            $dom->ownerDocument->formatOutput = true;
        }
        return $dom->ownerDocument->saveXML();
    }

    /**
     * Renders this problem as a native PHP array.
     *
     * This is mostly useful for debugging, or for placing
     * this problem response into, say, a Symfony JsonResponse object.
     *
     * @return array
     *   The API problem represented as an array.
    */
    public function asArray()
    {
        return $this->compile();
    }

    /**
     * Compiles the object down to an array format, suitable for serializing.
     *
     * @return array
     *   This object, rendered to an array.
     */
    protected function compile()
    {
        // Start with any extensions, since that's already an array.
        $response = $this->extensions;

        // These properties are optional.
        foreach (array('title', 'type', 'status', 'detail', 'instance') as $key) {
            if (!empty($this->$key)) {
                $response[$key] = $this->$key;
            }
        }

        return $response;
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
     * @param \SimpleXmlElement $element
     *   The XML object to which to add data.
     * @param mixed $parent
     *   Used for internal recursion only.
     */
    protected function arrayToXml(array $data, \SimpleXmlElement $element, $parent = null)
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
                    if (substr($key, 0, 1) === '@') {
                        $element->addAttribute(substr($key, 1), $value);
                    } elseif ($key === 'value') {
                        $element->{0} = $value;
                    } elseif (is_bool($value)) {
                        $element->addChild($key, intval($value));
                    } else {
                        $element->addChild($key, htmlspecialchars($value, ENT_QUOTES));
                    }
                } else {
                    $element->addChild($parent, htmlspecialchars($value, ENT_QUOTES));
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->extensions);
    }

    /**
     * {@inheritdoc}
     */
    public function &offsetGet($offset)
    {
        return $this->extensions[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->extensions[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->extensions[$offset]);
    }
}
