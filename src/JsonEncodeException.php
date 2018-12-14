<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

use Throwable;

class JsonEncodeException extends AbstractJsonException
{
    /**
     * @var mixed
     */
    protected $json;

    public function __construct(string $message = '', int $code = 0, Throwable $previous = null, $json = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setJson($json);
    }

    public function setJson($json) : self
    {
        $this->json = $json;
        return $this;
    }

    public function getJson()
    {
        return $this->json;
    }

    public static function fromJsonError(int $jsonError, $json): self
    {
        return new self(static::getExceptionMessage($jsonError), $jsonError, null, $json);
    }
}
