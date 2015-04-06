<?php

namespace Nimble\ElasticBundle\Type;

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
}
