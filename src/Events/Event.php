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

    /** @var array $handlerOutput */
    protected $handlerOutput;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $handlerName
     * @return array|bool $handlerOutput
     */
    public function getHandlerOutput($handlerName = null)
    {
        if(isset($handlerName)) {
            if(array_key_exists($handlerName,$this->handlerOutput)) {
                return $this->handlerOutput[$handlerName];
            }
            else {
                return false;
            }
        }
        else {
            return $this->handlerOutput;
        }
    }
}