<?php

namespace Nimble\ElasticBundle\Index;

use Elasticsearch\Client;
use Nimble\ElasticBundle\Document;
use Nimble\ElasticBundle\Type\Type;

class Index
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $settings;

    /**
     * @var array
     */
    private $mappings;

    /**
     * @var Type[]
     */
    private $types;

    /**
     * @param string $name
     * @param Client $client
     * @param array $settings
     * @param array $mappings
     */
    public function __construct($name, Client $client, array $settings, array $mappings)
    {
        $this->name = $name;
        $this->client = $client;
        $this->settings = $settings;
        $this->mappings = $mappings;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->client->indices()->exists(['index' => $this->name]);
    }

    /**
     * Deletes the index.
     */
    public function delete()
    {
        $this->client->indices()->delete(['index' => $this->name]);
    }

    /**
     * Resets the index.
     */
    public function reset()
    {
        if ($this->exists()) {
            $this->delete();
        }

        $this->create();
    }

    /**
     * Creates the index in ES.
     */
    public function create()
    {
        $params = [
            'index' => $this->name,
        ];

        if (!empty($this->mappings)) {
            $params['body']['mappings'] = $this->mappings;
        }

        if (!empty($this->settings)) {
            $params['body']['settings'] = $this->settings;
        }

        $this->client->indices()->create($params);
    }

    /**
     * @param string $name
     * @return Type
     */
    public function getType($name)
    {
        if (!isset($this->types[$name])) {
            $mappings = [];

            if (isset($this->mappings[$name]['properties'])) {
                $mappings = $this->mappings[$name]['properties'];
            }

            $this->types[$name] = new Type($name, $this, $mappings);
        }

        return $this->types[$name];
    }

    /**
     * @param string $type
     * @param Document $document
     */
    public function putDocument($type, Document $document)
    {
        $this->client->index([
            'index' => $this->name,
            'type' => $type,
            'id' => $document->getId(),
            'body' => $document->getData(),
        ]);
    }

    /**
     * @param string $type
     * @param string|int $id
     */
    public function deleteDocument($type, $id)
    {
        $this->client->delete([
            'index' => $this->name,
            'type' => $type,
            'id' => $id,
        ]);
    }

    /**
     * @param string $type
     * @param array $documents
     */
    public function putDocuments($type, array $documents)
    {
        foreach ($documents as $document) {
            $this->putDocument($type, $document);
        }
    }

    /**
     * @param string $type
     * @param array $ids
     */
    public function deleteDocuments($type, array $ids)
    {
        foreach ($ids as $id) {
            $this->deleteDocument($type, $id);
        }
    }

    /**
     * @param array|string $query Array that will be serialized or raw JSON.
     * @param array $options
     * @param string $type
     * @return array
     */
    public function search($query, array $options = [], $type = null)
    {
        $params = array_merge([
            'index' => $this->name,
            'body' => $query,
        ], $options);

        if (null !== $type) {
            $params['type'] = $type;
        }

        return $this->client->search($params);
    }
}