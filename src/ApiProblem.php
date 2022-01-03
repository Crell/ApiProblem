<?php

declare(strict_types=1);

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
 * @author Larry Garfield
 *
 * @implements \ArrayAccess<string, mixed> \JsonSerializable
 */
class ApiProblem implements \ArrayAccess, \JsonSerializable
{

    /**
     * The content type for a JSON based HTTP response carrying
     * problem details.
     *
     * @var string
     */
    public const CONTENT_TYPE_JSON = 'application/problem+json';

    /**
     * The content type for a XML based HTTP response carrying
     * problem details.
     *
     * @var string
     */
    public const CONTENT_TYPE_XML = 'application/problem+xml';

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
    protected $status = 0;

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
    protected $detail = '';

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
    protected $instance = '';

    /**
     * Any arbitrary extension properties that have been assigned on this object.
     *
     * @var array<string, string>
     */
    protected $extensions = [];

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
    public static function fromJson(string $json): self
    {
        if (trim($json) === '') {
            throw new JsonParseException('An empty string is not a valid JSON value', JSON_ERROR_SYNTAX, null, $json);
        }
        $parsed = json_decode($json, true);

        $lastError = json_last_error();

        if (\JSON_ERROR_NONE !== $lastError) {
            throw JsonParseException::fromJsonError($lastError, $json);
        }

        return static::decompile($parsed);
    }

    /**
     * Converts a SimpleXMLElement to a nested array.
     *
     * @param \SimpleXMLElement $element
     *   The XML
     * @return array<mixed>
     *   A nested array corresponding to the XML element provided.
     */
    protected static function xmlToArray(\SimpleXMLElement $element): array
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
    public static function fromXml(string $string): self
    {
        $xml = new \SimpleXMLElement($string);

        $data = static::xmlToArray($xml);

        return static::decompile($data);
    }

    /**
     * Parses an array into a Problem object.
     *
     * @param array<mixed> $input
     *   The array to parse.
     * @return ApiProblem
     *   A newly constructed problem object.
     */
    public static function fromArray(array $input): self
    {
        $defaultInput = ['title' => null, 'type' => null, 'status' => null, 'detail' => null, 'instance' => null];

        $data = $input + $defaultInput;

        return self::decompile($data);
    }

    /**
     * Decompiles an array into an ApiProblem object.
     *
     * @param array<mixed> $parsed
     *   An array parsed from JSON or XML to turn into an ApiProblem object.
     * @return ApiProblem
     *   A new ApiProblem object.
     */
    protected static function decompile(array $parsed) : self
    {
        // This line is fine as long as the constructor has only optional arguments. That is a requirement
        // that cannot be enforced in code, but is effectively a requirement of the class.
        // @phpstan-ignore-next-line
        $problem = new static();

        if (null !== ($title = self::filterStringValue('title', $parsed))) {
            $problem->setTitle($title);
        }

        if (null !== ($type = self::filterStringValue('type', $parsed))) {
            $problem->setType($type);
        }

        if (null !== ($status = self::filterIntValue('status', $parsed))) {
            $problem->setStatus($status);
        }

        if (null !== ($detail = self::filterStringValue('detail', $parsed))) {
            $problem->setDetail($detail);
        }

        if (null !== ($instance = self::filterStringValue('instance', $parsed))) {
            $problem->setInstance($instance);
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
     * Parse the incoming value as non empty string.
     * The returned value can be used to populate Problem string based properties.
     *
     * Skip empty string or missing values. The string 0, however is allowed.
     * PHP makes this ugly.
     * The check on string handles XML decompile which may return an empty array.
     *
     * @param string|int $key
     * @param array<int|string, mixed> $arr
     *
     * @return string|null
     */
    protected static function filterStringValue($key, array $arr): ?string
    {
        if (!array_key_exists($key, $arr) || !is_string($value = $arr[$key])) {
            return null;
        }

        if ($value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Parse the incoming value as integer
     * The returned value can be used to populate Problem integer based properties.
     *
     * If the value can be parse as an integer it is return as one
     * otherwise null is returned.
     *
     * non integer value will all be discarded float included
     * @see https://3v4l.org/vZjLD
     * The check on scalar handles XML decompile which may return an empty array.
     *
     * @param int|string $key
     * @param array<int|string, mixed> $arr
     *
     * @return int|null
     */
    protected static function filterIntValue($key, array $arr): ?int
    {
        if (!array_key_exists($key, $arr) || !is_scalar($value = $arr[$key])) {
            return null;
        }

        $intValue = intval($value);
        if (strval($value) !== strval($intValue)) {
            return null;
        }

        return $intValue;
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
    public function __construct(string $title = '', string $type = 'about:blank')
    {
        $this->title = $title;
        $this->type = $type;
    }

    /**
     * Retrieves the title of the problem.
     *
     * @return string
     *   The current title.
     */
    public function getTitle(): string
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
    public function setTitle(string $title): self
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
    public function getType(): string
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
    public function setType(string $type): self
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
    public function getDetail(): string
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
    public function setDetail(string $detail): self
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
    public function getInstance(): string
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
    public function setInstance(string $instance): self
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
    public function getStatus(): int
    {
        return $this->status;
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
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Renders this problem as JSON.
     *
     * @param bool $pretty
     *   Whether or not to pretty-print the JSON string for easier debugging.
     * @return string
     *   A JSON string representing this problem.
     */
    public function asJson(bool $pretty = false): string
    {
        $response = $this->compile();

        $options = 0;
        if ($pretty) {
            $options = \JSON_UNESCAPED_SLASHES | \JSON_PRETTY_PRINT;
        }

        $json = json_encode($response, $options);

        if (false === $json) {
            throw JsonEncodeException::fromJsonError(\json_last_error(), $response);
        }

        return $json;
    }

    /**
     * Renders this problem as XML.
     *
     * @param bool $pretty
     *   Whether or not to pretty-print the XML string for easier debugging.
     * @return string
     *   An XML string representing this problem.
     */
    public function asXml(bool $pretty = false): string
    {
        $doc = new \SimpleXMLElement('<problem></problem>');

        $this->arrayToXml($this->compile(), $doc);

        /** @var \DOMElement */
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
     * @return array<mixed>
     *   The API problem represented as an array.
     */
    public function asArray(): array
    {
        return $this->compile();
    }

    /**
     * Supports rendering this problem as a JSON using the json_encode() function.
     *
     * @return array<mixed>
     *   The API problem represented as an array for rendering.
     */
    public function jsonSerialize(): array
    {
        return $this->compile();
    }

    /**
     * Compiles the object down to an array format, suitable for serializing.
     *
     * @return array<mixed>
     *   This object, rendered to an array.
     */
    protected function compile(): array
    {
        // Start with any extensions, since that's already an array.
        $response = $this->extensions;

        // These properties are optional.
        foreach (['title', 'type', 'status', 'detail', 'instance'] as $key) {
            // Skip empty string or missing values, as they are optional.
            // The string or integer 0, however, are allowed.  PHP makes
            // this ugly.
            if (isset($this->$key) && $this->$key !== 0 && $this->$key !== '') {
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
     * @param array<mixed> $data
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

    /**
     * {@inheritdoc}
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->extensions);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $offset
     * @return mixed
     *
     * The proper return type here is `mixed`, which is only available as of 8.0.
     */
    #[\ReturnTypeWillChange]
    public function &offsetGet($offset)
    {
        return $this->extensions[$offset];
    }

    /**
     * {@inheritdoc}
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->extensions[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->extensions[$offset]);
    }
}
