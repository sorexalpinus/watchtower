<?php
namespace WatchTower;
use WatchTower\Events\EventInterface;
use WatchTower\Events\EventTrait;

/**
 * Class EventBuffer
 */
class EventBuffer implements \Countable
{
    use EventTrait;

    /** @var array $buffer */
    protected $buffer;
    protected $maxSize = 50;

    /**
     * @return EventBuffer $eventBuffer
     */
    static public function create()
    {
        return new self();
    }

    /**
     * EventBuffer constructor.
     */
    public function __construct()
    {
        $this->buffer = [];
    }

    /**
     * @param string $type
     * @param array|string $info
     * @return bool $canPush
     */
    public function canPush($type,$info)
    {
        if($this->count() < $this->maxSize) {
            $hash = $this->getCommonLocationHash($type,$info);
            if(!array_key_exists($hash,$this->buffer)) {
                return true;
            }
        }
        return  false;
    }

    /**
     * @param EventInterface $event
     * @return EventBuffer $eventBuffer
     */
    public function push(EventInterface $event) {
        $hash = $event->getLocationHash();
        $this->buffer[$hash] = $event;
        return $this;
    }

    /**
     * @return int $count
     */
    public function count()
    {
        return count($this->buffer);
    }




}