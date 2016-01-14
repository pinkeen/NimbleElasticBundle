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
     * @param $indexId
     * @param $typeName
     * @param PopulationFetcherInterface $fetcher
     */
    public function registerFetcher(PopulationFetcherInterface $fetcher, $indexId, $typeName)
    {
        if (isset($this->fetchers[$indexId][$typeName])) {
            throw new PopulationFetcherAlreadyRegisteredException($indexId, $typeName);
        }

        $this->fetchers[$indexId][$typeName] = $fetcher;
    }

    /**
     * @param Type $type
     * @return PopulationFetcherInterface
     */
    protected function getFetcherForType(Type $type)
    {
        $indexId = $type->getIndex()->getId();
        $typeName = $type->getName();

        if (!isset($this->fetchers[$indexId][$typeName])) {
            throw new PopulationFetcherNotFoundException($indexId, $typeName);
        }

        return $this->fetchers[$indexId][$typeName];
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