<?php

namespace Nimble\ElasticBundle\Synchronizer;

use Nimble\ElasticBundle\Exception\UnexpectedTypeException;
use Nimble\ElasticBundle\Synchronizer\Exception\InvalidSynchronizationAction;
use Nimble\ElasticBundle\Transformer\TransformerManager;
use Nimble\ElasticBundle\Type\Type;

class Synchronizer implements SynchronizerInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var string
     */
    private $onCreate;

    /**
     * @var string
     */
    private $onUpdate;

    /**
     * @var string
     */
    private $onDelete;

    /**
     * @var TransformerManager
     */
    private $transformer;

    /**
     * @param string $className
     * @param Type $type
     * @param string $onCreate
     * @param string $onUpdate
     * @param string $onDelete
     * @param TransformerManager $transformer
     */
    public function __construct(
        $className,
        Type $type,
        $onCreate,
        $onUpdate,
        $onDelete,
        TransformerManager $transformer
    ) {
        $this->className = $className;
        $this->type = $type;
        $this->onCreate = $onCreate;
        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
        $this->transformer = $transformer;
    }

    /**
     * @param string$action
     */
    protected function validateAction($action)
    {
        if (!$action) {
            return;
        }

        if (!in_array($action, [self::ACTION_CREATE, self::ACTION_UPDATE, self::ACTION_DELETE])) {
            throw new InvalidSynchronizationAction($action);
        }
    }

    /**
     * @param object $entity
     */
    protected function validateClass($entity)
    {
        if (!is_a($entity, $this->className)) {
            throw new UnexpectedTypeException($entity, $this->className);
        }
    }

    /**
     * @param $action
     * @param $entity
     */
    protected function performAction($action, $entity)
    {
        $this->validateClass($entity);

        switch ($action) {
            case self::ACTION_CREATE:
            case self::ACTION_UPDATE:
                $documents = $this->transformer->transformToDocuments(
                    $entity,
                    $this->type->getIndex()->getName(),
                    $this->type->getName()
                );

                $this->type->putDocuments($documents);

                break;

            case self::ACTION_DELETE:
                $ids = $this->transformer->transformToIds(
                    $entity,
                    $this->type->getIndex()->getName(),
                    $this->type->getName()
                );

                $this->type->deleteDocuments($ids);
        }
    }

    /**
     * @param $entity
     */
    public function synchronizeCreate($entity)
    {
        if ($this->onCreate) {
            $this->performAction($this->onCreate, $entity);
        }
    }

    /**
     * @param $entity
     */
    public function synchronizeUpdate($entity)
    {
        if ($this->onUpdate) {
            $this->performAction($this->onUpdate, $entity);
        }
    }

    /**
     * @param $entity
     */
    public function synchronizeDelete($entity)
    {
        if ($this->onDelete) {
            $this->performAction($this->onDelete, $entity);
        }
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}
