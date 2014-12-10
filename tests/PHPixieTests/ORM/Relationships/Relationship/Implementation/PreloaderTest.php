<?php

namespace PHPixieTests\ORM\Relationships\Relationship\Implementation;

/**
 * @coversDefaultClass \PHPixie\ORM\Relationships\Relationship\Implementation\Preloader
 */
abstract class PreloaderTest extends \PHPixieTests\AbstractORMTest
{
    protected $preloader;
    protected $loader;

    public function setUp()
    {
        $this->loader    = $this->loader();
        $this->preloader = $this->preloader();
    }
    
    /**
     * @covers ::__construct
     * @covers \PHPixie\ORM\Relationships\Relationship\Implementation\Preloader::__construct
     * @covers ::<protected>
     */
    public function testConstruct()
    {
    
    }
    
    /**
     * @covers ::loader
     * @covers ::<protected>
     */
    public function testLoader()
    {
        $this->assertEquals($this->loader, $this->preloader->loader());
    }
    
    
    protected function property($entity, $expectedValue)
    {
        $property = $this->getProperty();
        $this->method($property, 'entity', $entity, array());
        $property
            ->expects($this->once())
            ->method('setValue')
            ->with($this->identicalTo($expectedValue));
        return $property;
    }
    
    abstract protected function getProperty();
    abstract protected function getEntity();
    abstract protected function loader();
    abstract protected function preloader();
    
}