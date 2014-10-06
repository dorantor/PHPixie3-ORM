<?php

namespace PHPixieTests\ORM\Relationships\Type\OneTo;

/**
 * @coversDefaultClass \PHPixie\ORM\Relationships\Type\OneTo\Handler
 */
abstract class HandlerTest extends \PHPixieTests\ORM\Relationships\Relationship\HandlerTest
{
    protected $itemSide;
    protected $ownerPropertyName;
    protected $propertyConfig;
    protected $configOnwerProperty;
    public function setUp()
    {
        $this->configData = array(
            'ownerModel'        => 'fairy',
            'itemModel'         => 'flower',
            'ownerKey'          => 'fairy_id',
            'itemOwnerProperty' => 'fairy',
            $this->ownerPropertyName => $this->configOnwerProperty,
        );
        
        $this->propertyConfig = $this->config($this->configData);
        parent::setUp();
    }
    
    /**
     * @covers ::query
     * @covers ::<protected>
     */
    public function testQuery()
    {
        foreach(array('owner', $this->itemSide) as $type) {
            $side = $this->side($type, $this->configData);
            $query = $this->getQuery();
            $related = $this->getModel();
            $this->prepareQuery($side, $query, $related);
            $this->assertEquals($query, $this->handler->query($side, $related));
        }
    }

    /**
     * @covers ::linkPlan
     * @covers ::<protected>
     */
    public function testLinkPlan()
    {
        $plan = $this->prepareLinkPlan();
        $this->assertSame($plan, $this->handler->linkPlan($config, $owner, $items));
    }

    /**
     * @covers ::mapQuery
     * @covers ::<protected>
     */
    public function testMapQuery()
    {
        $repositories = $this->prepareRepositories();

        foreach(array('owner', 'item') as $type) {
            $side = $this->side($type, $this->configData);
            $query = $this->getDatabaseQuery();
            $plan = $this->getPlan();

            $subqueryRepository = $repositories[$type == 'owner' ? 'owner' : 'item'];
            $this->method($repositories['owner'], 'idField', 'id', array(), 0);

            if ($type === 'owner') {
                $queryField = $this->configData['ownerKey'];
                $subqueryField = 'id';
            } else {
                $queryField = 'id';
                $subqueryField = $this->configData['ownerKey'];
            }

            $subquery = $this->getDatabaseQuery();

            $subqueryRepoOffset = $type == 'owner' ? 1 : 0;

            $this->method($subqueryRepository, 'databaseSelectQuery', $subquery, array(), $subqueryRepoOffset++);

            $group = $group = $this->getConditionGroup('and', true, array(5));

            $modelName = $this->configData[$type.'Model'];

            $this->method($subqueryRepository, 'modelName', $modelName, array(), $subqueryRepoOffset++);

            $this->method($this->groupMapper, 'mapConditions', null, array(
                $subquery,
                array(5),
                $modelName,
                $plan
            ), 0);

            $inPlanner = $this->getPlanner('in');
            $this->method($this->planners, 'in', $inPlanner, array(), 0);
            $this->method($inPlanner, 'subquery', null, array(
                $query,
                $queryField,
                $subquery,
                $subqueryField,
                $plan,
                'and',
                true
            ), 0);

            $this->handler->mapQuery($side, $group, $query, $plan);
        }
    }

    /**
     * @covers ::mapPreload
     * @covers ::<protected>
     */
    public function testMapPreload()
    {
        $repositories = $this->prepareRepositories();

        foreach(array('owner', 'item') as $type) {
            $side = $this->side($type, $this->configData);
            $query = $this->getDatabaseQuery();
            $preloadPlan = $this->getPlan();

            $preloadRepository = $repositories[$type == 'owner' ? 'owner' : 'item'];
            $this->method($repositories['owner'], 'idField', 'id', array(), 0);

            if ($type === 'owner') {
                $queryField = 'id';
                $resultField = $this->configData['ownerKey'];
            } else {
                $queryField = $this->configData['ownerKey'];
                $resultField = 'id';
            }

            $query = $this->getDatabaseQuery();

            $preloadRepoOffset = $type == 'owner' ? 1 : 0;

            $this->method($preloadRepository, 'databaseSelectQuery', $query, array(), $preloadRepoOffset++);

            $inPlanner = $this->getPlanner('in');
            $this->method($this->planners, 'in', $inPlanner, array(), 0);

            $repoLoader = $this->getReusableResultLoader();
            $resultStep = $this->getReusableResult();
            $this->method($repoLoader, 'reusableResult', $resultStep, array(), 0);

            $this->method($inPlanner, 'result', null, array(
                $query,
                $queryField,
                $resultStep,
                $resultField,
                $preloadPlan
            ), 0);

            $preloadStep = $this->getReusableResult();
            $this->method($this->steps, 'reusableResult', $preloadStep, array($query), 0);
            $this->method($preloadPlan, 'add', null, array($preloadStep), 0);
            $loader = $this->getReusableResultLoader();
            $this->method($this->loaders, 'reusableResult', $loader, array($preloadRepository, $preloadStep), 0);

            $preloader = $this->getPreloader($type);
            $this->method($this->relationship, 'preloader', $preloader, array($side, $loader), 0);

            $this->assertEquals($preloader, $this->handler->mapPreload($side, $repoLoader, $preloadPlan));
        }
    }

    protected function prepareLinkPlan($owner, $items, $plansOffset = 0, $ownerRepoOffset = 0, $itemRepoOffset= 0, $plannersOffset = 0)
    {
        $ownerRepository = $this->repositories->get($this->configData['ownerModel']);
        $itemRepository = $this->repositories->get($this->configData['itemModel']);
        $data = $this->configData;
        $config = $this->config($data);

        $planners = $this->getPlanners(array('in', 'update'));
        
        $plan = $this->getPlan();
        $this->method($this->plans, 'plan', $plan, array(), $plansOffset);

        $ownerQuery = $this->getDatabaseQuery();
        $this->method($ownerRepository, 'databaseSelectQuery', $ownerQuery, array(), $ownerRepoOffset++);
        $this->prepareAddCollectionQuery($ownerQuery, 'owner', $owner, $plan, $planners['in'], null, 'and', 0, $plannersOffset, $ownerRepoOffset);

        $updateQuery = $this->getDatabaseQuery('update');
        $this->method($itemRepository, 'databaseUpdateQuery', $updateQuery, array(), $itemRepoOffset++);
        $this->prepareAddCollectionQuery($updateQuery, 'item', $items, $plan, $planners['in'], null, 'and', 1, $plannersOffset + 2, $itemRepoOffset++);

        $this->method($this->planners, 'update', $planners['update'], array(), $plannersOffset + 4);
        $this->method($ownerRepository, 'idField', 'id', array(), $ownerRepoOffset + 2);

        $this->method($planners['update'], 'subquery', null, array(
                                                                $updateQuery,
                                                                array(
                                                                    $data['ownerKey'] => 'id'
                                                                ),
                                                                $ownerQuery,
                                                                $plan
                                                            ), 0);
        return $plan;
    }
    
    protected function prepareAddCollectionQuery($query, $type, $items, $plan, $inPlanner, $queryField = null, $logic = 'and', $inPlannerOffset = 0, $plannersOffset = 0, $repositoryOffset = 1)
    {
        if($queryField === null)
            $queryField = 'id';

        $modelName = $this->configData[$type.'Model'];
        $repository = $this->repositories->get($modelName);

        $collection = $this->quickMock('\PHPixie\ORM\Planners\Collection');
        $this->method($repository, 'idField', 'id', array(), $repositoryOffset++);
        $this->method($repository, 'modelName', $modelName, array(), $repositoryOffset++);

        $this->method($this->planners, 'collection', $collection, array($modelName, $items), $plannersOffset++);
        $this->method($this->planners, 'in', $inPlanner, array(), $plannersOffset++);

        $this->method($inPlanner, 'collection', null, array($query, $queryField, $collection, 'id', $plan, $logic), $inPlannerOffset);
    }

    protected function prepareUnlinkTest($constrainOwners, $owners, $constrainItems, $items, $logic = 'and')
    {
        $itemRepository = $this->repositories->get($this->configData['itemModel']);
        
        $plan = $this->getPlan();
        $this->method($this->plans, 'plan', $plan, array(), 0);

        $ownerKey = $this->configData['ownerKey'];
        $updateQuery = $this->getDatabaseQuery('update');
        $this->method($itemRepository, 'databaseUpdateQuery', $updateQuery, array(), 0);

        $this->method($updateQuery, 'set', null, array($ownerKey, null), 0);

        $inPlanner = $this->getPlanner('in');
        $inPlannerOffset = 0;
        $plannersOffset = 0;

        if ($constrainItems) {
            $this->prepareAddCollectionQuery($updateQuery, 'item', $items, $plan, $inPlanner, null, 'and', $inPlannerOffset++, $plannersOffset, 1);
            $plannersOffset+=2;
        }

        if ($constrainOwners) {
            $this->prepareAddCollectionQuery($updateQuery, 'owner', $owners, $plan, $inPlanner, $ownerKey, $logic, $inPlannerOffset++, $plannersOffset, 0);
        }

        return $plan;
    }


    protected function prepareRepositories()
    {
        $repositories = array(
            'owner' => $this->getRepository(),
            'item'  => $this->getRepository(),
        );

        $this->setRepositories(array(
            $this->configData['ownerModel'] => $repositories['owner'],
            $this->configData['itemModel']  => $repositories['item'],
        ));

        return $repositories;
    }

    protected function prepareQuery($side, $query, $related)
    {
        $type = $side->type();
        $data = $this->configData;
        if($type !== 'owner')
            $type = 'item';

        $repository = $this->getRepository();
        $this->method($this->repositories, 'get', $repository, array($data[$type.'Model']), 0);
        $this->method($repository, 'query', $query, array(), 0);
        $this->method($query, 'related', $query, array($this->propertyName($type), $related), 0);
    }
    
    protected function prepareLoadSingleProperty($side, $related)
    {
        $model = $this->getDatabaseModel();
        $query = $this->getQuery();
        
        $this->prepareQuery($side, $query, $related);
        $this->method($query, 'findOne', $model, array());
        return $model;
    }
    
    protected function getQuery()
    {
        return $this->quickMock('\PHPixie\ORM\Query');
    }

    protected function getRepository()
    {
        return $this->abstractMock('\PHPixie\ORM\Repositories\Type\Database');
    }
    
    protected function addSingleProperty($model, $type, $propertyExists = true, $loaded = false, $owner = null, $expectCreateMissing = null)
    {
        $property = null;
        if($propertyExists) {
            $property = $this->getSingleProperty($type);
            $this->method($property, 'isLoaded', $loaded, array());
        
            if($loaded) {
                $this->method($property, 'value', $owner, array());
            }
        }
        $propertyName = $this->propertyName($type);
        
        $with = array($propertyName);
        if($expectCreateMissing !== null)
            $with[]=$expectCreateMissing;
        
        $this->method($model, 'relationshipProperty', $property, array($propertyName), null, true);
        return array(
            'model'    => $model,
            'property' => $property,
            'owner'    => $owner
        );
    }
    
    protected function expectSetValue($itemMock, $valueMock = null)
    {
        $value = null;
        if($valueMock !== null)
            $value = $valueMock['model'];
        $itemMock['property']
            ->expects($this->once())
            ->method('setValue')
            ->with($this->identicalTo($value));
    }
    
    protected function expectsExactly($mock, $method, $exactly)
    {
        $mock
            ->expects($this->exactly($exactly))
            ->method($method)
            ->with();
    }
    
    protected function config($map, $methodMap = array())
    {
        $methodMap['ownerProperty'] = $this->configData[$this->ownerPropertyName];
        return parent::config($map, $methodMap);
    }
    
    protected function propertyName($type)
    {
        if($type === 'owner')
            return $this->configData[$this->ownerPropertyName];
        
        return $this->configData['itemOwnerProperty'];    
    }
    
    protected function getDatabaseModel()
    {
        return $this->abstractMock('\PHPixie\ORM\Repositories\Type\Database\Model');
    }
    
    abstract protected function getSingleProperty($type);
    
    abstract protected function getPreloader($type);
}