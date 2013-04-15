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
 * Exception thrown when one of the required properties is not set.
 *
 * @author Larry Garield
 */
class RequiredPropertyNotFoundException extends \UnexpectedValueException
{
}
