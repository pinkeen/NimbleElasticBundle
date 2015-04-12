<?php

namespace Nimble\ElasticBundle;


class SearchResults implements \Iterator
{
    /**
     * @var bool
     */
    protected $hasTimedOut;

    /**
     * @var int
     */
    protected $maxScore;

    /**
     * @var int
     */
    protected $totalCount;

    /**
     * @var int
     */
    protected $totalTime;

    /**
     * @var Hit[]
     */
    protected $hits = [];

    /**
     * @var int
     */
    protected $_index = 0;

    /**
     * @var array
     */
    protected $aggregations = [];

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->totalTime = isset($data['took']) ? $data['took'] : null;
        $this->hasTimedOut = isset($data['timed_out']) ? $data['timed_out'] : null;
        $this->aggregations = isset($data['aggregations']) ? $data['aggregations'] : [];

        if (isset($data['hits'])) {
            $this->totalCount = $data['hits']['total'];
            $this->maxScore = $data['hits']['max_score'];
            $this->buildHits($data['hits']['hits']);
        }
    }

    /**
     * @param array $hitsData
     */
    protected function buildHits(array $hitsData)
    {
        $this->hits = array_map(
            function(array $hitData) {
                return new Hit($hitData);
            },
            array_values($hitsData)
        );
    }

    /**
     * @return boolean
     */
    public function hasTimedOut()
    {
        return $this->hasTimedOut;
    }

    /**
     * @return int
     */
    public function getMaxScore()
    {
        return $this->maxScore;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return int
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }

    /**
     * @return Hit[]
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function getAggregation($name)
    {
        return isset($this->aggregations[$name]) ?
            $this->aggregations[$name] : null;
    }

    /**
     * @return bool
     */
    public function hasAggregations()
    {
        return !empty($this->aggregations);
    }

    /**
     * @return array
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * return int
     */
    public function getCount()
    {
        return count($this->hits);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->_index = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->hits[$this->_index];
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->_index;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->_index;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return array_key_exists($this->_index, $this->hits);
    }
}
