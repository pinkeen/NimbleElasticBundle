<?php

namespace Nimble\ElasticBundle\Exception;

class UnexpectedTypeException extends \InvalidArgumentException
{
    /**
     * @param mixed $value
     * @param string $expectedType
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($value, $expectedType, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Argument of type "%s" expected but "%s" was given.',
            $expectedType,
            is_object($value) ? get_class($value) : gettype($value)
        );

        parent::__construct($message, $code, $previous);
    }
}