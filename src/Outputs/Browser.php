<?php

namespace WatchTower\Outputs;

use WatchTower\Events\EventInterface;

/**
 * Class Browser
 * @package WatchTower\Outputs
 */
class Browser extends OutputTarget
{

    /**
     * @return string $name
     */
    public function getName() {
        return 'browser';
    }

    /**
     * @param EventInterface $event
     * @param $content
     * @param array $outputStack
     * @return $this
     */
    public function execute(EventInterface $event,$content,$outputStack = []) {
        echo $content;
        return $this;
    }

    /**
     * @param string $initialOutput
     * @return $this
     */
    public function init($initialOutput) {
        echo $initialOutput;
        return $this;
    }
}