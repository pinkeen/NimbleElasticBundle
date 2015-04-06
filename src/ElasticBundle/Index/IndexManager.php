<?php

namespace Nimble\ElasticBundle\Index;

class IndexManager
{
    /**
     * @var Index[]
     */
    protected $indexes = [];

    /**
     * @param Index $index
     */
    public function registerIndex(Index $index)
    {
        if (array_key_exists($index->getName(), $this->indexes)) {
            throw new \InvalidArgumentException(
                sprintf('Index "%s" is already registered.', $index->getName())
            );
        }

        $this->indexes[$index->getName()] = $index;
    }

    /**
     * @param string $name
     * @return Index|null
     */
    public function getIndex($name)
    {
        if (!$this->hasIndex($name)) {
            return null;
        }

        return $this->indexes[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasIndex($name)
    {
        return array_key_exists($name, $this->indexes);
    }

    /**
     * @return array
     */
    public function getIndexNames()
    {
        return array_keys($this->indexes);
    }

    /**
     * @return Index[]
     */
    public function getIndexes()
    {
        return array_values($this->indexes);
    }
}
