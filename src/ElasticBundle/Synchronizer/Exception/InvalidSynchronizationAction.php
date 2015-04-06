<?php

namespace Nimble\ElasticBundle\Synchronizer\Exception;

use Nimble\ElasticBundle\Synchronizer\Synchronizer;

class InvalidSynchronizationAction extends \RuntimeException
{
    /**
     * @param string $gotAction
     */
    public function __construct($gotAction)
    {
        parent::__construct(sprintf('Action must be one of [%s], got "%s"',
            implode(', ', [
                Synchronizer::ACTION_CREATE,
                Synchronizer::ACTION_UPDATE,
                Synchronizer::ACTION_DELETE
            ]),
            $gotAction
        ));
    }

}