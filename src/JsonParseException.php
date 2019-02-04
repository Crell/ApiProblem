<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

class JsonParseException extends JsonException
{
    /**
     * JsonParseException constructor.
     *
     * This version forces a string for $failedValue, as that's the only thing that
     * could fail to parse, since that's all you can even try to parse.
     */
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null, string $failedValue = '')
    {
        parent::__construct($message, $code, $previous);
        $this->setFailedValue($failedValue);
    }
}
