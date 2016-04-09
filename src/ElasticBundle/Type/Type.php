<?php

namespace Nimble\ElasticBundle\Type;

use Elasticsearch\Client;
use Nimble\ElasticBundle\Document;
use Nimble\ElasticBundle\Index\Index;

class Type
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Index
     */
    private $index;

    /**
     * @var array|null
     */
    private $mappings;

    /**
     * @param string $name
     * @param Index $index
     * @param array $mappings
     */
    public function __construct($name, Index $index, array $mappings = null)
    {
        $this->name = $name;
        $this->index = $index;
        $this->mappings = $mappings;
    }

    /**
     * @return string
     */
    protected function getIndexName()
    {
        return $this->index->getName();
    }

    /**
     * @return string
     */
    protected function getIndexId()
    {
        return $this->index->getId();
    }

    /**
     * Creates client request params appending options that define the index and type.
     *
     * @param array $params
     * @return array
     */
    protected function createRequestParams(array $params = [])
    {
        return array_merge([
            'index' => $this->getIndexName(),
            'type' => $this->getName(),
        ], $params);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->index->getClient();
    }

    /**
     * Checks if the index exists in ES.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->getClient()->indices()->exists(
            $this->createRequestParams()
        );
    }

    /**
     * Creates the type mapping in ES.
     */
    public function createMappings()
    {
        if (empty($this->mappings)) {
            return;
        }

        $this->getClient()->indices()->putMapping(
            $this->createRequestParams([
                'body' => [$this->name => $this->mappings]
            ])
        );
    }

    /**
     * Deletes the type mappings in ES.
     */
    public function deleteMappings()
    {
        $this->getClient()->indices()->deleteMapping(
            $this->createRequestParams()
        );
    }

    /**
     * Resets the type mappings in ES.
     */
    public function reset()
    {
        $this->deleteMappings();
        $this->createMappings();
    }

    /**
     * @return array
     */
    public function getMappings()
    {
        return $this->mappings;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Index
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param Document $document
     */
    public function putDocument(Document $document)
    {
        $this->index->putDocument($this->name, $document);
    }

    /**
     * @param string|int $id
     */
    public function deleteDocument($id)
    {
        $this->index->deleteDocument($this->name, $id);
    }

    /**
     * @param array $documents
     */
    public function putDocuments(array $documents)
    {
        $this->index->putDocuments($this->name, $documents);
    }

    /**
     * @param array $ids
     */
    public function deleteDocuments(array $ids)
    {
        $this->index->deleteDocuments($this->name, $ids);
    }

    /**
     * @param array|string $query Array that will be serialized or raw JSON.
     * @param array $options
     * @return array
     */
    public function search($query, array $options = [])
    {
        return $this->index->search($query, $options, $this->name);
    }

    /**
     * @param array|string $query Array that will be serialized or raw JSON.
     * @param array $options
     * @return int|null
     */
    public function count($query, array $options = [])
    {
        return $this->index->count($query, $options, $this->name);
    }

    /**
     * @param array|string $query
     * @param array $options
     */
    public function deleteByQuery($query, array $options = [])
    {
        $this->index->deleteByQuery($query, $options, $this->name);
    }
}
