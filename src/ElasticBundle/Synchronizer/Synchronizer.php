<?php

namespace Nimble\ElasticBundle\Synchronizer;

use Nimble\ElasticBundle\Document;
use Nimble\ElasticBundle\Exception\UnexpectedTypeException;
use Nimble\ElasticBundle\Synchronizer\Exception\InvalidSynchronizationAction;
use Nimble\ElasticBundle\Transformer\TransformerManager;
use Nimble\ElasticBundle\Type\Type;
use Psr\Log\LoggerInterface;

class Synchronizer implements SynchronizerInterface
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var string
     */
    protected $onCreate;

    /**
     * @var string
     */
    protected $onUpdate;

    /**
     * @var string
     */
    protected $onDelete;

    /**
     * @var TransformerManager
     */
    protected $transformer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param string $className
     * @param Type $type
     * @param string $onCreate
     * @param string $onUpdate
     * @param string $onDelete
     * @param TransformerManager $transformer
     * @param LoggerInterface $logger
     */
    public function __construct(
        $className,
        Type $type,
        $onCreate,
        $onUpdate,
        $onDelete,
        TransformerManager $transformer,
        LoggerInterface $logger = null
    ) {
        $this->className = $className;
        $this->type = $type;
        $this->onCreate = $onCreate;
        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
        $this->transformer = $transformer;
        $this->logger = $logger;

        $this->validateAction($onCreate);
        $this->validateAction($onUpdate);
        $this->validateAction($onDelete);
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
     * @param string $action
     * @param object $entity
     */
    protected function performAction($action, $entity)
    {
        $indexName = $this->type->getIndex()->getName();
        $typeName = $this->type->getName();
        $ids = [];

        switch ($action) {
            case self::ACTION_CREATE:
            case self::ACTION_UPDATE:
                $documents = $this->transformer->transformToDocuments(
                    $entity,
                    $indexName,
                    $typeName
                );

                $this->type->putDocuments($documents);

                if (null !== $this->logger) {
                    $ids = array_map(
                        function (Document $doc) {
                            return $doc->getId();
                        },
                        $documents
                    );
                }

                break;

            case self::ACTION_DELETE:
                $ids = $this->transformer->transformToIds(
                    $entity,
                    $indexName,
                    $typeName
                );

                $this->type->deleteDocuments($ids);
        }

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Performed %s synchronization for entity "%s" to "%s/%s" type, affected document ids: ["%s"].',
                strtoupper($action),
                get_class($entity),
                $indexName,
                $typeName,
                implode(', ', $ids)
            ));
        }
    }

    /**
     * @param $entity
     */
    public function synchronizeCreate($entity)
    {
        $this->validateClass($entity);

        if ($this->onCreate) {
            $this->performAction($this->onCreate, $entity);
        }
    }

    /**
     * @param $entity
     */
    public function synchronizeUpdate($entity)
    {
        $this->validateClass($entity);

        if ($this->onUpdate) {
            $this->performAction($this->onUpdate, $entity);
        }
    }

    /**
     * @param $entity
     */
    public function synchronizeDelete($entity)
    {
        $this->validateClass($entity);

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
