<?php

namespace WatchTower\Events;


use WatchTower\Handlers\HandlerInterface;

/**
 * Class Event
 * @package WatchTower\Events
 */
abstract class Event implements EventInterface
{
    /** @var string $id */
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param callable $filter
     * @return bool
     */
    public function passedThroughFilter($filter) {
        if(is_callable($filter)) {
            return $filter($this);
        }
        else {
            return false;
        }
    }

}