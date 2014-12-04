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

use Exception;

/**
 * An exception wrapping an API error of some form.
 *
 * This wraps an ApiProblem instance in an Exception. This can be helpful when
 * working in systems where you want to deal with an ApiProblem instance
 * directly but pass it around by way of PHP's exceptions.
 *
 * One use case is for writing helpers for code that may encouter ApiProblem
 * instances. These can be difficult to handle as return values since whatever
 * method you are writing probably has another expected return type.
 *
 * Rather than affording the possiblity of returning the known type and
 * ApiProblem, the ApiProblem can be wrapped and thrown. This works nicely with
 * features like PHPUnit's @expectedException.
 *
 * <code>
 * \@expectedException        Crell\ApiProblem\ApiProblemException
 * \@expectedExceptionCode    404
 * \@expectedExceptionMessage The requested Widge resource could not be found.
 * </code>
 *
 * @author Beau Simensen
 */
class ApiProblemException extends Exception
{
    /**
     * @var ApiProblem
     */
    private $apiProblem;

    public function __construct(ApiProblem $apiProblem)
    {
        parent::__construct($apiProblem->getTitle(), $apiProblem->getStatus());

        $this->apiProblem = $apiProblem;
    }

    public function getApiProblem()
    {
        return $this->apiProblem;
    }
}
