<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

use Throwable;

class JsonParseException extends JsonException
{
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null, $failedValue = '')
    {
        parent::__construct($message, $code, $previous);
        $this->setFailedValue($failedValue);
    }
}
