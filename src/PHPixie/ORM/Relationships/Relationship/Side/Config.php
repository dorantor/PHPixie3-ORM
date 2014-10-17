<?php

namespace PHPixie\ORM\Relationships\Relationship\Side;

abstract class Config
{
    public function __construct($inflector, $config)
    {
        $this->processConfig($config, $inflector);
    }

    public function get($key)
    {
        return $this->$key;
    }

    abstract protected function processConfig($config, $inflector);
}
