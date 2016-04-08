<?php

namespace Nimble\ElasticBundle\Index;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Nimble\ElasticBundle\Document;
use Nimble\ElasticBundle\Exception\TypeNotFoundException;
use Nimble\ElasticBundle\SearchResults;
use Nimble\ElasticBundle\Type\Type;

class Index
{
    /**
     * The actual index name used for querying elasticsearch.
     *
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
     * @var Type[]
     */
    private $types = [];

    /**
     * Internal index id.
     * Used for naming services and internal identification.
     *
     * @var string
     */
    private $id;

    /**
     * @param string $id Internal index name.
     * @param string $name
     * @param Client $client
     * @param array $settings
     * @param array $types
     */
    public function __construct($id, $name = null, Client $client, array $settings, array $types)
    {
        $this->id = $id;
        $this->name = null === $name ? $id : $name;
        $this->client = $client;
        $this->settings = $settings;

        $this->buildTypes($types);
    }

    /**
     * @param array $types
     */
    protected function buildTypes(array $types)
    {
        foreach ($types as $typeName => $typeData) {

            $mappings = null;

            if (isset($typeData['mappings'])) {
                $mappings = ['properties' => $typeData['mappings']];
            }

            $this->types[$typeName] = new Type($typeName, $this, $mappings);
        }
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isAliased()
    {
        return $this->id !== $this->name;
    }

    /**
     * Checks if the index exists in ES.
     *
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

        $mappings = $this->getMappings();

        if (!empty($mappings)) {
            $params['body']['mappings'] = $mappings;
        }

        if (!empty($this->settings)) {
            $params['body']['settings'] = $this->settings;
        }

        $this->client->indices()->create($params);
    }

    /**
     * @return array
     */
    public function getMappings()
    {
        $mappings = [];

        foreach ($this->types as $type) {
            if (!empty($type->getMappings())) {
                $mappings[$type->getName()] = $type->getMappings();
            }
        }

        return $mappings;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasType($name)
    {
        return isset($this->types[$name]);
    }

    /**
     * @param string $name
     * @return Type
     */
    public function getType($name)
    {
        if (!$this->hasType($name)) {
            throw new TypeNotFoundException($name, $this->name);
        }

        return $this->types[$name];
    }

    /**
     * @return Type[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param string $type
     * @param Document $document
     */
    public function putDocument($type, Document $document)
    {
        $data = [
            'index' => $this->name,
            'type' => $type,
            'body' => $document->getData(),
        ];

        if (null !== $document->getId()) {
            $data['id'] = $document->getId();
        }

        $this->client->index($data);
    }

    /**
     * @param string $type
     * @param string|int $id
     * @return bool Returns false if document didn't exist.
     */
    public function deleteDocument($type, $id)
    {
        try {
            $this->client->delete([
                'index' => $this->name,
                'type' => $type,
                'id' => $id,
            ]);
        } catch (Missing404Exception $exception) {
            /* Do nothing, just ignore not synched. */
            return false;
        }

        return true;
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
     * @param array|string $body
     * @param array $options
     * @param string $type
     * @return array
     */
    protected function buildParams($body, array $options, $type = null)
    {
        $params = array_merge([
            'index' => $this->name,
            'body' => $body,
        ], $options);

        if (null !== $type) {
            $params['type'] = $type;
        }

        return $params;
    }

    /**
     * @param array|string $query Array that will be serialized or raw JSON.
     * @param array $options
     * @param string $type
     * @return SearchResults
     */
    public function search($query, array $options = [], $type = null)
    {
        return new SearchResults($this->client->search(
            $this->buildParams($query, $options, $type)
        ));
    }

    /**
     * @param array|string $query
     * @param array $options
     * @param string $type
     */
    public function deleteByQuery($query, array $options = [], $type)
    {
        $this->client->deleteByQuery(
            $this->buildParams($query, $options, $type)
        );
    }
}
