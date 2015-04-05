<?php

namespace Nimble\ElasticBundle;

class Document 
{
    /**
     * @var int|string
     */
    private $id;

    /**
     * @var array
     */
    private $data;

    /**
     * @param string|int $id
     * @param array $data
     */
    public function __construct($id, array $data)
    {

        $this->id = $id;
        $this->data = $data;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}