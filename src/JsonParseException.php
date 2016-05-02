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

    protected $json;

    public function setJson($json)
    {
        $this->json = $json;
        return $this;
    }

    public function getJson()
    {
        return $this->json;
    }
}
