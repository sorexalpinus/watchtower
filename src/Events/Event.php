<?php

namespace WatchTower\Events;

/**
 * Class Event
 * @package WatchTower\Events
 */
abstract class Event implements EventInterface
{
    /** @var string $id */
    protected $id;

    /** @var string $locationHash */
    protected $locationHash;

    /** @var bool $handled */
    protected $handled = false;

    /**
 * @return mixed
 */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string $hash
     */
    public function getLocationHash() {
        return $this->locationHash;
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

    /**
     * @param bool $wasHandled
     * @return $this
     */
    public function setHandled($wasHandled) {
        $this->handled = $wasHandled;
        return $this;
    }

    /**
     * @return bool $wasHandled
     */
    public function wasHandled() {
        return $this->handled;
    }

}