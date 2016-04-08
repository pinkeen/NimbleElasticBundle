<?php

namespace Nimble\ElasticBundle;

class Hit
{
    const SOURCE_DATA_KEY = '_source';
    const INDEX_DATA_KEY = '_index';
    const TYPE_DATA_KEY = '_type';
    const SCORE_DATA_KEY = '_score';
    const ID_DATA_KEY = '_id';
    const EXPLANATION_DATA_KEY = '_explanation';
    const VERSION_DATA_KEY = '_version';
    const SORT_DATA_KEY = 'sort';

    const FIELDS_DATA_KEY = 'fields';


    /**
     * @var string
     */
    protected $hitDataKey = self::SOURCE_DATA_KEY;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $hit
     */
    public function __construct(array $hit)
    {
        $this->data = $hit;

        if (
            isset($this->data[self::FIELDS_DATA_KEY]) &&
            !isset($this->data[self::SOURCE_DATA_KEY])
        ) {
            $this->hitDataKey = self::FIELDS_DATA_KEY;
        }
    }

    /**
     * @return string|null
     */
    public function getIndexName()
    {
        return isset($this->data[self::INDEX_DATA_KEY]) ?
            $this->data[self::INDEX_DATA_KEY] : null;
    }

    /**
     * @return string|null
     */
    public function getTypeName()
    {
        return isset($this->data[self::TYPE_DATA_KEY]) ?
            $this->data[self::TYPE_DATA_KEY] : null;
    }

    /**
     * @return int|null
     */
    public function getScore()
    {
        return isset($this->data[self::SCORE_DATA_KEY]) ?
            $this->data[self::SCORE_DATA_KEY] : null;
    }


    /**
     * @return array|null
     */
    public function getExplanation()
    {
        return isset($this->data[self::EXPLANATION_DATA_KEY]) ?
            $this->data[self::EXPLANATION_DATA_KEY] : null;
    }

    /**
     * @return int|string|null
     */
    public function getId()
    {
        return isset($this->data[self::ID_DATA_KEY]) ?
            $this->data[self::ID_DATA_KEY] : null;
    }

    /**
     * @return int|string|null
     */
    public function getVersion()
    {
        return isset($this->data[self::VERSION_DATA_KEY]) ?
            $this->data[self::VERSION_DATA_KEY] : null;
    }

    /**
     * Returns array of all hit metadata.
     *
     * @return array|null
     */
    public function getMetadata()
    {
        return array_intersect_key($this->data, array_flip([
            self::ID_DATA_KEY,
            self::SCORE_DATA_KEY,
            self::TYPE_DATA_KEY,
            self::INDEX_DATA_KEY,
            self::EXPLANATION_DATA_KEY,
            self::VERSION_DATA_KEY,
            self::SORT_DATA_KEY,
        ]));
    }

    /**
     * @return array|null
     */
    public function getFields()
    {
        return isset($this->data[self::FIELDS_DATA_KEY]) ?
            $this->data[self::FIELDS_DATA_KEY] : null;
    }


    /**
     * Returns the _source data.
     *
     * @return array|null
     */
    public function getSource()
    {
        return isset($this->data[self::SOURCE_DATA_KEY]) ?
            $this->data[self::SOURCE_DATA_KEY] : null;
    }

    /**
     * @return array
     */
    public function getRawData()
    {
        return $this->data;
    }

    /**
     * Returns value used for sorting or array of all if index is null.
     *
     * Returns null if not available.
     *
     * @param int|null $index
     * @return mixed|null
     */
    public function getSortValue($index = null)
    {
        if (!array_key_exists(self::SORT_DATA_KEY, $this->data)) {
            return null;
        }

        if (null === $index) {
            return $this->data[self::SORT_DATA_KEY];
        }

        if (isset($this->data[self::SORT_DATA_KEY][$index])) {
            return $this->data[self::SORT_DATA_KEY][$index];
        }

        return null;
    }

    /**
     * Returns a source data piece.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->data[$this->hitDataKey])) {
            return null;
        }

        return $this->data[$this->hitDataKey][$name];
    }
    
    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->data[$this->hitDataKey]);
    }
}
