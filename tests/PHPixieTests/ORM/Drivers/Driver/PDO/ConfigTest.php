<?php

namespace PHPixieTests\ORM\Drivers\Driver\PDO;

/**
 * @coversDefaultClass \PHPixie\ORM\Drivers\Driver\PDO\Config
 */
class ConfigTest extends \PHPixieTests\ORM\Drivers\Driver\SQL\ConfigTest
{
    protected $driver = 'PDO';
    
    protected function getConfig($slice)
    {
        return new \PHPixie\ORM\Drivers\Driver\PDO\Config($this->inflector, $this->model, $slice);
    }
}