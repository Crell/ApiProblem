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
