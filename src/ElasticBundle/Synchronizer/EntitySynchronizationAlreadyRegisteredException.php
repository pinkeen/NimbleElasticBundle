<?php

namespace Nimble\ElasticBundle\Synchronizer;

use Exception;

class EntitySynchronizationAlreadyRegisteredException extends \RuntimeException
{
    /**
     * @param string $className
     * @param string $indexName
     * @param Exception $typeName
     * @param string $action
     */
    public function __construct($action, $className, $indexName, $typeName)
    {
        $message = sprintf('Synchronization of "%s" for class "%s" in type "%s.%s" is already registered.',
            $action,
            $className,
            $indexName,
            $typeName
        );

        parent::__construct($message);
    }

}