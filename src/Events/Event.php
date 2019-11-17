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

}