<?php
namespace WatchTower\Handlers;

use WatchTower\Events\EventInterface;

/**
 * Class PlainTextNotice
 * @package WatchTower\Handlers
 */
class PlainTextNotice extends Handler
{
    /**
     * @param EventInterface $event
     * @return $this
     */
    public function handle(EventInterface $event)
    {
        $r = '<pre>';
        $r .= sprintf("%s: %s in file %s on line %d", $event->getName(), $event->getMessage(), $event->getFile(), $event->getLine());
        $r .= PHP_EOL . '---' . PHP_EOL;
        $r .= $event->getTraceAsString();
        $r .= '</pre>';
        $this->output = $r;
        return $this;
    }
}