<?php

namespace Nimble\ElasticBundle\Populator;

use Nimble\ElasticBundle\Populator\Exception\PopulationFetcherAlreadyRegisteredException;
use Nimble\ElasticBundle\Populator\Exception\PopulationFetcherNotFoundException;
use Nimble\ElasticBundle\Transformer\TransformerManager;
use Nimble\ElasticBundle\Type\Type;

class PopulatorManager
{
    /**
     * @var PopulationFetcherInterface[][]
     */
    protected $fetchers = [];

    /**
     * @var TransformerManager
     */
    private $transformer;

    /**
     * @param TransformerManager $transformer
     */
    public function __construct(TransformerManager $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @param $indexName
     * @param $typeName
     * @param PopulationFetcherInterface $fetcher
     */
    public function registerFetcher(PopulationFetcherInterface $fetcher, $indexName, $typeName)
    {
        if (isset($this->fetchers[$indexName][$typeName])) {
            throw new PopulationFetcherAlreadyRegisteredException($indexName, $typeName);
        }

        $this->fetchers[$indexName][$typeName] = $fetcher;
    }

    /**
     * @param Type $type
     * @return PopulationFetcherInterface
     */
    protected function getFetcherForType(Type $type)
    {
        $indexName = $type->getIndex()->getName();
        $typeName = $type->getName();

        if (!isset($this->fetchers[$indexName][$typeName])) {
            throw new PopulationFetcherNotFoundException($indexName, $typeName);
        }

        return $this->fetchers[$indexName][$typeName];
    }

    /**
     * @param Type $type
     * @return Populator
     */
    public function createPopulator(Type $type)
    {
        return new Populator(
            $type,
            $this->getFetcherForType($type),
            $this->transformer
        );
    }
}