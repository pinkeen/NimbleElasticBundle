<?php

namespace Nimble\ElasticBundle\Synchronizer;

use Nimble\ElasticBundle\Transformer\TransformerInterface;
use Nimble\ElasticBundle\Type\Type;

interface SynchronizerInterface
{
    /**
     * @param string $className
     * @param Type $type
     * @param TransformerInterface $transformer
     * @param bool $create
     * @param bool $update
     * @param bool $delete
     */
    public function registerEntitySynchronization(
        $className,
        Type $type,
        TransformerInterface $transformer,
        $create,
        $update,
        $delete
    );

    /**
     * Shall be called when an object is updated.
     *
     * @param object $object
     * @return mixed
     */
    public function update($object);

    /**
     * Shall be called when an object is updated.
     *
     * @param object $object
     * @return mixed
     */
    public function create($object);

    /**
     * Shall be called when an object is updated.
     *
     * @param object $object
     * @return mixed
     */
    public function delete($object);

    /**
     * Flushes any scheduled actions (if applicable).
     */
    public function flush();
}