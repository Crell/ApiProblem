<?php

declare(strict_types=1);

namespace Crell\ApiProblem;

/**
 * An API error of some form.
 *
 * This object generates errors in compliance with RFC 7807 "API Problem".
 *
 * This object should be configured via the appropriate methods, and then
 * rendered using the asArray() or jsonSerialize() methods.
 *
 * For problem properties defined by the specification, use the methods provided
 * to get/set those values.
 *
 * @link http://tools.ietf.org/html/rfc7807
 */
interface Problem
{
    /**
     * Retrieves the problem type of this problem.
     */
    public function getType(): string;

    /**
     * Retrieves the title of the problem.
     */
    public function getTitle(): string;

    /**
     * Returns the current HTTP status code. If not set, it will return 0.
     */
    public function getStatus(): int;

    /**
     * Returns the problem instance URI of this problem.
     */
    public function getInstance(): string;

    /**
     * Retrieves the detail information of the problem.
     */
    public function getDetail(): string;

    /**
     * Retrieves the extensions members attached to the problem.
     *
     * @return array The extensions members of this problem.
     */
    public function getExtensions(): array;

    /**
     * Renders this problem as a native PHP array.
     *
     * @return array The API problem represented as an array.
     */
    public function asArray() : array;

    /**
     * Sets the problem type of this problem.
     */
    public function setType(string $type): Problem;

    /**
     * Sets the title for this problem.
     */
    public function setTitle(string $title): Problem;

    /**
     * Sets the detail for this problem.
     */
    public function setDetail(string $detail): Problem;

    /**
     * Sets the problem instance URI of this problem.
     *
     * An absolute URI that uniquely identifies this problem. It MAY link to
     *  further information about the error, but that is not required.
     */
    public function setInstance(string $instance): Problem;

    /**
     * Sets the HTTP status code for this problem.
     *
     * It is an error for this value to be set to a different value than the
     * actual HTTP response code.
     */
    public function setStatus(int $status): Problem;

    /**
     * Sets the problem extensions of this problem.
     *
     * @param iterable $extensions A collection of extensions member.
     */
    public function setExtensions(iterable $extensions): Problem;
}
