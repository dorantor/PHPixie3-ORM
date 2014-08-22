<?php

namespace PHPixie\ORM\Relationships\Type\ManyToMany;

class Preloader extends \PHPixie\ORM\Relationships\Relationship\Preloader\Result\Multiple
{
    protected $pivotResult;

    public function __construct($loaders, $relationshipType, $side, $loader, $pivotResult)
    {
        parent::__construct($loaders, $relationshipType, $side, $loader);
        $this->pivotResult = $pivotResult;
    }

    protected function mapItems()
    {
        $type = $this->side->type();
        $opposing = $type === 'left' ? 'right' : 'left';
        
        $config = $this->side->config();
        
        $ownerIdField = $config->get($opposing.'PivotKey');
        $itemIdField = $config->get($type.'PivotKey');
        
        $fields = $this->pivotResult->getFields(array($ownerIdField, $itemIdField));

        foreach ($fields as $pivotData) {
            $id = $pivotData[$itemIdField];
            $ownerId = $pivotData[$ownerIdField];

            if (!isset($this->map[$ownerId]))
                $this->map[$ownerId] = array();

            $this->map[$ownerId][] = $id;
        }

        $idField = $this->loader->repository()->idField();
        $ids = $this->loader->reusableResult()->getField($idField);
        foreach ($ids as $offset => $id) {
            $this->idOffsets[$id] = $offset;
        }
    }
}
