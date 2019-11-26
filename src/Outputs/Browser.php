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
     * @param EventInterface $event
     * @param $content
     * @param array $globalVars
     * @return $this|void|OutputTargetInterface
     */
    public function execute(EventInterface $event,$content,$globalVars = []) {
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