<?php

namespace PHPixie\ORM\Relationships\Types\Embedded\Type\Embeds\Type\Many\Side;

class Config extends \PHPixie\ORM\Relationships\Types\Embedded\Type\Embeds\Side\Config
{
    protected function itemOptionName()
    {
        return 'items'
    }

    protected function defaultOwnerProperty($inflector)
    {
        return $inflector->plural($this->itemModel);
    }
}
