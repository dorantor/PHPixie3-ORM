<?php

namespace PHPixie\ORM;

class Values
{
    public function orderBy($field, $direction)
    {
        return new \PHPixie\ORM\Values\OrderBy($field, $direction);
    }
}