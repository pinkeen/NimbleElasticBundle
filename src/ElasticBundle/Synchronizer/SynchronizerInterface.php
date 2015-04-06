<?php
namespace Nimble\ElasticBundle\Synchronizer;

interface SynchronizerInterface
{
    /**
     * Create a document in ES.
     */
    const ACTION_CREATE = "create";

    /**
     * Update a document in ES.
     */
    const ACTION_UPDATE = "update";

    /**
     * Delete a document in ES.
     */
    const ACTION_DELETE = "delete";
    
    /**
     * @param $entity
     */
    public function synchronizeCreate($entity);

    /**
     * @param $entity
     */
    public function synchronizeUpdate($entity);

    /**
     * @param $entity
     */
    public function synchronizeDelete($entity);

    /**
     * @return string
     */
    public function getClassName();
}
