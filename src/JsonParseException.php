<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

class JsonParseException extends \InvalidArgumentException
{
    /**
     * @var string
     */
    protected $json;

    public function setJson(string $json) : self
    {
        $this->json = $json;
        return $this;
    }

    public function getJson() : string
    {
        return $this->json;
    }
}
